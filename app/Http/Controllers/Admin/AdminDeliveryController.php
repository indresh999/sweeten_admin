<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\DeliveryBoy;
use App\Models\DeliveryDocument;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryEarning;

class AdminDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $boys = DeliveryBoy::withCount(['assignments'])
            ->when($request->search, fn($q) => $q->where('full_name','like','%'.$request->search.'%')
                ->orWhere('phone_number','like','%'.$request->search.'%'))
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->when($request->verified === '1', fn($q) => $q->where('is_verified',1))
            ->when($request->verified === '0', fn($q) => $q->where('is_verified',0))
            ->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'    => DeliveryBoy::count(),
            'online'   => DeliveryBoy::where('status','online')->count(),
            'verified' => DeliveryBoy::where('is_verified',1)->count(),
            'pending_docs' => DeliveryDocument::where('status','pending')->count(),
        ];

        return view('admin.delivery.index', compact('boys','stats'));
    }

    public function show(int $id)
    {
        $boy  = DeliveryBoy::with('documents')->findOrFail($id);
        $assignments = DeliveryAssignment::with('order.owner:shop_id,restaurant_name')->where('delivery_boy_id',$id)->latest()->take(10)->get();
        $earningStats = [
            'today'  => (float) DeliveryEarning::where('delivery_boy_id',$id)->whereDate('created_at',today())->sum('net_earning'),
            'week'   => (float) DeliveryEarning::where('delivery_boy_id',$id)->whereBetween('created_at',[now()->startOfWeek(),now()])->sum('net_earning'),
            'month'  => (float) DeliveryEarning::where('delivery_boy_id',$id)->whereMonth('created_at',now()->month)->sum('net_earning'),
            'total'  => (float) DeliveryEarning::where('delivery_boy_id',$id)->sum('net_earning'),
            'pending'=> (float) DeliveryEarning::where('delivery_boy_id',$id)->where('is_paid',false)->sum('net_earning'),
        ];
        return view('admin.delivery.show', compact('boy','assignments','earningStats'));
    }

    public function toggle(int $id)
    {
        $boy = DeliveryBoy::findOrFail($id);
        $boy->status = $boy->status === 'blocked' ? 'offline' : 'blocked';
        $boy->save();
        return back()->with('success','Delivery boy status updated.');
    }

    public function verify(Request $request, int $id)
    {
        DeliveryBoy::findOrFail($id)->update(['is_verified'=>1]);
        return back()->with('success','Delivery boy verified.');
    }

    public function sendEmail(Request $request, int $id)
    {
        $request->validate(['subject'=>'required|max:200','message'=>'required|max:2000']);
        $boy = DeliveryBoy::findOrFail($id);
        // Delivery boys use phone, not always email — send if email present
        if ($boy->email ?? null) {
            Mail::raw($request->message, fn($m) => $m->to($boy->email)->subject($request->subject));
        }
        return back()->with('success','Email sent.');
    }

    public function orders(int $id)
    {
        $boy  = DeliveryBoy::findOrFail($id);
        $assignments = DeliveryAssignment::with(['order.user:id,full_name','order.owner:shop_id,restaurant_name'])
            ->where('delivery_boy_id',$id)->latest()->paginate(15);
        return view('admin.delivery.orders', compact('boy','assignments'));
    }

    public function earnings(int $id)
    {
        $boy      = DeliveryBoy::findOrFail($id);
        $earnings = DeliveryEarning::with('order:id,final_amount,created_at')->where('delivery_boy_id',$id)->latest()->paginate(15);
        $summary  = [
            'total'   => (float) DeliveryEarning::where('delivery_boy_id',$id)->sum('net_earning'),
            'pending' => (float) DeliveryEarning::where('delivery_boy_id',$id)->where('is_paid',false)->sum('net_earning'),
            'paid'    => (float) DeliveryEarning::where('delivery_boy_id',$id)->where('is_paid',true)->sum('net_earning'),
        ];
        return view('admin.delivery.earnings', compact('boy','earnings','summary'));
    }

    public function pendingDocs()
    {
        $docs = DeliveryDocument::with('deliveryBoy')->where('status','pending')->latest()->paginate(20);
        return view('admin.delivery.pending-docs', compact('docs'));
    }

    public function approveDoc(Request $request, int $id)
    {
        $doc = DeliveryDocument::with('deliveryBoy')->findOrFail($id);
        $doc->update(['status'=>'approved','remarks'=>$request->remarks]);
        // Check if all docs are approved → auto-verify boy
        $allApproved = DeliveryDocument::where('delivery_boy_id',$doc->delivery_boy_id)->where('status','!=','approved')->doesntExist();
        if ($allApproved) $doc->deliveryBoy->update(['is_verified'=>1]);
        return back()->with('success','Document approved.');
    }

    public function rejectDoc(Request $request, int $id)
    {
        $request->validate(['remarks'=>'required|string|max:300']);
        DeliveryDocument::findOrFail($id)->update(['status'=>'rejected','remarks'=>$request->remarks]);
        return back()->with('success','Document rejected.');
    }

    public function markPaid(Request $request)
    {
        $request->validate(['delivery_boy_id'=>'required|exists:delivery_boys,id']);
        DeliveryEarning::where('delivery_boy_id',$request->delivery_boy_id)->where('is_paid',false)->update(['is_paid'=>true,'paid_at'=>now()]);
        return back()->with('success','All pending earnings marked as paid.');
    }
}
