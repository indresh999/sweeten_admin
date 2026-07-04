<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use App\Models\CancelReason;
use App\Services\NotificationService;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user:id,full_name,phone_number','owner:shop_id,restaurant_name'])
            ->when($request->search, fn($q) => $q->where('id','like','%'.$request->search.'%'))
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->when($request->shop_id, fn($q) => $q->where('shop_id',$request->shop_id))
            ->when($request->from,    fn($q) => $q->whereDate('created_at','>=',$request->from))
            ->when($request->to,      fn($q) => $q->whereDate('created_at','<=',$request->to))
            ->when($request->payment, fn($q) => $q->where('payment_method',$request->payment))
            ->latest()->paginate(20)->withQueryString();

        $summary = [
            'total'            => Order::when($request->from,fn($q)=>$q->whereDate('created_at','>=',$request->from))->count(),
            'pending'          => Order::where('status','pending')->count(),
            'out_for_delivery' => Order::where('status','out_for_delivery')->count(),
            'delivered'        => Order::where('status','delivered')->count(),
            'cancelled'        => Order::where('status','cancelled')->count(),
            'revenue'          => (float) Order::where('status','delivered')
                ->when($request->from,fn($q)=>$q->whereDate('created_at','>=',$request->from))->sum('final_amount'),
        ];

        $cancelReasons = CancelReason::select('id','reason')->get();
        $deliveryBoys  = DeliveryBoy::where('status','online')->where('is_verified',1)->get();

        return view('admin.orders.index', compact('orders','summary','cancelReasons','deliveryBoys'));
    }

    public function show(int $id)
    {
        $order = Order::with([
            'user:id,full_name,phone_number,email',
            'owner:shop_id,restaurant_name,phone_number,restaurant_address',
            'items','timeline',
            'assignment.boy:id,full_name,phone_number,vehicle_type',
            'cancelReason',
        ])->findOrFail($id);

        $deliveryBoys = DeliveryBoy::where('status','online')->where('is_verified',1)->select('id','full_name','phone_number','vehicle_type')->get();
        return view('admin.orders.show', compact('order','deliveryBoys'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status'=>'required|in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled']);
        $order = Order::findOrFail($id);
        $order->update(['status'=>$request->status]);
        DeliveryTimeline::create([
            'order_id'  => $id,
            'status'    => $request->status,
            'message'   => 'Status updated to '.$request->status.' by admin',
            'created_at'=> now(),
        ]);
        NotificationService::send($order->user_id, 'Order Update', 'Your order #'.$id.' status: '.ucfirst(str_replace('_',' ',$request->status)), 'order', 'order', $id);
        return back()->with('success','Order status updated.');
    }

    public function assignDelivery(Request $request, int $id)
    {
        $request->validate(['delivery_boy_id'=>'required|exists:delivery_boys,id']);
        $order = Order::findOrFail($id);
        DeliveryAssignment::updateOrCreate(
            ['order_id'=>$id],
            ['delivery_boy_id'=>$request->delivery_boy_id,'status'=>'assigned','expected_delivery'=>now()->addMinutes(45)]
        );
        DeliveryBoy::where('id',$request->delivery_boy_id)->increment('current_active_orders');
        DeliveryTimeline::create(['order_id'=>$id,'status'=>'assigned','message'=>'Manually assigned by admin','created_at'=>now()]);
        return back()->with('success','Delivery boy assigned.');
    }

    public function autoAssign(int $id)
    {
        $order = Order::findOrFail($id);
        $boy = DeliveryBoy::selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS dist', [$order->lat,$order->lng,$order->lat])
            ->where('status','online')->where('is_verified',1)
            ->whereColumn('current_active_orders','<','max_active_orders')
            ->having('dist','<=',15)->orderBy('dist')->first();
        if (!$boy) return back()->with('error','No delivery boy available nearby.');
        DeliveryAssignment::updateOrCreate(['order_id'=>$id],['delivery_boy_id'=>$boy->id,'status'=>'assigned','expected_delivery'=>now()->addMinutes(45)]);
        $boy->increment('current_active_orders');
        DeliveryTimeline::create(['order_id'=>$id,'status'=>'assigned','message'=>'Auto-assigned to '.$boy->full_name,'created_at'=>now()]);
        return back()->with('success','Auto-assigned to '.$boy->full_name.'.');
    }

    public function cancel(Request $request, int $id)
    {
        $request->validate(['reason_id'=>'required|exists:cancel_reasons,id','remark'=>'nullable|string|max:500']);
        $order = Order::findOrFail($id);
        $order->update(['status'=>'cancelled','cancel_reason_id'=>$request->reason_id,'cancel_remark'=>$request->remark]);
        if ($order->wallet_used > 0) {
            optional(\App\Models\AppUser::find($order->user_id))->getOrCreateWallet()?->credit($order->wallet_used,'Refund for Order #'.$id,'order_refund',$id);
        }
        NotificationService::orderCancelled($order->user_id,$id);
        return back()->with('success','Order cancelled.');
    }

    public function timeline(int $id)
    {
        $timeline = DeliveryTimeline::where('order_id',$id)->orderBy('created_at')->get();
        return response()->json($timeline);
    }

    public function export(Request $request)
    {
        $orders = Order::with(['user:id,full_name','owner:shop_id,restaurant_name'])
            ->when($request->from, fn($q) => $q->whereDate('created_at','>=',$request->from))
            ->when($request->to,   fn($q) => $q->whereDate('created_at','<=',$request->to))
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->latest()->get();

        $csv  = "Order ID,Customer,Vendor,Amount,Status,Payment,Date\n";
        foreach ($orders as $o) {
            $csv .= implode(',', [
                $o->id, '"'.($o->user?->full_name ?? '').'"', '"'.($o->owner?->restaurant_name ?? '').'"',
                $o->final_amount, $o->status, $o->payment_method, $o->created_at->format('Y-m-d H:i'),
            ])."\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment;filename="orders_export.csv"']);
    }
}
