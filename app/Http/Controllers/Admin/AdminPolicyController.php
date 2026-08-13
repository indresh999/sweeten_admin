<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::orderBy('sort_order')->orderByDesc('id')->paginate(20);
        return view('admin.policies.index', compact('policies'));
    }

    public function create()
    {
        return view('admin.policies.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:policies,slug',
            'content'     => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        Policy::create([
            'title'      => $request->title,
            'slug'       => $request->slug ?: Str::slug($request->title),
            'content'    => $request->content,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.policies.index')->with('success', 'Policy created successfully.');
    }

    public function edit(int $id)
    {
        $policy = Policy::findOrFail($id);
        return view('admin.policies.form', compact('policy'));
    }

    public function update(Request $request, int $id)
    {
        $policy = Policy::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:policies,slug,' . $id,
            'content'     => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $policy->update([
            'title'      => $request->title,
            'slug'       => $request->slug ?: Str::slug($request->title),
            'content'    => $request->content,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.policies.index')->with('success', 'Policy updated successfully.');
    }

    public function destroy(int $id)
    {
        Policy::findOrFail($id)->delete();
        return back()->with('success', 'Policy deleted.');
    }
}
