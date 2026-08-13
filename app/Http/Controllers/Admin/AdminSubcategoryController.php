<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemSubcategory::with('category','parent')->withCount('items');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name','like','%'.$request->search.'%')
                  ->orWhere('hsn_code','like','%'.$request->search.'%');
            });
        }
        if ($request->filled('category_id')) $query->where('category_id',$request->category_id);
        if ($request->filled('status'))      $query->where('status',$request->status);

        $subcategories = $query->orderBy('sort_order')->orderBy('id','desc')->paginate(20)->withQueryString();
        $categories    = ItemCategory::where('status',1)->orderBy('category_name')->get();

        $stats = [
            'total'    => ItemSubcategory::count(),
            'active'   => ItemSubcategory::where('status',1)->count(),
            'inactive' => ItemSubcategory::where('status',0)->count(),
        ];

        return view('admin.subcategories.index', compact('subcategories','categories','stats'));
    }

  public function create()
{
    $sub = null;

    $categories = ItemCategory::where('status', 1)
        ->orderBy('category_name')
        ->get();

    $taxSlabs = $this->gstSlabs();

    $subcategories = ItemSubcategory::whereNull('parent_id')
        ->where('status', 1)
        ->get();

    return view('admin.subcategories.create', compact(
        'sub',
        'categories',
        'taxSlabs',
        'subcategories'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:item_categories,id',
            'parent_id'       => 'nullable|exists:item_subcategories,id',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'status'          => 'required|in:0,1',
            'sort_order'      => 'nullable|integer|min:0',
            'is_featured'     => 'nullable|boolean',
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
        ]);

        $gst = (float)($validated['gst_percent'] ?? 0);
        if (!isset($validated['cgst_percent']) && $gst > 0) {
            $validated['cgst_percent'] = round($gst/2,2);
            $validated['sgst_percent'] = round($gst/2,2);
            $validated['igst_percent'] = $gst;
        }

        if (!$gst && $request->filled('parent_id')) {
            $parent = ItemSubcategory::with('category')->find($request->parent_id);
            $src = $parent ?? $parent?->category;
            if ($src) {
                $validated['cgst_percent'] = $validated['cgst_percent'] ?? $src->cgst_percent;
                $validated['sgst_percent'] = $validated['sgst_percent'] ?? $src->sgst_percent;
                $validated['igst_percent'] = $validated['igst_percent'] ?? $src->igst_percent;
                $validated['cess_percent'] = $validated['cess_percent'] ?? $src->cess_percent;
                $validated['hsn_code']     = $validated['hsn_code']     ?? $src->hsn_code;
            }
        }

        $validated['slug']              = Str::slug($validated['name']);
        $validated['is_featured']       = $request->boolean('is_featured');
        $validated['is_tax_inclusive']  = $request->boolean('is_tax_inclusive');
        $validated['gst_percent']       = (float)($validated['gst_percent']        ?? 0);
        $validated['cgst_percent']      = (float)($validated['cgst_percent']       ?? 0);
        $validated['sgst_percent']      = (float)($validated['sgst_percent']       ?? 0);
        $validated['igst_percent']      = (float)($validated['igst_percent']       ?? 0);
        $validated['cess_percent']      = (float)($validated['cess_percent']       ?? 0);
        $validated['commission_percent']= (float)($validated['commission_percent'] ?? 0);
        $validated['sort_order']        = (int)($validated['sort_order']           ?? 0);

        if ($request->hasFile('image')) {
            $validated['image'] = 'storage/'.$request->file('image')->store('subcategories','public');
        }

        ItemSubcategory::create($validated);

        return redirect()->route('admin.subcategories.index')->with('success','Subcategory created successfully.');
    }

    public function show(int $id)
    {
        $sub   = ItemSubcategory::with(['category','parent','children','items'])->findOrFail($id);
        $stats = ['items' => Item::where('subcategory_id',$id)->count(), 'active' => Item::where('subcategory_id',$id)->where('status','active')->count()];
        return view('admin.subcategories.show', compact('sub','stats'));
    }

    public function edit(int $id)
    {
        $sub           = ItemSubcategory::findOrFail($id);
        $categories    = ItemCategory::where('status',1)->orderBy('category_name')->get();
        $taxSlabs      = $this->gstSlabs();
        $subcategories = ItemSubcategory::whereNull('parent_id')->where('id','!=',$id)->where('status',1)->get();
        return view('admin.subcategories.edit', compact('sub','categories','taxSlabs','subcategories'));
    }

    public function update(Request $request, int $id)
    {
        $sub = ItemSubcategory::findOrFail($id);

        $validated = $request->validate([
            'category_id'     => 'required|exists:item_categories,id',
            'parent_id'       => 'nullable|exists:item_subcategories,id',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'status'          => 'required|in:0,1',
            'sort_order'      => 'nullable|integer|min:0',
            'is_featured'     => 'nullable|boolean',
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
        ]);

        $gst = (float)($validated['gst_percent'] ?? 0);
        if (!isset($validated['cgst_percent']) && $gst > 0) {
            $validated['cgst_percent'] = round($gst/2,2);
            $validated['sgst_percent'] = round($gst/2,2);
            $validated['igst_percent'] = $gst;
        }

        $validated['is_featured']       = $request->boolean('is_featured');
        $validated['is_tax_inclusive']  = $request->boolean('is_tax_inclusive');
        $validated['gst_percent']       = (float)($validated['gst_percent']        ?? $sub->gst_percent        ?? 0);
        $validated['cgst_percent']      = (float)($validated['cgst_percent']       ?? $sub->cgst_percent       ?? 0);
        $validated['sgst_percent']      = (float)($validated['sgst_percent']       ?? $sub->sgst_percent       ?? 0);
        $validated['igst_percent']      = (float)($validated['igst_percent']       ?? $sub->igst_percent       ?? 0);
        $validated['cess_percent']      = (float)($validated['cess_percent']       ?? $sub->cess_percent       ?? 0);
        $validated['commission_percent']= (float)($validated['commission_percent'] ?? $sub->commission_percent ?? 0);
        $validated['sort_order']        = (int)($validated['sort_order']           ?? $sub->sort_order         ?? 0);

        if ($request->hasFile('image')) {
            if ($sub->image) Storage::disk('public')->delete(str_replace('storage/','',$sub->image));
            $validated['image'] = 'storage/'.$request->file('image')->store('subcategories','public');
        }

        $sub->update($validated);

        return redirect()->route('admin.subcategories.index')->with('success','Subcategory updated successfully.');
    }

    public function destroy(int $id)
    {
        $sub = ItemSubcategory::findOrFail($id);
        if ($sub->image) Storage::disk('public')->delete(str_replace('storage/','',$sub->image));
        $sub->delete();
        return back()->with('success','Subcategory deleted.');
    }

    public function toggle(int $id)
    {
        $sub = ItemSubcategory::findOrFail($id);
        $sub->update(['status' => $sub->status ? 0 : 1]);
        return back()->with('success','Status updated.');
    }

    public function ajaxSearch(Request $request)
    {
        $query = ItemSubcategory::where('status',1);
        if ($request->filled('category_id')) $query->where('category_id',$request->category_id);
        if ($request->filled('q')) $query->where('name','like','%'.$request->q.'%');
        $subs = $query->with('category')->select('id','name','category_id')->limit(40)->get()->map(fn($s)=>['id'=>$s->id,'text'=>$s->name.' ('.$s->category?->category_name.')']);
        return response()->json(['results'=>$subs]);
    }

    private function gstSlabs(): array
    {
        return [
            ['label'=>'Exempt (0%)',  'gst'=>0,   'cgst'=>0,  'sgst'=>0,  'igst'=>0],
            ['label'=>'GST 5%',       'gst'=>5,   'cgst'=>2.5,'sgst'=>2.5,'igst'=>5],
            ['label'=>'GST 12%',      'gst'=>12,  'cgst'=>6,  'sgst'=>6,  'igst'=>12],
            ['label'=>'GST 18%',      'gst'=>18,  'cgst'=>9,  'sgst'=>9,  'igst'=>18],
            ['label'=>'GST 28%',      'gst'=>28,  'cgst'=>14, 'sgst'=>14, 'igst'=>28],
        ];
    }
}
