<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\AppOwnerUser;

class AdminItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with(['category:id,category_name','subcategory:id,name','owner:shop_id,restaurant_name','variants'])
            ->when($request->search, fn($q) => $q->where('item_name','like','%'.$request->search.'%'))
            ->when($request->shop_id, fn($q) => $q->where('shop_id',$request->shop_id))
            ->when($request->category_id, fn($q) => $q->where('category_id',$request->category_id))
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->latest()->paginate(20)->withQueryString();

        $vendors    = AppOwnerUser::select('shop_id','restaurant_name')->orderBy('restaurant_name')->get();
        $categories = ItemCategory::select('id','category_name')->orderBy('category_name')->get();

        return view('admin.items.index', compact('items','vendors','categories'));
    }

    public function create()
    {
        $vendors    = AppOwnerUser::select('shop_id','restaurant_name')->orderBy('restaurant_name')->get();
        $categories = ItemCategory::select('id','category_name')->get();
        return view('admin.items.create', compact('vendors','categories'));
    }

    public function show(int $id)
    {
        $item = Item::with(['category','subcategory','variants','readyMedia','owner:shop_id,restaurant_name'])->findOrFail($id);
        return view('admin.items.show', compact('item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id'       => 'required|exists:app_owner_shops,shop_id',
            'category_id'   => 'required|exists:item_categories,id',
            'subcategory_id'=> 'nullable|exists:item_subcategories,id',
            'item_name'     => 'required|string|max:150',
            'description'   => 'nullable|string|max:2000',
            'is_veg'        => 'nullable',
            'is_featured'   => 'nullable',
            'status'        => 'nullable|in:active,inactive',
            'variants'      => 'required|array|min:1',
            'variants.*.label'      => 'required|string|max:100',
            'variants.*.price'      => 'required|numeric|min:0.01',
            'variants.*.offer_price'=> 'nullable|numeric|min:0',
            'variants.*.gst_percent'=> 'required|numeric|min:0|max:100',
            'variants.*.hsn_code'   => 'nullable|string|max:20',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    if ($img->isValid()) {
                        $images[] = $img->store('items','public');
                    }
                }
            }

            $firstVariant = $request->variants[0];
            $item = Item::create([
                'shop_id'        => $validated['shop_id'],
                'category_id'    => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'item_name'      => $validated['item_name'],
                'description'    => $validated['description'] ?? null,
                'price'          => $firstVariant['price'],
                'offer_price'    => $firstVariant['offer_price'] ?? null,
                'gst_percent'    => $firstVariant['gst_percent'],
                'is_veg'         => $request->boolean('is_veg', true),
                'is_featured'    => $request->boolean('is_featured'),
                'status'         => $validated['status'] ?? 'active',
                'images'         => $images,
            ]);

            foreach ($request->variants as $i => $v) {
                $price = (float) $v['price'];
                $offerPrice = !empty($v['offer_price']) ? (float) $v['offer_price'] : null;
                $gst = (float) $v['gst_percent'];
                $netPrice = $offerPrice ?? $price;
                $cgst = round($netPrice * $gst / 200, 2);
                $sgst = round($netPrice * $gst / 200, 2);

                ItemVariant::create([
                    'item_id'     => $item->id,
                    'label'       => $v['label'],
                    'price'       => $price,
                    'offer_price' => $offerPrice,
                    'gst_percent' => $gst,
                    'cgst'        => $cgst,
                    'sgst'        => $sgst,
                    'hsn_code'    => $v['hsn_code'] ?? null,
                    'is_default'  => $i === 0,
                    'status'      => 'active',
                ]);
            }

            DB::commit();
            return redirect()->route('admin.items.index')->with('success','Item "' . $item->item_name . '" created successfully with ' . count($request->variants) . ' variant(s).');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()]);
        }
    }

    public function edit(int $id)
    {
        $item       = Item::with(['variants', 'readyMedia'])->findOrFail($id);
        $vendors    = AppOwnerUser::select('shop_id','restaurant_name')->get();
        $categories = ItemCategory::select('id','category_name')->get();
        $subcats    = ItemSubcategory::where('category_id',$item->category_id)->get();
        return view('admin.items.edit', compact('item','vendors','categories','subcats'));
    }

    public function update(Request $request, int $id)
    {
        $item = Item::with('variants')->findOrFail($id);

        $validated = $request->validate([
            'shop_id'       => 'required|exists:app_owner_shops,shop_id',
            'category_id'   => 'required|exists:item_categories,id',
            'subcategory_id'=> 'nullable|exists:item_subcategories,id',
            'item_name'     => 'required|string|max:150',
            'description'   => 'nullable|string|max:2000',
            'is_veg'        => 'nullable',
            'is_featured'   => 'nullable',
            'status'        => 'nullable|in:active,inactive',
            'variants'      => 'required|array|min:1',
            'variants.*.id'         => 'nullable|integer',
            'variants.*.label'      => 'required|string|max:100',
            'variants.*.price'      => 'required|numeric|min:0.01',
            'variants.*.offer_price'=> 'nullable|numeric|min:0',
            'variants.*.gst_percent'=> 'required|numeric|min:0|max:100',
            'variants.*.hsn_code'   => 'nullable|string|max:20',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $images = $item->images ?? [];
            if ($request->hasFile('images')) {
                foreach ($images as $img) Storage::disk('public')->delete($img);
                $images = [];
                foreach ($request->file('images') as $img) {
                    if ($img->isValid()) {
                        $images[] = $img->store('items','public');
                    }
                }
            }

            $firstVariant = $request->variants[0];
            $item->update([
                'shop_id'        => $validated['shop_id'],
                'category_id'    => $validated['category_id'],
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'item_name'      => $validated['item_name'],
                'description'    => $validated['description'] ?? null,
                'price'          => $firstVariant['price'],
                'offer_price'    => $firstVariant['offer_price'] ?? null,
                'gst_percent'    => $firstVariant['gst_percent'],
                'is_veg'         => $request->boolean('is_veg', $item->is_veg),
                'is_featured'    => $request->boolean('is_featured', $item->is_featured),
                'status'         => $validated['status'] ?? $item->status,
                'images'         => $images,
            ]);

            $existIds    = $item->variants->pluck('id')->toArray();
            $incomingIds = collect($request->variants)->pluck('id')->filter()->toArray();
            ItemVariant::whereIn('id', array_diff($existIds, $incomingIds))->update(['status' => 'inactive']);

            foreach ($request->variants as $i => $v) {
                $price = (float) $v['price'];
                $offerPrice = !empty($v['offer_price']) ? (float) $v['offer_price'] : null;
                $gst = (float) $v['gst_percent'];
                $netPrice = $offerPrice ?? $price;
                $cgst = round($netPrice * $gst / 200, 2);
                $sgst = round($netPrice * $gst / 200, 2);

                ItemVariant::updateOrCreate(['id' => $v['id'] ?? null], [
                    'item_id'     => $item->id,
                    'label'       => $v['label'],
                    'price'       => $price,
                    'offer_price' => $offerPrice,
                    'gst_percent' => $gst,
                    'cgst'        => $cgst,
                    'sgst'        => $sgst,
                    'hsn_code'    => $v['hsn_code'] ?? null,
                    'is_default'  => $i === 0,
                    'status'      => 'active',
                ]);
            }

            DB::commit();
            return redirect()->route('admin.items.index')->with('success', 'Item "' . $item->item_name . '" updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update item: ' . $e->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);
        foreach ($item->images ?? [] as $img) Storage::disk('public')->delete($img);
        $item->delete();
        return back()->with('success','Item deleted.');
    }

    public function toggle(int $id)
    {
        $item = Item::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();
        return back()->with('success','Item status updated.');
    }

    public function subcategories(int $categoryId)
    {
        return response()->json(ItemSubcategory::where('category_id',$categoryId)->where('status',1)->select('id','name')->get());
    }
}
