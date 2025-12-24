<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminItemCategoryController extends Controller
{
  public function index(Request $request)
{
    $query = ItemCategory::orderBy('id', 'desc');

    // ✅ FILTER BY CATEGORY TYPE
    if ($request->filled('category_type')) {
        if ($request->category_type === 'birthday') {
            $query->where('category_type', 'birthday');
        } elseif ($request->category_type === 'normal') {
            $query->whereNull('category_type');
        }
    }

    $categories = $query->paginate(10)->withQueryString();

    return view('item-category.item-categories', compact('categories'));
}

    public function create()
    {
        return view('item-category.create');
    }

   public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('categories', $filename, 'public');

            // ✅ RELATIVE PATH ONLY
            $imagePath = 'storage/' . $path;
        }

      ItemCategory::create([
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'status'        => $request->status ?? 1,
            'image'         => $imagePath,
            'category_type' => $request->has('is_birthday') ? 'birthday' : null,
            'is_featured'   => $request->has('is_featured') ? 1 : 0, // ✅ FEATURED
        ]);

        return redirect()->route('admin.item-categories.index')
            ->with('success', 'Category created successfully.');
    }
    public function edit($id)
    {
        $category = ItemCategory::findOrFail($id);
        return view('item-category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $category = ItemCategory::findOrFail($id);

        $imagePath = $category->getRawOriginal('image'); // 👈 IMPORTANT

        if ($request->hasFile('image')) {

            // ✅ DELETE OLD IMAGE
            if ($imagePath) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $imagePath)
                );
            }

            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';
            $path = $file->storeAs('categories', $filename, 'public');

            // ✅ SAVE RELATIVE PATH
            $imagePath = 'storage/' . $path;
        }

                $category->update([
                'category_name' => $request->category_name,
                'description'   => $request->description,
                'status'        => $request->status ?? 1,
                'image'         => $imagePath ?? $category->image, // keep old image if not changed
                'category_type' => $request->has('is_birthday') ? 'birthday' : null,
                'is_featured'   => $request->has('is_featured') ? 1 : 0, // ✅ IMPORTANT
            ]);

        return redirect()->route('admin.item-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);

        $imagePath = $category->getRawOriginal('image');

        if ($imagePath) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $imagePath)
            );
        }

        $category->delete();

        return redirect()->route('admin.item-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}