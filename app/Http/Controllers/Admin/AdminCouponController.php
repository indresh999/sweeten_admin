<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponUsage;

class AdminCouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::withCount('usages')
            ->when($request->search, fn($q) => $q->where('code','like','%'.$request->search.'%'))
            ->when($request->type, fn($q) => $q->where('discount_type',$request->type))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'             => 'required|string|max:30|unique:coupons,code',
            'title'            => 'required|string|max:150',
            'description'      => 'nullable|string|max:500',
            'discount_type'    => 'required|in:percent,flat',
            'discount_value'   => 'required|numeric|min:1',
            'max_discount_amount'=> 'nullable|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'usage_per_user'   => 'required|integer|min:1',
            'valid_from'       => 'required|date',
            'valid_until'      => 'required|date|after_or_equal:valid_from',
            'is_active'        => 'nullable|boolean',
        ]);

        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        Coupon::create($data);
        return redirect()->route('admin.coupons.index')->with('success','Coupon created.');
    }

    public function edit(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $request->validate([
            'title'            => 'required|string|max:150',
            'discount_type'    => 'required|in:percent,flat',
            'discount_value'   => 'required|numeric|min:1',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_per_user'   => 'required|integer|min:1',
            'valid_from'       => 'required|date',
            'valid_until'      => 'required|date|after_or_equal:valid_from',
        ]);
        $coupon->update($request->except(['code','_token','_method']));
        return redirect()->route('admin.coupons.index')->with('success','Coupon updated.');
    }

    public function destroy(int $id)
    {
        Coupon::findOrFail($id)->delete();
        return back()->with('success','Coupon deleted.');
    }

    public function toggle(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success','Coupon status updated.');
    }

    public function usage(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $usages = CouponUsage::with(['user:id,full_name,email','order:id,final_amount,created_at'])
            ->where('coupon_id',$id)->latest()->paginate(20);
        return view('admin.coupons.usage', compact('coupon','usages'));
    }
}
