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

class ItemController extends Controller
{
    // ======================================================
    // LIST ITEMS BY OWNER
    // ======================================================
    public function listByOwner($shop_id)
    {
        return response()->json([
            'data' => Item::with(['variants','category','subcategory','owner'])
                ->where('shop_id', $shop_id)
                ->get()
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
            return response()->json(['message'=>'Item not found'],404);
        }

        $variants = $request->variants
            ? json_decode($request->variants, true)
            : null;

        DB::beginTransaction();

        try {

            // ------------------ IMAGE REPLACE ------------------
            if ($request->hasFile('images')) {

                // delete old images
                foreach ($item->images ?? [] as $old) {
                    Storage::disk('public')->delete($old);
                }

                $paths = [];
                foreach ($request->file('images') as $image) {
                    $paths[] = $image->store('items', 'public');
                }

                $item->images = $paths;
            }

            // ------------------ UPDATE ITEM ------------------
            $item->update($request->only([
                'shop_id',
                'category_id',
                'subcategory_id',
                'item_name',
                'description',
                'status'
            ]));

            // ------------------ VARIANT SYNC ------------------
            if (is_array($variants)) {

                $existingIds = $item->variants->pluck('id')->toArray();
                $incomingIds = collect($variants)->pluck('id')->filter()->toArray();

                // soft delete removed variants
                ItemVariant::whereIn('id', array_diff($existingIds, $incomingIds))
                    ->update(['status'=>'inactive','is_default'=>false]);

                foreach ($variants as $variant) {
                    ItemVariant::updateOrCreate(
                        ['id' => $variant['id'] ?? null],
                        [
                            'item_id'     => $item->id,
                            'label'       => $variant['label'],
                            'price'       => $variant['price'],
                            'offer_price' => $variant['offer_price'] ?? null,
                            'gst_percent' => $variant['gst_percent'],
                            'hsn_code'    => $variant['hsn_code'] ?? null,
                            'is_default'  => $variant['is_default'] ?? false,
                            'status'      => 'active',
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Item updated successfully',
                'data'    => $item->fresh()->load('variants')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],500);
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
                'variants',
                'category',
                'subcategory',
                'owner'
            ])
            ->where('subcategory_id', $subcategoryId)
            ->where('status', 'active')
            ->when($shopId, function ($q) use ($shopId) {
                // Same shop items first
                $q->orderByRaw('shop_id = ? DESC', [$shopId]);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'data' => $items
        ], 200);
    }

    // ======================================================
    // SIMILAR ITEMS API
    // ======================================================
    // public function similarItems(Request $request, $itemId)
    // {
    //     $limit  = $request->get('limit', 10);
    //     $shopId = $request->get('shop_id');

    //     $item = Item::with(['category','subcategory'])->find($itemId);

    //     if (!$item) {
    //         return response()->json([
    //             'message' => 'Item not found'
    //         ], 404);
    //     }

    //     $query = Item::with([
    //             'variants' => function ($q) {
    //                 $q->where('status', 'active');
    //             },
    //             'category',
    //             'subcategory',
    //             'owner'
    //         ])
    //         ->where('status', 'active')
    //         ->where('id', '!=', $item->id)

    //         // SAME SUBCATEGORY FIRST
    //         ->where(function ($q) use ($item) {
    //             $q->where('subcategory_id', $item->subcategory_id)
    //             ->orWhere('category_id', $item->category_id);
    //         });

    //     // SAME SHOP FIRST (OPTIONAL)
    //     if ($shopId) {
    //         $query->orderByRaw('shop_id = ? DESC', [$shopId]);
    //     }

    //     $items = $query
    //         ->orderByRaw('subcategory_id = ? DESC', [$item->subcategory_id])
    //         ->orderBy('id', 'DESC')
    //         ->limit($limit)
    //         ->get();

    //     return response()->json([
    //         'data' => $items
    //     ], 200);
    // }

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

    return response()->json([
        'data' => $items
    ]);
}
}