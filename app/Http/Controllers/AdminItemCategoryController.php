<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::orderBy('id','desc')->paginate(10);
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
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

    
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('categories', $filename, 'public');

            // FULL URL stored in DB
            $imagePath = asset('storage/' . $path);
        }

    
        ItemCategory::create([
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'status'        => $request->status ?? 1,
            'image'         => $imagePath,
        ]);

        return redirect()
            ->route('admin.item-categories.index')
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

        $imagePath = $category->image;

        if ($request->hasFile('image')) {

            // DELETE OLD IMAGE
            if ($category->image) {
                $oldPath = str_replace(asset('storage') . '/', '', $category->image);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';
            $path = $file->storeAs('categories', $filename, 'public');

            $imagePath = asset('storage/' . $path);
        }

        $category->update([
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'status'        => $request->status ?? 1,
            'image'         => $imagePath,
        ]);

        return redirect()
            ->route('admin.item-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);

        if ($category->image) {
            $path = str_replace(asset('storage') . '/', '', $category->image);
            Storage::disk('public')->delete($path);
        }

        $category->delete();

        return redirect()
            ->route('admin.item-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}