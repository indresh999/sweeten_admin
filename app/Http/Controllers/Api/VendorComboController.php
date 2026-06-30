<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessItemMedia;
use App\Models\Item;
use App\Models\ItemComboComponent;
use App\Models\ItemMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorComboController extends Controller
{
    // ── List Combos ───────────────────────────────────────────────────────────
    // GET /shop/combos?status=active&q=bundle&per_page=20
    public function index(Request $request): JsonResponse
    {
        $shop    = $request->user();
        $perPage = $request->integer('per_page', 20);

        $combos = Item::with(['comboComponents', 'readyMedia', 'category:id,category_name'])
            ->where('shop_id', $shop->shop_id)
            ->where('item_type', 'combo')
            ->when($request->filled('q'), fn($q) => $q->where('item_name', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $combos->getCollection()->transform(fn($c) => $this->formatCombo($c));

        return response()->json(['status' => true, 'data' => $combos]);
    }

    // ── Create Combo ──────────────────────────────────────────────────────────
    // POST /shop/combos
    // Body (multipart/form-data or JSON):
    //   item_name, description, price, offer_price, category_id, subcategory_id
    //   badge, display_order, is_featured, status
    //   components (JSON): [{item_id, variant_id?, quantity, display_label?}]
    //   media[] (optional files)
    public function store(Request $request): JsonResponse
    {
        $components = $this->decodeJson($request, 'components');

        $validator = Validator::make(
            array_merge($request->all(), ['components' => $components]),
            [
                'item_name'              => 'required|string|max:150',
                'description'            => 'nullable|string|max:2000',
                'price'                  => 'required|numeric|min:0',
                'offer_price'            => 'nullable|numeric|min:0|lt:price',
                'category_id'            => 'nullable|exists:item_categories,id',
                'subcategory_id'         => 'nullable|exists:item_subcategories,id',
                'badge'                  => 'nullable|string|max:50',
                'display_order'          => 'nullable|integer|min:0',
                'is_featured'            => 'nullable|boolean',
                'status'                 => 'nullable|in:active,inactive',
                'is_veg'                 => 'nullable|boolean',
                'gst_percent'            => 'nullable|numeric|min:0|max:28',
                'hsn_code'               => 'nullable|string|max:20',
                'sku'                    => 'nullable|string|max:100',
                'components'             => 'required|array|min:1',
                'components.*.item_id'   => 'required|exists:items,id',
                'components.*.variant_id'=> 'nullable|exists:item_variants,id',
                'components.*.quantity'  => 'nullable|integer|min:1|max:100',
                'components.*.display_label' => 'nullable|string|max:120',
                'media'                  => 'nullable|array|max:6',
                'media.*'                => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $item = Item::create([
                'shop_id'        => $request->user()->shop_id,
                'item_type'      => 'combo',
                'item_name'      => $request->item_name,
                'slug'           => Str::slug($request->item_name) . '-' . Str::random(5),
                'description'    => $request->description,
                'price'          => $request->price,
                'offer_price'    => $request->offer_price,
                'category_id'    => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'badge'          => $request->badge,
                'display_order'  => $request->integer('display_order', 0),
                'is_featured'    => $request->boolean('is_featured'),
                'is_veg'         => $request->boolean('is_veg', true),
                'gst_percent'    => $request->gst_percent,
                'hsn_code'       => $request->hsn_code,
                'sku'            => $request->sku,
                'status'         => $request->get('status', 'active'),
            ]);

            $this->syncComponents($item->id, $components);
            $this->handleMediaUploads($request, $item);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Combo created successfully.',
                'data'    => $this->freshCombo($item->id),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VendorCombo] store failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create combo.'], 500);
        }
    }

    // ── Get Single Combo ──────────────────────────────────────────────────────
    // GET /shop/combos/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $combo = Item::with(['comboComponents', 'media', 'category:id,category_name', 'subcategory:id,name'])
            ->where('shop_id', $request->user()->shop_id)
            ->where('item_type', 'combo')
            ->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->formatCombo($combo)]);
    }

    // ── Update Combo ──────────────────────────────────────────────────────────
    // PUT /shop/combos/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $combo      = Item::where('shop_id', $request->user()->shop_id)->where('item_type', 'combo')->findOrFail($id);
        $components = $this->decodeJson($request, 'components');

        $validator = Validator::make(
            array_merge($request->all(), ['components' => $components]),
            [
                'item_name'              => 'sometimes|string|max:150',
                'description'            => 'nullable|string|max:2000',
                'price'                  => 'sometimes|numeric|min:0',
                'offer_price'            => 'nullable|numeric|min:0',
                'category_id'            => 'nullable|exists:item_categories,id',
                'subcategory_id'         => 'nullable|exists:item_subcategories,id',
                'badge'                  => 'nullable|string|max:50',
                'display_order'          => 'nullable|integer|min:0',
                'is_featured'            => 'nullable|boolean',
                'is_veg'                 => 'nullable|boolean',
                'status'                 => 'nullable|in:active,inactive',
                'gst_percent'            => 'nullable|numeric|min:0|max:28',
                'components'             => 'nullable|array|min:1',
                'components.*.item_id'   => 'required|exists:items,id',
                'components.*.variant_id'=> 'nullable|exists:item_variants,id',
                'components.*.quantity'  => 'nullable|integer|min:1|max:100',
                'components.*.display_label' => 'nullable|string|max:120',
                'media'                  => 'nullable|array|max:6',
                'media.*'                => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200',
                'remove_media'           => 'nullable|array',
                'remove_media.*'         => 'integer|exists:item_media,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $updateData = array_filter([
                'item_name'      => $request->item_name,
                'description'    => $request->description,
                'price'          => $request->price,
                'offer_price'    => $request->offer_price,
                'category_id'    => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'badge'          => $request->badge,
                'display_order'  => $request->display_order,
                'status'         => $request->status,
                'gst_percent'    => $request->gst_percent,
                'hsn_code'       => $request->hsn_code,
                'sku'            => $request->sku,
            ], fn($v) => $v !== null);

            foreach (['is_featured', 'is_veg'] as $bool) {
                if ($request->has($bool)) $updateData[$bool] = $request->boolean($bool);
            }
            if (isset($updateData['item_name'])) {
                $updateData['slug'] = Str::slug($updateData['item_name']) . '-' . Str::random(5);
            }

            $combo->update($updateData);

            if ($components !== null) {
                $this->syncComponents($combo->id, $components);
            }

            if ($request->filled('remove_media')) {
                $toDelete = ItemMedia::where('item_id', $combo->id)->whereIn('id', $request->remove_media)->get();
                foreach ($toDelete as $m) {
                    $this->deleteMediaFiles($m);
                    $m->delete();
                }
            }

            $this->handleMediaUploads($request, $combo);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Combo updated.',
                'data'    => $this->freshCombo($combo->id),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VendorCombo] update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to update combo.'], 500);
        }
    }

    // ── Delete Combo ──────────────────────────────────────────────────────────
    // DELETE /shop/combos/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $combo = Item::where('shop_id', $request->user()->shop_id)->where('item_type', 'combo')->findOrFail($id);

        foreach ($combo->media as $m) $this->deleteMediaFiles($m);
        $combo->delete();

        return response()->json(['status' => true, 'message' => 'Combo deleted.']);
    }

    // ── Toggle Status ─────────────────────────────────────────────────────────
    // POST /shop/combos/{id}/toggle-status
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $combo         = Item::where('shop_id', $request->user()->shop_id)->where('item_type', 'combo')->findOrFail($id);
        $combo->status = $combo->status === 'active' ? 'inactive' : 'active';
        $combo->save();

        return response()->json(['status' => true, 'combo_status' => $combo->status]);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function syncComponents(int $comboId, array $components): void
    {
        // Replace all — simpler and avoids stale records
        ItemComboComponent::where('combo_id', $comboId)->delete();

        foreach ($components as $c) {
            ItemComboComponent::create([
                'combo_id'      => $comboId,
                'item_id'       => $c['item_id'],
                'variant_id'    => $c['variant_id'] ?? null,
                'quantity'      => (int) ($c['quantity'] ?? 1),
                'display_label' => $c['display_label'] ?? null,
            ]);
        }
    }

    private function formatCombo(Item $combo): array
    {
        $mediaList = $combo->relationLoaded('readyMedia')
            ? $combo->readyMedia
            : ($combo->relationLoaded('media') ? $combo->media->where('processing_status', 'done') : collect());

        return [
            'id'              => $combo->id,
            'item_name'       => $combo->item_name,
            'description'     => $combo->description,
            'item_type'       => $combo->item_type,
            'price'           => $combo->price,
            'offer_price'     => $combo->offer_price,
            'is_veg'          => $combo->is_veg,
            'is_featured'     => $combo->is_featured,
            'badge'           => $combo->badge,
            'display_order'   => $combo->display_order,
            'status'          => $combo->status,
            'category'        => $combo->category ? ['id' => $combo->category->id, 'name' => $combo->category->category_name] : null,
            'media'           => $mediaList->map(fn($m) => [
                'id'          => $m->id,
                'url'         => $m->url,
                'thumb_url'   => $m->thumb_url,
                'is_thumbnail'=> $m->is_thumbnail,
                'sort_order'  => $m->sort_order,
                'status'      => $m->processing_status,
            ])->values(),
            'thumbnail_url'   => $mediaList->firstWhere('is_thumbnail', true)?->thumb_url ?? $mediaList->first()?->url,
            'components'      => $combo->relationLoaded('comboComponents')
                ? $combo->comboComponents->map(fn($c) => [
                    'id'            => $c->id,
                    'item_id'       => $c->item_id,
                    'item_name'     => $c->item?->item_name,
                    'variant_id'    => $c->variant_id,
                    'variant_label' => $c->variant?->label,
                    'quantity'      => $c->quantity,
                    'display_label' => $c->display_label,
                    'unit_price'    => $c->variant?->effectivePrice() ?? $c->item?->effectivePrice(),
                ])->values()
                : [],
            'created_at'      => $combo->created_at,
            'updated_at'      => $combo->updated_at,
        ];
    }

    private function handleMediaUploads(Request $request, Item $item): void
    {
        $nextOrder = ItemMedia::where('item_id', $item->id)->max('sort_order') ?? -1;

        foreach ($request->file('media') ?? [] as $file) {
            $fileType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
            $origPath = $file->store('item_raw/' . $item->id, 'public');

            $media = ItemMedia::create([
                'item_id'           => $item->id,
                'file_type'         => $fileType,
                'original_path'     => $origPath,
                'mime_type'         => $file->getMimeType(),
                'size_bytes'        => $file->getSize(),
                'sort_order'        => ++$nextOrder,
                'is_thumbnail'      => ItemMedia::where('item_id', $item->id)->doesntExist(),
                'processing_status' => 'pending',
            ]);

            ProcessItemMedia::dispatch($media->id);
        }
    }

    private function deleteMediaFiles(ItemMedia $m): void
    {
        $disk = Storage::disk('public');
        foreach ([$m->original_path, $m->processed_path, $m->thumb_path] as $path) {
            if ($path) $disk->delete($path);
        }
    }

    private function freshCombo(int $id): array
    {
        $combo = Item::with(['comboComponents', 'readyMedia', 'category:id,category_name', 'subcategory:id,name'])->find($id);
        return $this->formatCombo($combo);
    }

    private function decodeJson(Request $request, string $key): ?array
    {
        if (!$request->filled($key)) return null;
        $val = is_string($request->input($key)) ? json_decode($request->input($key), true) : $request->input($key);
        return is_array($val) ? $val : null;
    }
}
