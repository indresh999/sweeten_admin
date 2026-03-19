<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemSubcategory;
use App\Models\AppHomeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\CommissionService;

class ItemController extends Controller
{
    
    // ======================================================
    // LIST ITEMS BY OWNER
    // ======================================================
    public function listByOwner(Request $request, $shop_id)
    {
        
        $perPage = $request->get('per_page', 15);

        $items = Item::with([
                'category:id,category_name',
                'subcategory:id,name',
                'variants' => function ($q) {
                    $q->where('status', 'active')
                    ->select([
                        'id',
                        'item_id',
                        'label',
                        'price',
                        'offer_price',
                        'gst_percent',
                        'cgst',
                        'sgst',
                        'igst',
                        'hsn_code',
                        'is_default',
                        'status'
                    ]);
                },
                'defaultVariant:id,item_id,label,price,offer_price,gst_percent,hsn_code,is_default'
            ])
         //   ->where('shop_id', $shop_id)
            ->select([
                'id',
                'shop_id',
                'category_id',
                'subcategory_id',
                'item_name',
                'description',
                'status',
                'images',
                'weight_or_piece',
                'created_at'
            ])
            ->latest()
            ->paginate($perPage);

            $items->getCollection()->transform(function ($item) {
                return $this->applyCommissionToVariants($item);
            });

        return response()->json([
            'status' => true,
            'data'   => $items
            
        ]);
    }
    // ======================================================
    // STORE ITEM (FORM-DATA + IMAGES + VARIANTS + GST)
    // ======================================================
    public function store(Request $request)
    {
        // variants comes as JSON string in form-data
        $variants = json_decode($request->variants, true);

        if (!is_array($variants)) {
            return response()->json(['message' => 'Invalid variants format'], 422);
        }

        $validator = Validator::make(
            array_merge($request->all(), ['variants' => $variants]),
            [
                'shop_id'     => 'required|exists:app_owner_shops,shop_id',
                'category_id' => 'required|exists:item_categories,id',
                'subcategory_id' => 'nullable|exists:item_subcategories,id',
                'item_name'   => 'required|string|max:100',
                'description' => 'nullable|string',
                'status'      => 'nullable|in:active,inactive',

                // VARIANTS
                'variants'               => 'required|array|min:1',
                'variants.*.label'       => 'required|string|max:50',
                'variants.*.price'       => 'required|numeric|min:0',
                'variants.*.gst_percent' => 'required|numeric|min:0',
                'variants.*.hsn_code'    => 'nullable|string|max:20',
                'variants.*.is_default'  => 'nullable|boolean',

                // IMAGES
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        DB::beginTransaction();

        try {

            // ------------------ IMAGE UPLOAD ------------------
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('items', 'public');
                }
            }

            // ------------------ CREATE ITEM ------------------
            $item = Item::create([
                'shop_id'        => $request->shop_id,
                'category_id'    => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'item_name'      => $request->item_name,
                'description'    => $request->description,
                'status'         => $request->status ?? 'active',
                'images'         => $imagePaths
            ]);

            // ------------------ CREATE VARIANTS ------------------
            foreach ($variants as $variant) {
                ItemVariant::create([
                    'item_id'     => $item->id,
                    'label'       => $variant['label'],
                    'price'       => $variant['price'],
                    'offer_price' => $variant['offer_price'] ?? null,
                    'gst_percent' => $variant['gst_percent'],
                    'hsn_code'    => $variant['hsn_code'] ?? null,
                    'is_default'  => $variant['is_default'] ?? false,
                    'status'      => 'active',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Item created successfully',
                'data'    => $item->load('variants')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }

    // ======================================================
    // UPDATE ITEM + IMAGES + VARIANTS
    // ======================================================
    public function update(Request $request, $id)
    {
        $item = Item::with('variants')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $variants = $request->variants
            ? json_decode($request->variants, true)
            : [];

        // ------------------ VALIDATION ------------------
        $validator = Validator::make(
            array_merge($request->all(), ['variants' => $variants]),
            [
                'category_id' => 'required|exists:item_categories,id',
                'subcategory_id' => 'nullable|exists:item_subcategories,id',
                'item_name' => 'required|string|max:100',
                'description' => 'nullable|string',

                'variants' => 'required|array|min:1',
                'variants.*.label' => 'required|string|max:50',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.gst_percent' => 'required|numeric|min:0',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {

            // =====================================================
            // IMAGE SYNC (PARTIAL REMOVE + ADD SUPPORT)
            // =====================================================

            $existingImages = $request->existing_images
                ? json_decode($request->existing_images, true)
                : [];

            $currentImages = $item->images ?? [];

            $finalImages = [];

            // Keep only selected existing images
            foreach ($currentImages as $old) {

                $fullUrl = asset('storage/' . $old);

                if (in_array($fullUrl, $existingImages)) {
                    $finalImages[] = $old;
                } else {
                    Storage::disk('public')->delete($old);
                }
            }

            // Add new uploaded images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $finalImages[] = $image->store('items', 'public');
                }
            }

            $item->images = $finalImages;

            // =====================================================
            // UPDATE ITEM
            // =====================================================

            $item->update([
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'item_name' => $request->item_name,
                'description' => $request->description,
                'status' => $request->status ?? $item->status,
            ]);

            // =====================================================
            // VARIANT SYNC
            // =====================================================

            $existingIds = $item->variants->pluck('id')->toArray();
            $incomingIds = collect($variants)->pluck('id')->filter()->toArray();

            // Soft delete removed variants
            ItemVariant::whereIn('id', array_diff($existingIds, $incomingIds))
                ->update([
                    'status' => 'inactive',
                    'is_default' => false
                ]);

            $hasDefault = false;

            foreach ($variants as $variant) {

                // Ensure only one default
                if (!empty($variant['is_default'])) {
                    $hasDefault = true;
                }

                if (!empty($variant['id'])) {

                    ItemVariant::where('id', $variant['id'])->update([
                        'label' => $variant['label'],
                        'price' => $variant['price'],
                        'offer_price' => $variant['offer_price'] ?? null,
                        'gst_percent' => $variant['gst_percent'],
                        'hsn_code' => $variant['hsn_code'] ?? null,
                        'is_default' => $variant['is_default'] ?? false,
                        'status' => 'active',
                    ]);

                } else {

                    ItemVariant::create([
                        'item_id' => $item->id,
                        'label' => $variant['label'],
                        'price' => $variant['price'],
                        'offer_price' => $variant['offer_price'] ?? null,
                        'gst_percent' => $variant['gst_percent'],
                        'hsn_code' => $variant['hsn_code'] ?? null,
                        'is_default' => $variant['is_default'] ?? false,
                        'status' => 'active',
                    ]);
                }
            }

            // If no default selected, auto assign first active
            if (!$hasDefault) {
                ItemVariant::where('item_id', $item->id)
                    ->where('status', 'active')
                    ->first()
                    ?->update(['is_default' => true]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Item updated successfully',
                'data' => $item->fresh()->load('variants')
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ======================================================
    // DELETE ITEM
    // ======================================================
    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return response()->json(['message'=>'Item deleted']);
    }

    // ======================================================
    // DELETE VARIANT (SOFT DELETE)
    // ======================================================
    public function deleteVariant($id)
    {
        ItemVariant::where('id',$id)
            ->update(['status'=>'inactive','is_default'=>false]);

        return response()->json(['message'=>'Variant deleted']);
    }
    public function itemsBySubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'shop_id'        => 'nullable|exists:app_owner_shops,shop_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $subcategoryId = $request->subcategory_id;
        $shopId        = $request->shop_id;

        $items = Item::with([
            // ✅ LOAD COMMISSION FIELDS
            'category:id,category_name,commission_percent,commission_type',
            'subcategory:id,name,commission_percent,commission_type',
            'owner',

            // ✅ VARIANTS
            'variants' => function ($q) {
                $q->where('status', 'active');
            },

            // ✅ DEFAULT VARIANT
            'defaultVariant:id,item_id,label,price,offer_price,gst_percent,hsn_code,is_default'
        ])
        ->where('subcategory_id', $subcategoryId)
        ->where('status', 'active')
        ->when($shopId, function ($q) use ($shopId) {
            $q->orderByRaw('shop_id = ? DESC', [$shopId]);
        })
        ->orderBy('id', 'DESC')
        ->get();

        // ✅ APPLY COMMISSION
        $items->transform(function ($item) {
            return $this->applyCommissionToVariants($item);
        });

        return response()->json([
            'data' => $items
        ], 200);
    }
    public function similarItems(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id'
        ]);

        $item = Item::find($request->item_id);

        $items = Item::with(['variants','owner'])
            ->where('subcategory_id', $item->subcategory_id)
            ->where('id', '!=', $item->id)
            ->where('status','active')
            ->limit(6)
            ->get();

             $items->transform(function ($item) {
                return $this->applyCommissionToVariants($item);
            });

        return response()->json([
            'data' => $items
        ]);
    }

  
    public function show($id)
{
    $item = Item::with([
        'category:id,category_name',
        'subcategory:id,name',
        'variants' => function ($q) {
            $q->where('status', 'active')
              ->orderByDesc('is_default');
        }
    ])
    ->where('id', $id)
    ->first();

    if (!$item) {
        return response()->json([
            'status' => false,
            'message' => 'Item not found'
        ], 404);
    }

    // ✅ APPLY COMMISSION
    $item = $this->applyCommissionToVariants($item);

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $item->id,
            'shop_id' => $item->shop_id,
            'item_name' => $item->item_name,
            'description' => $item->description,
            'status' => $item->status,
            'images' => $item->image_urls,

            'category' => [
                'id' => $item->category?->id,
                'name' => $item->category?->category_name,
            ],

            'subcategory' => [
                'id' => $item->subcategory?->id,
                'name' => $item->subcategory?->name,
            ],

            // ✅ DIRECT VARIANTS (already modified)
            'variants' => $item->variants,

            'default_variant' => $item->variants->firstWhere('is_default', true)
            
        ]
    ]);
}

    public function getAppHomeFilter()
    {
        $filters = AppHomeFilter::get();
        return response()->json(['data' => $filters], 200);
    }

private function applyCommissionToVariants($item)
{
    $item->variants->transform(function ($variant) use ($item) {

        $price = $variant->offer_price ?? $variant->price;

        $commission = CommissionService::getCommissionDetails($item, $price);

        $commissionAmount = CommissionService::calculateCommission($price, $commission);

        $variant->base_price = $price; // ✅ NEW
        $variant->commission_type = $commission['type'];
        $variant->commission_value = $commission['value'];
        $variant->commission_amount = round($commissionAmount, 2);
        $variant->final_price = round($price + $commissionAmount, 2);

        return $variant;
    });

    return $item;
}
}