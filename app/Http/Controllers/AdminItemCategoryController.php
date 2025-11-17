<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;

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
        ]);

        ItemCategory::create([
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'status'        => $request->status ?? 1
        ]);

        return redirect()->route('admin.item-categories.index')->with('success','Category created successfully.');
    }

    public function edit($id)
    {
        $category = ItemCategory::findOrFail($id);
        return view('item-category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        $category = ItemCategory::findOrFail($id);

        $category->update([
            'category_name' => $request->category_name,
            'description'   => $request->description,
            'status'        => $request->status ?? 1
        ]);

        return redirect()->route('item-categories')->with('success','Category updated successfully.');
    }

public function destroy($id)
{
    $category = ItemCategory::findOrFail($id);

    // Toggle enum status
    $category->status = $category->status === 'active' ? 'inactive' : 'active';
    $category->save();

    return redirect()->route('admin.item-categories.index')
        ->with('success', 'Category status updated successfully.');
}
}