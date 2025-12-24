<?php

namespace App\Http\Controllers;

use App\Models\ItemSubcategory;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminItemSubcategoryController extends Controller
{
   public function index(Request $request)
{
    $categories = \App\Models\ItemCategory::where('status', 1)->get();

    $subcategories = \App\Models\ItemSubcategory::with('category')
        ->when($request->category_id, function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
        })
        ->latest()
        ->paginate(10)
        ->withQueryString(); // keeps filter on pagination

    return view('item-subcategory.index', compact('subcategories', 'categories'));
}

    public function create()
    {
        $categories = ItemCategory::where('status', 1)->get();
        return view('item-subcategory.create', compact('categories'));
    }

  public function store(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:item_categories,id',
        'name'        => 'required|string|max:255',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $imagePath = null;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = Str::uuid() . '.webp';
        $path = $file->storeAs('subcategories', $filename, 'public');

        // ✅ RELATIVE PATH ONLY (SAME AS CATEGORY)
        $imagePath = 'storage/' . $path;
    }

    ItemSubcategory::create([
        'category_id' => $request->category_id,
        'name'        => $request->name,
        'description' => $request->description,
        'status'      => $request->status ?? 1,
        'image'       => $imagePath,
    ]);

    return redirect()
        ->route('admin.item-subcategories.index')
        ->with('success', 'Subcategory created successfully.');
}

    public function edit($id)
    {
        $subcategory = ItemSubcategory::findOrFail($id);
        $categories  = ItemCategory::where('status', 1)->get();

        return view('item-subcategory.edit', compact('subcategory', 'categories'));
    }

   public function update(Request $request, $id)
{
    $request->validate([
        'category_id' => 'required|exists:item_categories,id',
        'name'        => 'required|string|max:255',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $subcategory = ItemSubcategory::findOrFail($id);

    // ✅ RAW DB VALUE
    $imagePath = $subcategory->getRawOriginal('image');

    if ($request->hasFile('image')) {

        // ✅ DELETE OLD IMAGE
        if ($imagePath) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $imagePath)
            );
        }

        $file = $request->file('image');
        $filename = Str::uuid() . '.webp';
        $path = $file->storeAs('subcategories', $filename, 'public');

        // ✅ SAVE RELATIVE PATH
        $imagePath = 'storage/' . $path;
    }

    $subcategory->update([
        'category_id' => $request->category_id,
        'name'        => $request->name,
        'description' => $request->description,
        'status'      => $request->status ?? 1,
        'image'       => $imagePath ?? $subcategory->image,
    ]);

    return redirect()
        ->route('admin.item-subcategories.index')
        ->with('success', 'Subcategory updated successfully.');
}

   public function destroy($id)
{
    $subcategory = ItemSubcategory::findOrFail($id);

    $imagePath = $subcategory->getRawOriginal('image');

    if ($imagePath) {
        Storage::disk('public')->delete(
            str_replace('storage/', '', $imagePath)
        );
    }

    $subcategory->delete();

    return redirect()
        ->route('admin.item-subcategories.index')
        ->with('success', 'Subcategory deleted successfully.');
}
}