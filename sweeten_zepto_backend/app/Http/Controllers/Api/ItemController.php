<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\AppHomeFilter;
use App\Services\CommissionService;

class ItemController extends Controller
{
    public function listByOwner(Request $request, int $shopId): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 15);
        $items = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'variants' => fn($q) => $q->where('status','active')->select(['id','item_id','label','price','offer_price','gst_percent','is_default','status']),
        ])
        ->where('shop_id', $shopId)
        ->select(['id','shop_id','category_id','subcategory_id','item_name','description','status','images','is_veg','is_featured','weight_or_piece','created_at'])
        ->latest()->paginate($perPage);

        $items->getCollection()->transform(fn($item) => $this->applyCommission($item));
        return response()->json(['status' => true, 'data' => $items]);
    }

    public function show(int $id): JsonResponse
    {
        $item = Item::with([
            'category:id,category_name',
            'subcategory:id,name',
            'variants' => fn($q) => $q->where('status','active')->orderByDesc('is_default'),
            'owner:shop_id,restaurant_name,city,status',
        ])->find($id);

        if (!$item) return response()->json(['status' => false, 'message' => 'Item not found'], 404);

        $item = $this->applyCommission($item);
        $defaultVariant = $item->variants->firstWhere('is_default', true) ?? $item->variants->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'id'              => $item->id,
                'shop_id'         => $item->shop_id,
                'item_name'       => $item->item_name,
                'description'     => $item->description,
                'status'          => $item->status,
                'is_veg'          => (bool)$item->is_veg,
                'is_jain'         => (bool)$item->is_jain,
                'is_featured'     => (bool)$item->is_featured,
                'spice_level'     => $item->spice_level,
                'preparation_time'=> $item->preparation_time,
                'image_urls'      => $item->image_urls,
                'category'        => ['id' => $item->category?->id, 'name' => $item->category?->category_name],
                'subcategory'     => ['id' => $item->subcategory?->id, 'name' => $item->subcategory?->name],
                'owner'           => $item->owner,
                'default_variant' => $defaultVariant,
                'variants'        => $item->variants,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $variants = json_decode($request->variants, true);
        if (!is_array($variants)) return response()->json(['status' => false, 'message' => 'Invalid variants format'], 422);

        $validator = Validator::make(array_merge($request->all(), ['variants' => $variants]), [
            'shop_id'              => 'required|exists:app_owner_shops,shop_id',
            'category_id'          => 'required|exists:item_categories,id',
            'subcategory_id'       => 'nullable|exists:item_subcategories,id',
            'item_name'            => 'required|string|max:150',
            'description'          => 'nullable|string',
            'is_veg'               => 'nullable|boolean',
            'is_jain'              => 'nullable|boolean',
            'spice_level'          => 'nullable|in:none,mild,medium,hot,very_hot',
            'preparation_time'     => 'nullable|integer|min:1',
            'is_featured'          => 'nullable|boolean',
            'status'               => 'nullable|in:active,inactive',
            'variants'             => 'required|array|min:1',
            'variants.*.label'     => 'required|string|max:100',
            'variants.*.price'     => 'required|numeric|min:0',
            'variants.*.offer_price' => 'nullable|numeric|min:0',
            'variants.*.gst_percent' => 'required|numeric|min:0',
            'variants.*.hsn_code'  => 'nullable|string|max:20',
            'variants.*.is_default'=> 'nullable|boolean',
            'images.*'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        DB::beginTransaction();
        try {
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $imagePaths[] = $img->store('items', 'public');
                }
            }
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

            $hasDefault = false;
            foreach ($variants as $v) {
                if (!empty($v['is_default'])) $hasDefault = true;
                ItemVariant::create([
                    'item_id'     => $item->id,
                    'label'       => $v['label'],
                    'price'       => $v['price'],
                    'offer_price' => $v['offer_price'] ?? null,
                    'gst_percent' => $v['gst_percent'],
                    'hsn_code'    => $v['hsn_code'] ?? null,
                    'is_default'  => $v['is_default'] ?? false,
                    'status'      => 'active',
                ]);
            }
            if (!$hasDefault) {
                ItemVariant::where('item_id', $item->id)->first()?->update(['is_default' => true]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Item created', 'data' => $item->load('variants')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = Item::with('variants')->findOrFail($id);
        $variants = $request->variants ? json_decode($request->variants, true) : null;

        $rules = [
            'category_id'    => 'sometimes|exists:item_categories,id',
            'subcategory_id' => 'nullable|exists:item_subcategories,id',
            'item_name'      => 'sometimes|string|max:150',
            'description'    => 'nullable|string',
            'is_veg'         => 'nullable|boolean',
            'is_jain'        => 'nullable|boolean',
            'spice_level'    => 'nullable|in:none,mild,medium,hot,very_hot',
            'preparation_time'=> 'nullable|integer',
            'is_featured'    => 'nullable|boolean',
            'status'         => 'nullable|in:active,inactive',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
        if ($variants !== null) {
            $rules['variants']             = 'array|min:1';
            $rules['variants.*.label']     = 'required|string|max:100';
            $rules['variants.*.price']     = 'required|numeric|min:0';
            $rules['variants.*.gst_percent'] = 'required|numeric|min:0';
        }

        $validator = Validator::make(array_merge($request->all(), $variants !== null ? ['variants' => $variants] : []), $rules);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        DB::beginTransaction();
        try {
            $existingImages = $request->existing_images ? json_decode($request->existing_images, true) : [];
            $currentImages  = $item->images ?? [];
            $finalImages    = [];
            foreach ($currentImages as $old) {
                if (in_array(asset('storage/' . $old), $existingImages)) $finalImages[] = $old;
                else Storage::disk('public')->delete($old);
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) $finalImages[] = $img->store('items', 'public');
            }

            $updateData = array_filter([
                'category_id'      => $request->category_id,
                'subcategory_id'   => $request->subcategory_id,
                'item_name'        => $request->item_name,
                'description'      => $request->description,
                'spice_level'      => $request->spice_level,
                'preparation_time' => $request->preparation_time,
                'status'           => $request->status,
                'images'           => $finalImages ?: null,
            ], fn($v) => $v !== null);
            if ($request->has('is_veg')) $updateData['is_veg'] = $request->boolean('is_veg');
            if ($request->has('is_jain')) $updateData['is_jain'] = $request->boolean('is_jain');
            if ($request->has('is_featured')) $updateData['is_featured'] = $request->boolean('is_featured');
            $item->update($updateData);

            if ($variants !== null) {
                $existingIds = $item->variants->pluck('id')->toArray();
                $incomingIds = collect($variants)->pluck('id')->filter()->toArray();
                ItemVariant::whereIn('id', array_diff($existingIds, $incomingIds))->update(['status' => 'inactive', 'is_default' => false]);
                $hasDefault = false;
                foreach ($variants as $v) {
                    if (!empty($v['is_default'])) $hasDefault = true;
                    $variantData = ['label'=>$v['label'],'price'=>$v['price'],'offer_price'=>$v['offer_price']??null,'gst_percent'=>$v['gst_percent'],'is_default'=>$v['is_default']??false,'status'=>'active'];
                    if (!empty($v['id'])) ItemVariant::where('id', $v['id'])->update($variantData);
                    else ItemVariant::create(array_merge($variantData, ['item_id' => $item->id]));
                }
                if (!$hasDefault) ItemVariant::where('item_id', $item->id)->where('status','active')->first()?->update(['is_default' => true]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Item updated', 'data' => $item->fresh()->load('variants')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        Item::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Item deleted']);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();
        return response()->json(['status' => true, 'status' => $item->status]);
    }

    public function itemsBySubcategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'shop_id'        => 'nullable|exists:app_owner_shops,shop_id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $items = Item::with(['category:id,category_name,commission_percent,commission_type','subcategory:id,name,commission_percent,commission_type','owner:shop_id,restaurant_name','variants'=>fn($q)=>$q->where('status','active'),'defaultVariant:id,item_id,label,price,offer_price,gst_percent,is_default'])
            ->where('subcategory_id', $request->subcategory_id)->where('status', 'active')
            ->when($request->shop_id, fn($q) => $q->orderByRaw('shop_id = ? DESC', [$request->shop_id]))
            ->orderByDesc('id')->get();

        $items->transform(fn($i) => $this->applyCommission($i));
        return response()->json(['status' => true, 'data' => $items]);
    }

    public function similarItems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['item_id' => 'required|exists:items,id']);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        $item = Item::findOrFail($request->item_id);
        $items = Item::with(['variants','owner:shop_id,restaurant_name'])->where('subcategory_id', $item->subcategory_id)->where('id', '!=', $item->id)->where('status','active')->limit(8)->get();
        $items->transform(fn($i) => $this->applyCommission($i));
        return response()->json(['status' => true, 'data' => $items]);
    }

    public function getAppHomeFilter(): JsonResponse
    {
        return response()->json(['status' => true, 'data' => AppHomeFilter::all()]);
    }

    private function applyCommission(Item $item): Item
    {
        $item->variants->transform(function ($v) use ($item) {
            $price = $v->offer_price ?? $v->price;
            $commission = CommissionService::getCommissionDetails($item, $price);
            $commissionAmount = CommissionService::calculateCommission($price, $commission);
            $v->commission_type   = $commission['type'];
            $v->commission_value  = $commission['value'];
            $v->commission_amount = round($commissionAmount, 2);
            $v->final_price       = round((float)$price + $commissionAmount, 2);
            return $v;
        });
        return $item;
    }
}
