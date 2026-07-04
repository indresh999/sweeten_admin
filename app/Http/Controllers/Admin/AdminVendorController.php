<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\AppOwnerUser;
use App\Models\Order;
use App\Models\DeliveryEarning;

class AdminVendorController extends Controller
{
    public function index(Request $request)
    {
        $vendors = AppOwnerUser::with('images')
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('restaurant_name','like','%'.$request->search.'%')
                  ->orWhere('email','like','%'.$request->search.'%')
                  ->orWhere('city','like','%'.$request->search.'%');
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('shop_id')
            ->paginate(15)->withQueryString();

        return view('admin.vendors.index', compact('vendors'));
    }

    public function pending()
    {
        $vendors = AppOwnerUser::where('status', 'pending')->with('images')->latest('shop_id')->paginate(15);
        return view('admin.vendors.pending', compact('vendors'));
    }

    public function show(int $id)
    {
        $vendor = AppOwnerUser::with(['images','schedules','items','orders'])->findOrFail($id);
        $stats = [
            'total_orders'   => Order::where('shop_id', $id)->count(),
            'total_revenue'  => (float) Order::where('shop_id', $id)->where('status','delivered')->sum('final_amount'),
            'today_orders'   => Order::where('shop_id', $id)->whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('shop_id', $id)->where('status','pending')->count(),
            'total_items'    => $vendor->items->count(),
        ];
        $recentOrders = Order::with(['user:id,full_name', 'items'])->where('shop_id', $id)->latest()->take(10)->get();
        return view('admin.vendors.show', compact('vendor','stats','recentOrders'));
    }

    public function update(Request $request, int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $vendor->update($request->only(['restaurant_name','email','phone_number','restaurant_address','city','state','gst_number','fssai_number']));
        return back()->with('success','Vendor updated successfully.');
    }

    public function toggle(int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $vendor->status = $vendor->status === 'active' ? 'inactive' : 'active';
        $vendor->save();
        return back()->with('success','Vendor status updated.');
    }

    public function approve(int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $vendor->update(['status' => 'active']);
        try {
            Mail::raw("Congratulations! Your store '{$vendor->restaurant_name}' has been approved on Sweetan. You can now start receiving orders.", function($m) use ($vendor) {
                $m->to($vendor->email)->subject('Your Sweetan Store is Approved! 🎉');
            });
        } catch (\Exception $e) {}
        return back()->with('success','Vendor approved and notified.');
    }

    public function reject(Request $request, int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $reason = $request->get('reason', 'Your application did not meet our requirements.');
        $vendor->update(['status' => 'rejected']);
        try {
            Mail::raw("Dear {$vendor->full_name}, unfortunately your store application has been rejected. Reason: {$reason}. Please contact support for assistance.", function($m) use ($vendor) {
                $m->to($vendor->email)->subject('Sweetan Store Application Status');
            });
        } catch (\Exception $e) {}
        return back()->with('success','Vendor rejected.');
    }

    public function sendEmail(Request $request, int $id)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);
        $vendor = AppOwnerUser::findOrFail($id);
        Mail::raw($request->message, function($m) use ($vendor, $request) {
            $m->to($vendor->email)->subject($request->subject);
        });
        return back()->with('success','Email sent to vendor.');
    }

    public function orders(int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $orders = Order::with('user:id,full_name')->where('shop_id',$id)->latest()->paginate(15);
        return view('admin.vendors.orders', compact('vendor','orders'));
    }

    public function earnings(int $id)
    {
        $vendor = AppOwnerUser::findOrFail($id);
        $orders = Order::where('shop_id',$id)->where('status','delivered')
            ->select('id','final_amount','discount_amount','gst_amount','delivery_charge','created_at')
            ->latest()->paginate(15);
        $total = Order::where('shop_id',$id)->where('status','delivered')->sum('final_amount');
        return view('admin.vendors.earnings', compact('vendor','orders','total'));
    }
}
