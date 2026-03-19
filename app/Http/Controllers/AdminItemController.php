<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\AppOwnerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminItemController extends Controller
{
    // ===============================
    // LIST
    // ===============================
    public function index(Request $request)
    {
        $owners = AppOwnerUser::orderBy('restaurant_name')->get();

        $items = Item::with(['category','subcategory','owner','variants'])
            ->when($request->shop_id, fn($q) => $q->where('shop_id',$request->shop_id))
            ->latest()
            ->paginate(10);

        return view('items.index', compact('items','owners'));
    }

    // ===============================
    // CREATE
    // ===============================
    public function create()
    {
        return view('items.create', [
            'categories' => ItemCategory::where('status',1)->get(),
            'owners'     => AppOwnerUser::all()
        ]);
    }

    // ===============================
    // AJAX SUBCATEGORIES
    // ===============================
    public function getSubcategories($categoryId)
    {
        return ItemSubcategory::where('category_id',$categoryId)
            ->where('status',1)
            ->get();
    }

    // ===============================
    // STORE ITEM + VARIANTS
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'owner_id'       => 'required',
            'category_id'    => 'required',
            'subcategory_id' => 'required',
            'item_name'      => 'required',
            'variants'       => 'required|array|min:1',
            'variants.*.label' => 'required',
            'variants.*.price' => 'required|numeric',
            'variants.*.gst_percent' => 'required|numeric',
            'images.*' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();

        try {

            // ---------- Images ----------
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $name = Str::uuid().'.webp';
                    $images[] = $img->storeAs('items',$name,'public');
                }
            }

            // ---------- Item ----------
            $item = Item::create([
                'shop_id'        => $request->owner_id,
                'category_id'    => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'item_name'      => $request->item_name,
                'description'    => $request->description,
                'price'          => 0, // comes from variants
                'status'         => $request->status ?? 'active',
                'images'         => $images,
            ]);

            // ---------- Variants ----------
            foreach ($request->variants as $i => $v) {
                ItemVariant::create([
                    'item_id'     => $item->id,
                    'label'       => $v['label'],
                    'price'       => $v['price'],
                    'offer_price' => $v['offer_price'] ?? null,
                    'gst_percent' => $v['gst_percent'],
                    'hsn_code'    => $v['hsn_code'] ?? null,
                    'is_default'  => $i === 0, // first default
                    'status'      => 'active',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.items.index')
                ->with('success','Item created with variants');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    // ===============================
    // EDIT
    // ===============================
    public function edit($id)
    {
        $item = Item::with('variants')->findOrFail($id);

        return view('items.edit', [
            'item'          => $item,
            'categories'    => ItemCategory::where('status',1)->get(),
            'owners'        => AppOwnerUser::all(),
            'subcategories' => ItemSubcategory::where('category_id',$item->category_id)->get()
        ]);
    }

    // ===============================
    // UPDATE ITEM + VARIANTS
    // ===============================
    public function update(Request $request, $id)
    {
        $item = Item::with('variants')->findOrFail($id);

        DB::beginTransaction();

        try {

            // ---------- Images ----------
            $images = $item->images ?? [];

            if ($request->hasFile('images')) {
                foreach ($images as $img) {
                    Storage::disk('public')->delete($img);
                }
                $images = [];
                foreach ($request->file('images') as $img) {
                    $name = Str::uuid().'.webp';
                    $images[] = $img->storeAs('items',$name,'public');
                }
            }

            // ---------- Update Item ----------
           $item->update([
    'shop_id'        => $request->owner_id,
    'category_id'    => $request->category_id,
    'subcategory_id' => $request->subcategory_id,

    'item_name'      => $request->item_name,
    'description'    => $request->description,
    'status'         => $request->status ?? 'active',

    // 🔥 Pricing
    'price'          => $request->price,
    'offer_price'    => $request->offer_price,
    'gst_percent'    => $request->gst_percent,
    'hsn_code'       => $request->hsn_code,

    // 🔥 Inventory
    'sku'            => $request->sku,
    'stock_quantity' => $request->stock_quantity,
    'track_inventory'=> $request->track_inventory ?? 0,

    // 🔥 Extra
    'weight_or_piece'=> $request->weight_or_piece,

    'images'         => $images
]);

            // ---------- Variants Sync ----------
            $existingIds = $item->variants->pluck('id')->toArray();
            $incomingIds = collect($request->variants)->pluck('id')->filter()->toArray();

            ItemVariant::whereIn('id', array_diff($existingIds,$incomingIds))
                ->update(['status'=>'inactive','is_default'=>0]);

            foreach ($request->variants as $i => $v) {
                ItemVariant::updateOrCreate(
                    ['id'=>$v['id'] ?? null],
                    [
                        'item_id'     => $item->id,
                        'label'       => $v['label'],
                        'price'       => $v['price'],
                        'offer_price' => $v['offer_price'] ?? null,
                        'gst_percent' => $v['gst_percent'],
                        'hsn_code'    => $v['hsn_code'] ?? null,
                        'is_default'  => $i === 0,
                        'status'      => 'active'
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.items.index')
                ->with('success','Item updated');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    // ===============================
    // DELETE
    // ===============================
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        foreach ($item->images ?? [] as $img) {
            Storage::disk('public')->delete($img);
        }

        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('success','Item deleted');
    }
}