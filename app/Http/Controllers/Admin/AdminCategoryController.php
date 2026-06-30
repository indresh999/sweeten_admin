<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemCategory::withCount(['items','allSubcategories']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('category_name','like','%'.$request->search.'%')
                  ->orWhere('hsn_code','like','%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status',$request->status);
        }
        if ($request->filled('type')) {
            if ($request->type === 'birthday') {
                $query->where('category_type','birthday');
            } elseif ($request->type === 'normal') {
                $query->where(function($q){ $q->whereNull('category_type')->orWhere('category_type',''); });
            }
        }

        $categories = $query->orderBy('sort_order')->orderBy('id','desc')->paginate(20)->withQueryString();

        $stats = [
            'total'    => ItemCategory::count(),
            'active'   => ItemCategory::where('status',1)->count(),
            'inactive' => ItemCategory::where('status',0)->count(),
            'featured' => ItemCategory::where('is_featured',1)->count(),
        ];

        return view('admin.categories.index', compact('categories','stats'));
    }

    public function create()
    {
        $taxSlabs = $this->gstSlabs();
        return view('admin.categories.create', compact('taxSlabs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name'   => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'status'          => 'required|in:0,1',
            'sort_order'      => 'nullable|integer|min:0',
            'is_featured'     => 'nullable|boolean',
            'category_type'   => 'nullable|string|max:50',
            'hsn_code'        => 'nullable|string|max:20',
            'sac_code'        => 'nullable|string|max:20',
            'gst_percent'     => 'nullable|numeric|min:0|max:100',
            'cgst_percent'    => 'nullable|numeric|min:0|max:50',
            'sgst_percent'    => 'nullable|numeric|min:0|max:50',
            'igst_percent'    => 'nullable|numeric|min:0|max:100',
            'cess_percent'    => 'nullable|numeric|min:0|max:100',
            'is_tax_inclusive'=> 'nullable|boolean',
            'commission_percent'=> 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'meta_title'      => 'nullable|string|max:255',
            'meta_description'=> 'nullable|string|max:500',
        ]);

        $gst = (float)($validated['gst_percent'] ?? 0);
        if (!isset($validated['cgst_percent']) && $gst > 0) {
            $validated['cgst_percent'] = round($gst / 2, 2);
            $validated['sgst_percent'] = round($gst / 2, 2);
            $validated['igst_percent'] = $gst;
        }

        $validated['slug']       = Str::slug($validated['category_name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_tax_inclusive'] = $request->boolean('is_tax_inclusive');

        if ($request->hasFile('image')) {
            $validated['image'] = 'storage/'.$request->file('image')->store('categories','public');
        }

        ItemCategory::create($validated);

        return redirect()->route('admin.categories.index')->with('success','Category created successfully.');
    }

    public function show(int $id)
    {
        $category = ItemCategory::with(['allSubcategories.children','items'])->findOrFail($id);
        $stats = [
            'subcategories' => ItemSubcategory::where('category_id',$id)->count(),
            'items'         => Item::where('category_id',$id)->count(),
            'active_items'  => Item::where('category_id',$id)->where('status','active')->count(),
        ];
        return view('admin.categories.show', compact('category','stats'));
    }

    public function edit(int $id)
    {
        $category = ItemCategory::findOrFail($id);
        $taxSlabs = $this->gstSlabs();
        return view('admin.categories.edit', compact('category','taxSlabs'));
    }

    public function update(Request $request, int $id)
    {
        $category = ItemCategory::findOrFail($id);

        $validated = $request->validate([
            'category_name'   => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'status'          => 'required|in:0,1',
            'sort_order'      => 'nullable|integer|min:0',
            'is_featured'     => 'nullable|boolean',
            'category_type'   => 'nullable|string|max:50',
            'hsn_code'        => 'nullable|string|max:20',
            'sac_code'        => 'nullable|string|max:20',
            'gst_percent'     => 'nullable|numeric|min:0|max:100',
            'cgst_percent'    => 'nullable|numeric|min:0|max:50',
            'sgst_percent'    => 'nullable|numeric|min:0|max:50',
            'igst_percent'    => 'nullable|numeric|min:0|max:100',
            'cess_percent'    => 'nullable|numeric|min:0|max:100',
            'is_tax_inclusive'=> 'nullable|boolean',
            'commission_percent'=> 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|in:percentage,fixed',
            'meta_title'      => 'nullable|string|max:255',
            'meta_description'=> 'nullable|string|max:500',
        ]);

        $gst = (float)($validated['gst_percent'] ?? $category->gst_percent ?? 0);
        if (empty($validated['cgst_percent']) && $gst > 0) {
            $validated['cgst_percent'] = round($gst / 2, 2);
            $validated['sgst_percent'] = round($gst / 2, 2);
            $validated['igst_percent'] = $gst;
        }

        $validated['is_featured']      = $request->boolean('is_featured');
        $validated['is_tax_inclusive'] = $request->boolean('is_tax_inclusive');

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete(str_replace('storage/','',$category->image));
            $validated['image'] = 'storage/'.$request->file('image')->store('categories','public');
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success','Category updated successfully.');
    }

    public function destroy(int $id)
    {
        $category = ItemCategory::findOrFail($id);
        if ($category->image) Storage::disk('public')->delete(str_replace('storage/','',$category->image));
        $category->delete();
        return back()->with('success','Category deleted.');
    }

    public function toggle(int $id)
    {
        $category = ItemCategory::findOrFail($id);
        $category->update(['status' => $category->status ? 0 : 1]);
        return back()->with('success','Category status updated.');
    }

    public function ajaxSearch(Request $request)
    {
        $categories = ItemCategory::where('status',1)
            ->when($request->q, fn($query) => $query->where('category_name','like','%'.$request->q.'%'))
            ->select('id','category_name as text','image')
            ->limit(30)->get();
        return response()->json(['results' => $categories]);
    }

    public function getSubcategories(int $id)
    {
        $subs = ItemSubcategory::where('category_id',$id)->where('status',1)
            ->select('id','name as text')->orderBy('sort_order')->get();
        return response()->json($subs);
    }

    public function bulkAction(Request $request)
    {
        $request->validate(['action'=>'required|in:activate,deactivate,delete','ids'=>'required|array']);
        $ids = $request->ids;
        switch ($request->action) {
            case 'activate':   ItemCategory::whereIn('id',$ids)->update(['status'=>1]); break;
            case 'deactivate': ItemCategory::whereIn('id',$ids)->update(['status'=>0]); break;
            case 'delete':
                ItemCategory::whereIn('id',$ids)->each(function($c){
                    if ($c->image) Storage::disk('public')->delete(str_replace('storage/','',$c->image));
                    $c->delete();
                });
                break;
        }
        return back()->with('success','Bulk action completed.');
    }

    private function gstSlabs(): array
    {
        return [
            ['label'=>'Exempt (0%)',  'gst'=>0,   'cgst'=>0,    'sgst'=>0,    'igst'=>0],
            ['label'=>'GST 5%',       'gst'=>5,   'cgst'=>2.5,  'sgst'=>2.5,  'igst'=>5],
            ['label'=>'GST 12%',      'gst'=>12,  'cgst'=>6,    'sgst'=>6,    'igst'=>12],
            ['label'=>'GST 18%',      'gst'=>18,  'cgst'=>9,    'sgst'=>9,    'igst'=>18],
            ['label'=>'GST 28%',      'gst'=>28,  'cgst'=>14,   'sgst'=>14,   'igst'=>28],
            ['label'=>'GST 28%+Cess', 'gst'=>28,  'cgst'=>14,   'sgst'=>14,   'igst'=>28],
        ];
    }
}
