<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'category_id', 'subcategory_id', /* etc */
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ItemSubcategory::class, 'subcategory_id');
    }
        public function owner()
    {
        return $this->belongsTo(AppOwnerUser::class, 'shop_id', 'shop_id');
    }

    public function itemsBySubcategory(Request $request)
    {
        $request->validate([
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'shop_id' => 'nullable|exists:shops,shop_id', // optional
        ]);

        $subcategoryId = $request->subcategory_id;
        $shopId = $request->shop_id;

        $items = Item::with(['category', 'subcategory', 'owner'])
            ->where('subcategory_id', $subcategoryId)
            ->when($shopId, function ($query) use ($shopId) {
                // ✅ Same shop items first
                $query->orderByRaw("shop_id = ? DESC", [$shopId]);
            })
            ->orderBy('id', 'DESC') // fallback ordering
            ->get();

        return response()->json([
            'data' => $items
        ], 200);
    }
}