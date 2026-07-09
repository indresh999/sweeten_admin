<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\AppHomeFilter;
use App\Models\ItemView;
use App\Services\CommissionService;

class ItemController extends Controller
{
    // ── List by Owner ──────────────────────────────────────
    public function listByOwner(Request $request, int $shopId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        $items = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'variants' => fn($q) => $q->where('status', 'active')
                ->select(['id', 'item_id', 'label', 'price', 'offer_price', 'gst_percent', 'is_default', 'status']),
        ])
        ->where('shop_id', $shopId)
        ->latest()
        ->paginate($perPage);

        $items->getCollection()->transform(fn($item) => $this->appendImageUrls($item));

        return response()->json(['status' => true, 'data' => $items]);
    }

    // ── Show Single Item ───────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $item = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'variants'       => fn($q) => $q->where('status', 'active')->orderByDesc('is_default'),
            'owner:shop_id,restaurant_name,city,status',
        ])->find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found.'], 404);
        }

        // Log view asynchronously — best-effort, don't fail the response
        try {
            $user    = request()->bearerToken() ? \App\Models\AppUser::where('api_token', request()->bearerToken())->first() : null;
            $lastOrder = $user ? \App\Models\Order::where('user_id', $user->id)->latest()->first(['city','state']) : null;
            ItemView::create([
                'item_id' => $item->id,
                'shop_id' => $item->shop_id,
                'user_id' => $user?->id,
                'city'    => $lastOrder?->city,
                'state'   => $lastOrder?->state,
            ]);
        } catch (\Throwable) {}

        $item = $this->applyCommission($this->appendImageUrls($item));
        $default = $item->variants->firstWhere('is_default', true) ?? $item->variants->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $item->id,
                'shop_id'          => $item->shop_id,
                'item_name'        => $item->item_name,
                'description'      => $item->description,
                'status'           => $item->status,
                'is_veg'           => (bool) $item->is_veg,
                'is_jain'          => (bool) $item->is_jain,
                'is_featured'      => (bool) $item->is_featured,
                'spice_level'      => $item->spice_level,
                'preparation_time' => $item->preparation_time,
                'image_urls'       => $item->image_urls,
                'category'         => $item->category ? ['id' => $item->category->id, 'name' => $item->category->category_name] : null,
                'subcategory'      => $item->subcategory ? ['id' => $item->subcategory->id, 'name' => $item->subcategory->name] : null,
                'owner'            => $item->owner,
                'default_variant'  => $default,
                'variants'         => $item->variants,
            ],
        ]);
    }

    // ── Store (Add Item) ───────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $variants = json_decode($request->input('variants', '[]'), true);
        if (!is_array($variants) || empty($variants)) {
            return response()->json(['status' => false, 'message' => 'At least one variant is required.'], 422);
        }

        $validator = Validator::make(array_merge($request->all(), ['variants' => $variants]), [
            'shop_id'                  => 'required|exists:app_owner_shops,shop_id',
            'category_id'              => 'required|exists:item_categories,id',
            'subcategory_id'           => 'nullable|exists:item_subcategories,id',
            'item_name'                => 'required|string|max:150',
            'description'              => 'nullable|string|max:1000',
            'is_veg'                   => 'nullable|boolean',
            'is_jain'                  => 'nullable|boolean',
            'spice_level'              => 'nullable|in:none,mild,medium,hot,very_hot',
            'preparation_time'         => 'nullable|integer|min:1|max:300',
            'is_featured'              => 'nullable|boolean',
            'status'                   => 'nullable|in:active,inactive',
            'variants'                 => 'required|array|min:1',
            'variants.*.label'         => 'required|string|max:100',
            'variants.*.price'         => 'required|numeric|min:0',
            'variants.*.offer_price'   => 'nullable|numeric|min:0',
            'variants.*.gst_percent'   => 'required|numeric|min:0|max:100',
            'variants.*.hsn_code'      => 'nullable|string|max:20',
            'variants.*.is_default'    => 'nullable|boolean',
            'images.*'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $imagePaths = $this->storeImages($request);

            $item = Item::create([
                'shop_id'          => $request->shop_id,
                'category_id'      => $request->category_id,
                'subcategory_id'   => $request->subcategory_id,
                'item_name'        => $request->item_name,
                'description'      => $request->description,
                'is_veg'           => $request->boolean('is_veg', true),
                'is_jain'          => $request->boolean('is_jain'),
                'spice_level'      => $request->spice_level,
                'preparation_time' => $request->preparation_time,
                'is_featured'      => $request->boolean('is_featured'),
                'status'           => $request->get('status', 'active'),
                'images'           => $imagePaths,
            ]);

            $this->syncVariants($item->id, $variants);

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Item created successfully.',
                'data'    => $item->load('variants'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ItemController] store failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create item.'], 500);
        }
    }

    // ── Update Item ────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $item     = Item::with('variants')->findOrFail($id);
        $variants = $request->filled('variants') ? json_decode($request->variants, true) : null;

        $rules = [
            'category_id'       => 'sometimes|exists:item_categories,id',
            'subcategory_id'    => 'nullable|exists:item_subcategories,id',
            'item_name'         => 'sometimes|string|max:150',
            'description'       => 'nullable|string|max:1000',
            'is_veg'            => 'nullable|boolean',
            'is_jain'           => 'nullable|boolean',
            'spice_level'       => 'nullable|in:none,mild,medium,hot,very_hot',
            'preparation_time'  => 'nullable|integer|min:1',
            'is_featured'       => 'nullable|boolean',
            'status'            => 'nullable|in:active,inactive',
            'images.*'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ];
        if ($variants !== null) {
            $rules['variants']                = 'array|min:1';
            $rules['variants.*.label']        = 'required|string|max:100';
            $rules['variants.*.price']        = 'required|numeric|min:0';
            $rules['variants.*.offer_price']  = 'nullable|numeric|min:0';
            $rules['variants.*.gst_percent']  = 'required|numeric|min:0|max:100';
        }

        $mergedInput = $variants !== null
            ? array_merge($request->all(), ['variants' => $variants])
            : $request->all();

        $validator = Validator::make($mergedInput, $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Handle images
            $existingImages = $request->filled('existing_images')
                ? json_decode($request->existing_images, true)
                : [];
            $currentImages = $item->images ?? [];
            $finalImages   = [];

            foreach ($currentImages as $old) {
                $fullUrl = asset('storage/' . $old);
                if (in_array($fullUrl, (array) $existingImages) || in_array($old, (array) $existingImages)) {
                    $finalImages[] = $old;
                } else {
                    Storage::disk('public')->delete($old);
                }
            }
            foreach ($this->storeImages($request) as $new) {
                $finalImages[] = $new;
            }

            $updateData = array_filter([
                'category_id'      => $request->category_id,
                'subcategory_id'   => $request->subcategory_id,
                'item_name'        => $request->item_name,
                'description'      => $request->description,
                'spice_level'      => $request->spice_level,
                'preparation_time' => $request->preparation_time,
                'status'           => $request->status,
                'images'           => count($finalImages) ? $finalImages : null,
            ], fn($v) => $v !== null);

            if ($request->has('is_veg'))      $updateData['is_veg']      = $request->boolean('is_veg');
            if ($request->has('is_jain'))     $updateData['is_jain']     = $request->boolean('is_jain');
            if ($request->has('is_featured')) $updateData['is_featured'] = $request->boolean('is_featured');

            $item->update($updateData);

            if ($variants !== null) {
                $this->syncVariants($item->id, $variants, $item->variants->pluck('id')->toArray());
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Item updated.', 'data' => $item->fresh()->load('variants')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ItemController] update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to update item.'], 500);
        }
    }

    // ── Delete ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        foreach ($item->images ?? [] as $img) Storage::disk('public')->delete($img);
        $item->delete();
        return response()->json(['status' => true, 'message' => 'Item deleted.']);
    }

    // ── Toggle Status ──────────────────────────────────────
    public function toggleStatus(int $id): JsonResponse
    {
        $item         = Item::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();
        return response()->json(['status' => true, 'item_status' => $item->status]);
    }

    // ── Items by Category ──────────────────────────────────
    public function itemsByCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:item_categories,id',
            'shop_id'     => 'nullable|exists:app_owner_shops,shop_id',
            'is_veg'      => 'nullable|boolean',
            'sort'        => 'nullable|in:price,rating,popular,newest',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $query = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'owner:shop_id,restaurant_name,city',
            'variants' => fn($q) => $q->where('status', 'active')
                ->select(['id', 'item_id', 'label', 'price', 'offer_price', 'gst_percent', 'is_default', 'status']),
        ])
        ->where('category_id', $request->category_id)
        ->where('status', 'active');

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->boolean('is_veg')) {
            $query->where('is_veg', true);
        }

        // Prioritize user's shop items if shop_id provided
        if ($request->filled('shop_id')) {
            $query->orderByRaw('shop_id = ? DESC', [$request->shop_id]);
        }

        // Apply sorting
        $sort = $request->input('sort', 'popular');
        switch ($sort) {
            case 'price':
                $query->orderBy('price');
                break;
            case 'rating':
                $query->orderByDesc('rating_avg');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'popular':
            default:
                $query->orderByDesc('total_sold')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('rating_avg');
                break;
        }

        $items = $query->paginate($request->integer('per_page', 30));
        $items->getCollection()->transform(fn($i) => $this->appendImageUrls($i));

        // Subcategories for this category
        $subcategories = \App\Models\ItemSubcategory::where('category_id', $request->category_id)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->select('id', 'name', 'slug')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $items,
            'subcategories' => $subcategories,
        ]);
    }

    // ── Items by Subcategory ───────────────────────────────
    public function itemsBySubcategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'shop_id'        => 'nullable|exists:app_owner_shops,shop_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $items = Item::with([
            'category:id,category_name,commission_percent,commission_type',
            'subcategory:id,name,commission_percent,commission_type',
            'owner:shop_id,restaurant_name',
            'variants' => fn($q) => $q->where('status', 'active'),
        ])
        ->where('subcategory_id', $request->subcategory_id)
        ->where('status', 'active')
        ->when($request->shop_id, fn($q) => $q->orderByRaw('shop_id = ? DESC', [$request->shop_id]))
        ->orderByDesc('id')
        ->get()
        ->map(fn($i) => $this->applyCommission($this->appendImageUrls($i)));

        return response()->json(['status' => true, 'data' => $items]);
    }

    // ── Similar Items ──────────────────────────────────────
    public function similarItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['item_id' => 'required|exists:items,id']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $item  = Item::findOrFail($request->item_id);
        $items = Item::with(['variants', 'owner:shop_id,restaurant_name'])
            ->where('subcategory_id', $item->subcategory_id)
            ->where('id', '!=', $item->id)
            ->where('status', 'active')
            ->limit(8)
            ->get()
            ->map(fn($i) => $this->appendImageUrls($i));

        return response()->json(['status' => true, 'data' => $items]);
    }

    // ── App Home Filters ───────────────────────────────────
    public function getAppHomeFilter(): JsonResponse
    {
        return response()->json(['status' => true, 'data' => AppHomeFilter::all()]);
    }

    // ── Private Helpers ────────────────────────────────────
    private function storeImages(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $paths[] = $img->store('items', 'public');
            }
        }
        return $paths;
    }

    private function appendImageUrls(Item $item): Item
    {
        $images = is_array($item->images)
            ? $item->images
            : json_decode($item->images ?? '[]', true);

        $item->image_urls = collect($images ?? [])
            ->map(fn($p) => asset('storage/' . $p))
            ->toArray();

        return $item;
    }

    private function syncVariants(int $itemId, array $variants, array $existingIds = []): void
    {
        $incomingIds = collect($variants)->pluck('id')->filter()->toArray();

        // Deactivate removed variants
        if (!empty($existingIds)) {
            ItemVariant::whereIn('id', array_diff($existingIds, $incomingIds))
                ->update(['status' => 'inactive', 'is_default' => false]);
        }

        $hasDefault = false;
        foreach ($variants as $v) {
            if (!empty($v['is_default'])) $hasDefault = true;
            $data = [
                'label'       => $v['label'],
                'price'       => (float) $v['price'],
                'offer_price' => isset($v['offer_price']) && $v['offer_price'] > 0 ? (float) $v['offer_price'] : null,
                'gst_percent' => (float) ($v['gst_percent'] ?? 0),
                'hsn_code'    => $v['hsn_code'] ?? null,
                'is_default'  => (bool) ($v['is_default'] ?? false),
                'status'      => 'active',
            ];
            if (!empty($v['id'])) {
                ItemVariant::where('id', $v['id'])->update($data);
            } else {
                ItemVariant::create(array_merge($data, ['item_id' => $itemId]));
            }
        }

        if (!$hasDefault) {
            ItemVariant::where('item_id', $itemId)->where('status', 'active')->first()?->update(['is_default' => true]);
        }
    }

    private function applyCommission(Item $item): Item
    {
        $item->variants->transform(function ($v) use ($item) {
            $price            = (float) ($v->offer_price ?? $v->price ?? 0);
            $commission       = CommissionService::getCommissionDetails($item, $price);
            $commissionAmount = CommissionService::calculateCommission($price, $commission);
            $v->commission_type   = $commission['type'];
            $v->commission_value  = $commission['value'];
            $v->commission_amount = round($commissionAmount, 2);
            $v->final_price       = round($price + $commissionAmount, 2);
            return $v;
        });
        return $item;
    }
}
