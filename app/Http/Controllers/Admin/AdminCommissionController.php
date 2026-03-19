<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\AppOwnerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCommissionController extends Controller
{

    public function editCategoryCommission($id)
    {
        $category = ItemCategory::findOrFail($id);

        return view('admin.commission.category',compact('category'));
    }

   public function updateCategoryCommission(Request $request, $id)
{
    $category = ItemCategory::findOrFail($id);

    $category->update([
        'commission_percent' => $request->commission_percent,
        'commission_type' => $request->commission_type,
    ]);

    return back()->with('success','Commission updated');
}

    public function editSubcategoryCommission($id)
{
    $subcategory = ItemSubcategory::findOrFail($id);

    return view('admin.commission.sub-category',compact('subcategory'));
}
    public function editItemCommission($id)
{
    $item = Item::findOrFail($id);

    return view('admin.commission.item',compact('item'));
}

public function updateSubcategoryCommission(Request $request, $id)
{
    $subcategory = ItemSubcategory::findOrFail($id);

    $subcategory->update([
        'commission_percent' => $request->commission_percent,
        'commission_type' => $request->commission_type,
    ]);

    return back()->with('success','Subcategory commission updated');
}


public function updateItemCommission(Request $request, $id)
{
    $item = Item::findOrFail($id);

    $item->update([
        'commission_percent' => $request->commission_percent,
        'commission_type' => $request->commission_type,
    ]);

    return back()->with('success','Item commission updated');
}
}