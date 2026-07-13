<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessItemMedia;
use App\Models\Item;
use App\Models\ItemMedia;
use App\Models\ItemVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorItemController extends Controller
{
    // ── List Products ─────────────────────────────────────────────────────────
    // GET /shop/products
    // Query: q, category_id, subcategory_id, status, item_type, is_veg, is_featured
    //        sort (name|price|status|created_at|display_order|rating|total_sold)
    //        order (asc|desc), per_page
    public function index(Request $request): JsonResponse
    {
        $shop    = $request->user();
        $perPage = $request->integer('per_page', 20);
        $sort    = in_array($request->sort, ['item_name', 'price', 'status', 'created_at', 'display_order', 'rating_avg', 'total_sold'])
            ? $request->sort : 'display_order';
        $order   = $request->order === 'asc' ? 'asc' : 'desc';

        $query = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'variants',
            'readyMedia',
        ])
            ->where('shop_id', $shop->shop_id)
            ->when($request->filled('q'), fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('item_name', 'like', '%' . $request->q . '%')
                    ->orWhere('sku', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            }))
            ->when($request->filled('category_id'),    fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('subcategory_id'), fn($q) => $q->where('subcategory_id', $request->subcategory_id))
            ->when($request->filled('status'),         fn($q) => $q->where('status', $request->status))
            ->when($request->filled('item_type'),      fn($q) => $q->where('item_type', $request->item_type))
            ->when($request->filled('is_veg'),         fn($q) => $q->where('is_veg', (bool) $request->is_veg))
            ->when($request->filled('is_featured'),    fn($q) => $q->where('is_featured', (bool) $request->is_featured))
            ->orderBy($sort === 'price' ? 'offer_price' : $sort, $order)
            ->when($sort !== 'item_name', fn($q) => $q->orderBy('item_name'));

        $items = $query->paginate($perPage);
        $items->getCollection()->transform(fn($i) => $this->appendMedia($i));

        // Stats sidebar counts (single efficient query)
        $stats = Item::where('shop_id', $shop->shop_id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status='active') as active,
                SUM(status='inactive') as inactive,
                SUM(item_type='combo') as combos,
                SUM(is_featured=1) as featured,
                SUM(track_inventory=1 AND stock_quantity <= low_stock_alert) as low_stock
            ")->first();

        return response()->json([
            'status' => true,
            'data'   => $items,
            'stats'  => $stats,
        ]);
    }

    // ── Create Product ────────────────────────────────────────────────────────
    // POST /shop/products
    // Body (multipart/form-data):
    //   item_name, category_id, subcategory_id, description, item_type (regular|combo)
    //   price, offer_price, min_quantity, max_quantity, weight_or_piece
    //   is_veg, is_jain, spice_level, preparation_time, allow_custom_notes
    //   gst_percent, hsn_code, sku, badge, display_order, is_featured
    //   track_inventory, stock_quantity, low_stock_alert
    //   status, variants (JSON string), media[] (files)
    public function store(Request $request): JsonResponse
    {
        $shop     = $request->user();
        $variants = $this->decodeVariants($request);

        $validator = Validator::make(
            array_merge($request->all(), ['variants' => $variants]),
            $this->itemRules() + [
                'category_id'  => 'required|exists:item_categories,id',
                'variants'     => 'nullable|array',
                'media'        => 'nullable|array|max:10',
                'media.*'      => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200', // 50 MB per file
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $item = Item::create(array_merge(
                $this->itemPayload($request),
                ['shop_id' => $shop->shop_id, 'status' => $request->get('status', 'active')]
            ));

            if (!empty($variants)) {
                $this->syncVariants($item->id, $variants);
            }

            $this->handleMediaUploads($request, $item);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Product created successfully.',
                'data'    => $this->freshItem($item->id),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VendorItem] store failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create product. Please try again.'], 500);
        }
    }

    // ── Get Single Product ────────────────────────────────────────────────────
    // GET /shop/products/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $item = Item::with(['category:id,category_name', 'subcategory:id,name', 'allVariants', 'media', 'comboComponents', 'offers'])
            ->where('shop_id', $request->user()->shop_id)
            ->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->appendMedia($item)]);
    }

    // ── Update Product ────────────────────────────────────────────────────────
    // PUT /shop/products/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $item = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        $variants = $this->decodeVariants($request);

        $validator = Validator::make(
            array_merge($request->all(), ['variants' => $variants]),
            $this->itemRules('sometimes') + [
                'category_id'    => 'sometimes|exists:item_categories,id',
                'variants'       => 'nullable|array',
                'media'          => 'nullable|array|max:10',
                'media.*'        => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200',
                'remove_media'   => 'nullable|array',
                'remove_media.*' => 'integer|exists:item_media,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $item->update($this->itemPayload($request, $item));

            if ($variants !== null) {
                $this->syncVariants($item->id, $variants, $item->allVariants->pluck('id')->toArray());
            }

            // Delete removed media
            if ($request->filled('remove_media')) {
                $toDelete = ItemMedia::where('item_id', $item->id)
                    ->whereIn('id', $request->remove_media)
                    ->get();
                foreach ($toDelete as $m) {
                    $this->deleteMediaFiles($m);
                    $m->delete();
                }
            }

            $this->handleMediaUploads($request, $item);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Product updated.',
                'data'    => $this->freshItem($item->id),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VendorItem] update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to update product.'], 500);
        }
    }

    // ── Delete Product ────────────────────────────────────────────────────────
    // DELETE /shop/products/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        // Delete all media files
        foreach ($item->media as $m) {
            $this->deleteMediaFiles($m);
        }

        $item->delete(); // soft delete

        return response()->json(['status' => true, 'message' => 'Product deleted.']);
    }

    // ── Toggle Status ─────────────────────────────────────────────────────────
    // POST /shop/products/{id}/toggle-status
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $item         = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();

        return response()->json(['status' => true, 'item_status' => $item->status, 'id' => $item->id]);
    }

    // ── Bulk Action ───────────────────────────────────────────────────────────
    // POST /shop/products/bulk
    // Body: { ids: [1,2,3], action: "activate|deactivate|delete|set_category|set_featured|reorder" }
    //       action=set_category → also requires category_id
    //       action=reorder      → requires order: [{id:1,display_order:1}, ...]
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action'       => 'required|in:activate,deactivate,delete,set_category,set_featured,unset_featured,reorder',
            'ids'          => 'required_unless:action,reorder|array|min:1',
            'ids.*'        => 'integer',
            'category_id'  => 'required_if:action,set_category|exists:item_categories,id',
            'order'        => 'required_if:action,reorder|array|min:1',
            'order.*.id'   => 'required|integer',
            'order.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shopId = $request->user()->shop_id;

        match ($request->action) {
            'activate'      => Item::where('shop_id', $shopId)->whereIn('id', $request->ids)->update(['status' => 'active']),
            'deactivate'    => Item::where('shop_id', $shopId)->whereIn('id', $request->ids)->update(['status' => 'inactive']),
            'set_featured'  => Item::where('shop_id', $shopId)->whereIn('id', $request->ids)->update(['is_featured' => true]),
            'unset_featured'=> Item::where('shop_id', $shopId)->whereIn('id', $request->ids)->update(['is_featured' => false]),
            'set_category'  => Item::where('shop_id', $shopId)->whereIn('id', $request->ids)->update(['category_id' => $request->category_id]),
            'delete'        => $this->bulkDelete($shopId, $request->ids),
            'reorder'       => $this->bulkReorder($shopId, $request->order),
        };

        return response()->json(['status' => true, 'message' => 'Bulk action applied.']);
    }

    // ── Media: Upload ─────────────────────────────────────────────────────────
    // POST /shop/products/{id}/media
    // Body (multipart/form-data): media[] files (images or video)
    public function uploadMedia(Request $request, int $id): JsonResponse
    {
        $item = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'media'   => 'required|array|min:1|max:10',
            'media.*' => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $created = $this->handleMediaUploads($request, $item);

        return response()->json([
            'status'  => true,
            'message' => count($created) . ' file(s) uploaded. Processing in background.',
            'data'    => $created,
        ], 201);
    }

    // ── Media: List ───────────────────────────────────────────────────────────
    // GET /shop/products/{id}/media
    public function listMedia(Request $request, int $id): JsonResponse
    {
        $item  = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);
        $media = ItemMedia::where('item_id', $item->id)->orderBy('sort_order')->get()
            ->map(fn($m) => $this->formatMedia($m));

        return response()->json(['status' => true, 'data' => $media]);
    }

    // ── Media: Delete ─────────────────────────────────────────────────────────
    // DELETE /shop/products/media/{mediaId}
    public function deleteMedia(Request $request, int $mediaId): JsonResponse
    {
        $media = ItemMedia::whereHas('item', fn($q) => $q->where('shop_id', $request->user()->shop_id))
            ->findOrFail($mediaId);

        $this->deleteMediaFiles($media);
        $media->delete();

        return response()->json(['status' => true, 'message' => 'Media deleted.']);
    }

    // ── Media: Reorder ────────────────────────────────────────────────────────
    // POST /shop/products/{id}/media/reorder
    // Body: { order: [{id: 1, sort_order: 0}, {id: 2, sort_order: 1}] }
    public function reorderMedia(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order'              => 'required|array|min:1',
            'order.*.id'         => 'required|integer',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $item    = Item::where('shop_id', $request->user()->shop_id)->findOrFail($id);
        $mediaIds = collect($request->order)->pluck('id');

        // Verify all belong to this item
        $count = ItemMedia::where('item_id', $item->id)->whereIn('id', $mediaIds)->count();
        if ($count !== $mediaIds->count()) {
            return response()->json(['status' => false, 'message' => 'One or more media IDs are invalid.'], 422);
        }

        foreach ($request->order as $entry) {
            ItemMedia::where('id', $entry['id'])->update(['sort_order' => $entry['sort_order']]);
        }

        return response()->json(['status' => true, 'message' => 'Media order updated.']);
    }

    // ── Media: Set Thumbnail ──────────────────────────────────────────────────
    // POST /shop/products/media/{mediaId}/set-thumbnail
    public function setThumbnail(Request $request, int $mediaId): JsonResponse
    {
        $media = ItemMedia::whereHas('item', fn($q) => $q->where('shop_id', $request->user()->shop_id))
            ->findOrFail($mediaId);

        // Unset all others for this item
        ItemMedia::where('item_id', $media->item_id)->update(['is_thumbnail' => false]);
        $media->update(['is_thumbnail' => true]);

        return response()->json(['status' => true, 'message' => 'Thumbnail set.']);
    }

    // ── Category Tree (for filter dropdown) ──────────────────────────────────
    // GET /shop/products/categories
    public function categoryTree(Request $request): JsonResponse
    {
        $shopId = $request->user()->shop_id;

        $cats = DB::table('item_categories as c')
            ->leftJoin('items as i', fn($j) => $j->on('i.category_id', '=', 'c.id')->where('i.shop_id', $shopId)->whereNull('i.deleted_at'))
            ->where('c.status', 1)
            ->groupBy('c.id', 'c.category_name', 'c.sort_order')
            ->orderBy('c.sort_order')
            ->select('c.id', 'c.category_name', DB::raw('COUNT(i.id) as item_count'))
            ->get();

        return response()->json(['status' => true, 'data' => $cats]);
    }

    public function subcategoriesByCategory(Request $request, int $categoryId): JsonResponse
    {
        $shopId = $request->user()->shop_id;

        $subcats = DB::table('item_subcategories as s')
            ->leftJoin('items as i', fn($j) => $j->on('i.subcategory_id', '=', 's.id')->where('i.shop_id', $shopId)->whereNull('i.deleted_at'))
            ->where('s.category_id', $categoryId)
            ->where('s.status', 1)
            ->whereNull('s.parent_id')
            ->groupBy('s.id', 's.name', 's.sort_order', 's.commission_percent')
            ->orderBy('s.sort_order')
            ->select('s.id', 's.name', 's.commission_percent', DB::raw('COUNT(i.id) as item_count'))
            ->get();

        return response()->json(['status' => true, 'data' => $subcats]);
    }

    // ── Private: Helpers ──────────────────────────────────────────────────────

    private function itemRules(string $presence = 'required'): array
    {
        return [
            'item_name'          => "{$presence}|string|max:150",
            'description'        => 'nullable|string|max:2000',
            'item_type'          => 'nullable|in:regular,combo',
            'price'              => 'nullable|numeric|min:0',
            'offer_price'        => 'nullable|numeric|min:0|lt:price',
            'min_quantity'       => 'nullable|integer|min:1',
            'max_quantity'       => 'nullable|integer|min:1|gte:min_quantity',
            'weight_or_piece'    => 'nullable|string|max:50',
            'allow_custom_notes' => 'nullable|boolean',
            'is_veg'             => 'nullable|boolean',
            'is_jain'            => 'nullable|boolean',
            'spice_level'        => 'nullable|in:none,mild,medium,hot,very_hot',
            'preparation_time'   => 'nullable|integer|min:1|max:300',
            'is_featured'        => 'nullable|boolean',
            'display_order'      => 'nullable|integer|min:0',
            'badge'              => 'nullable|string|max:50',
            'subcategory_id'     => 'nullable|exists:item_subcategories,id',
            'gst_percent'        => 'nullable|numeric|min:0|max:28',
            'hsn_code'           => 'nullable|string|max:20',
            'sku'                => 'nullable|string|max:100',
            'track_inventory'    => 'nullable|boolean',
            'stock_quantity'     => 'nullable|integer|min:0',
            'low_stock_alert'    => 'nullable|integer|min:0',
            'status'             => 'nullable|in:active,inactive',
        ];
    }

    private function itemPayload(Request $request, ?Item $existing = null): array
    {
        $data = [];

        $stringFields = ['item_name', 'description', 'item_type', 'weight_or_piece', 'badge', 'sku', 'spice_level', 'hsn_code', 'status'];
        foreach ($stringFields as $f) {
            if ($request->has($f)) $data[$f] = $request->input($f);
        }

        $numericFields = ['price', 'offer_price', 'min_quantity', 'max_quantity', 'preparation_time', 'display_order', 'gst_percent', 'stock_quantity', 'low_stock_alert', 'category_id', 'subcategory_id'];
        foreach ($numericFields as $f) {
            if ($request->has($f)) $data[$f] = $request->input($f);
        }

        $boolFields = ['is_veg', 'is_jain', 'is_featured', 'allow_custom_notes', 'track_inventory'];
        foreach ($boolFields as $f) {
            if ($request->has($f)) $data[$f] = $request->boolean($f);
        }

        // Auto-generate slug if item_name changed
        if (isset($data['item_name'])) {
            $data['slug'] = Str::slug($data['item_name']) . '-' . Str::random(5);
        }

        // Calculate GST split if gst_percent provided
        if (isset($data['gst_percent'])) {
            $half          = round((float) $data['gst_percent'] / 2, 2);
            $data['cgst']  = $half;
            $data['sgst']  = $half;
            $data['igst']  = (float) $data['gst_percent'];
        }

        return $data;
    }

    private function decodeVariants(Request $request): ?array
    {
        if (!$request->filled('variants')) return null;
        $v = is_string($request->variants) ? json_decode($request->variants, true) : $request->variants;
        return is_array($v) ? $v : null;
    }

    private function syncVariants(int $itemId, array $variants, array $existingIds = []): void
    {
        $incomingIds = collect($variants)->pluck('id')->filter()->toArray();

        if (!empty($existingIds)) {
            ItemVariant::whereIn('id', array_diff($existingIds, $incomingIds))
                ->update(['status' => 'inactive', 'is_default' => false]);
        }

        $hasDefault = false;
        foreach ($variants as $v) {
            $isDefault  = (bool) ($v['is_default'] ?? false);
            if ($isDefault) $hasDefault = true;

            $data = [
                'label'        => $v['label'],
                'price'        => (float) $v['price'],
                'offer_price'  => isset($v['offer_price']) && $v['offer_price'] > 0 ? (float) $v['offer_price'] : null,
                'gst_percent'  => (float) ($v['gst_percent'] ?? 0),
                'hsn_code'     => $v['hsn_code'] ?? null,
                'min_quantity' => (int) ($v['min_quantity'] ?? 1),
                'is_default'   => $isDefault,
                'status'       => 'active',
            ];

            if (!empty($v['id'])) {
                ItemVariant::where('id', $v['id'])->update($data);
            } else {
                ItemVariant::create(array_merge($data, ['item_id' => $itemId]));
            }
        }

        if (!$hasDefault) {
            ItemVariant::where('item_id', $itemId)->where('status', 'active')
                ->first()?->update(['is_default' => true]);
        }
    }

    private function handleMediaUploads(Request $request, Item $item): array
    {
        $created  = [];
        $files    = $request->file('media') ?? [];
        $nextOrder = ItemMedia::where('item_id', $item->id)->max('sort_order') ?? -1;

        foreach ($files as $file) {
            $fileType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
            $dir      = 'item_raw/' . $item->id;
            $origPath = $file->store($dir, 'public');

            $media = ItemMedia::create([
                'item_id'            => $item->id,
                'file_type'          => $fileType,
                'original_path'      => $origPath,
                'mime_type'          => $file->getMimeType(),
                'size_bytes'         => $file->getSize(),
                'sort_order'         => ++$nextOrder,
                'is_thumbnail'       => ItemMedia::where('item_id', $item->id)->doesntExist(),
                'processing_status'  => 'pending',
            ]);

            ProcessItemMedia::dispatch($media->id);

            $created[] = $this->formatMedia($media);
        }

        return $created;
    }

    private function formatMedia(ItemMedia $m): array
    {
        return [
            'id'                 => $m->id,
            'item_id'            => $m->item_id,
            'file_type'          => $m->file_type,
            'url'                => $m->url,
            'thumb_url'          => $m->thumb_url,
            'is_thumbnail'       => $m->is_thumbnail,
            'sort_order'         => $m->sort_order,
            'processing_status'  => $m->processing_status,
            'processing_error'   => $m->processing_error,
        ];
    }

    private function deleteMediaFiles(ItemMedia $m): void
    {
        $disk = Storage::disk('public');
        foreach ([$m->original_path, $m->processed_path, $m->thumb_path] as $path) {
            if ($path) $disk->delete($path);
        }
    }

    private function appendMedia(Item $item): Item
    {
        if ($item->relationLoaded('readyMedia')) {
            $item->media_urls    = $item->readyMedia->map(fn($m) => $this->formatMedia($m))->toArray();
            $item->thumbnail_url = $item->readyMedia->firstWhere('is_thumbnail', true)?->thumb_url
                ?? $item->readyMedia->first()?->url;
        }

        // Legacy JSON images fallback
        if (empty($item->media_urls)) {
            $item->image_urls = collect($item->images ?? [])->map(fn($p) => asset('storage/' . $p))->toArray();
        }

        return $item;
    }

    private function freshItem(int $id): Item
    {
        return tap(
            Item::with(['category:id,category_name', 'subcategory:id,name', 'variants', 'media', 'comboComponents'])->find($id),
            fn($i) => $this->appendMedia($i)
        );
    }

    private function bulkDelete(int $shopId, array $ids): void
    {
        $items = Item::where('shop_id', $shopId)->whereIn('id', $ids)->with('media')->get();
        foreach ($items as $item) {
            foreach ($item->media as $m) $this->deleteMediaFiles($m);
            $item->delete();
        }
    }

    private function bulkReorder(int $shopId, array $order): void
    {
        foreach ($order as $entry) {
            Item::where('shop_id', $shopId)
                ->where('id', $entry['id'])
                ->update(['display_order' => $entry['display_order']]);
        }
    }
}
