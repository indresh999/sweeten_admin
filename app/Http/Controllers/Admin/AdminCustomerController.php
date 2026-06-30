<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\AppUser;
use App\Models\Order;
use App\Models\Wallet;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = AppUser::withCount('orders')
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('full_name','like','%'.$request->search.'%')
                  ->orWhere('email','like','%'.$request->search.'%')
                  ->orWhere('phone_number','like','%'.$request->search.'%');
            }))
            ->when($request->status === 'blocked', fn($q) => $q->where('is_blocked',1))
            ->when($request->status === 'active',  fn($q) => $q->where('is_blocked',0))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(int $id)
    {
        $customer = AppUser::with(['addresses','wallet'])->findOrFail($id);
        $orders   = Order::with('owner:shop_id,restaurant_name')->where('user_id',$id)->latest()->take(10)->get();
        $stats = [
            'total_orders'    => Order::where('user_id',$id)->count(),
            'total_spent'     => (float) Order::where('user_id',$id)->where('status','delivered')->sum('final_amount'),
            'cancelled_orders'=> Order::where('user_id',$id)->where('status','cancelled')->count(),
            'wallet_balance'  => $customer->wallet?->balance ?? 0,
        ];
        return view('admin.customers.show', compact('customer','orders','stats'));
    }

    public function toggle(int $id)
    {
        $customer = AppUser::findOrFail($id);
        $customer->update(['is_blocked' => !$customer->is_blocked]);
        return back()->with('success', $customer->is_blocked ? 'Customer blocked.' : 'Customer unblocked.');
    }

    public function sendEmail(Request $request, int $id)
    {
        $request->validate(['subject'=>'required|max:200','message'=>'required|max:2000']);
        $customer = AppUser::findOrFail($id);
        Mail::raw($request->message, function($m) use ($customer,$request) {
            $m->to($customer->email)->subject($request->subject);
        });
        return back()->with('success','Email sent.');
    }

    public function orders(int $id)
    {
        $customer = AppUser::findOrFail($id);
        $orders   = Order::with('owner:shop_id,restaurant_name')->where('user_id',$id)->latest()->paginate(15);
        return view('admin.customers.orders', compact('customer','orders'));
    }

    public function walletCredit(Request $request, int $id)
    {
        $request->validate(['amount'=>'required|numeric|min:1|max:10000','note'=>'nullable|string|max:200']);
        $user   = AppUser::findOrFail($id);
        $wallet = $user->getOrCreateWallet();
        $wallet->credit((float)$request->amount, $request->note ?? 'Admin credit', 'admin');
        return back()->with('success','₹'.$request->amount.' credited to wallet.');
    }
}
