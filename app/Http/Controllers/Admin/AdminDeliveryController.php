<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\DeliveryBoy;
use App\Models\DeliveryDocument;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryEarning;
use App\Models\DeliveryCashSubmission;
use App\Mail\DeliveryDocumentApprovedMail;
use App\Mail\DeliveryDocumentRejectedMail;
use App\Mail\DeliveryProfileActivatedMail;

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

    public function activate(int $id)
    {
        $boy = DeliveryBoy::findOrFail($id);
        $boy->update(['status' => 'offline', 'is_verified' => 1]);

        if ($boy->email) {
            Mail::to($boy->email)->send(new DeliveryProfileActivatedMail($boy->full_name));
        }

        return back()->with('success','Delivery boy activated. He can now go online.');
    }

    public function deactivate(int $id)
    {
        $boy = DeliveryBoy::findOrFail($id);
        $boy->update(['status' => 'blocked']);
        return back()->with('success','Delivery boy deactivated.');
    }

    public function verify(Request $request, int $id)
    {
        $boy = DeliveryBoy::findOrFail($id);
        $boy->update([
            'is_verified' => 1,
            'status'      => 'offline',
        ]);

        if ($boy->email) {
            Mail::to($boy->email)->send(new DeliveryProfileActivatedMail($boy->full_name));
        }

        return back()->with('success','Delivery boy verified and activated. He can now go online.');
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

        $boy = $doc->deliveryBoy;
        if ($boy->email) {
            $docLabels = [
                'aadhar' => 'Aadhar Card', 'pan' => 'PAN Card',
                'driving_license' => 'Driving License', 'vehicle_rc_front' => 'Vehicle RC (Front)',
                'vehicle_rc_back' => 'Vehicle RC (Back)', 'bank_passbook' => 'Bank Passbook',
                'selfie' => 'Selfie',
            ];
            $label = $docLabels[$doc->doc_type] ?? ucfirst(str_replace('_', ' ', $doc->doc_type));
            Mail::to($boy->email)->send(new DeliveryDocumentApprovedMail($boy->full_name, $label));
        }

        return back()->with('success','Document approved.');
    }

    public function rejectDoc(Request $request, int $id)
    {
        $request->validate(['remarks'=>'required|string|max:300']);
        $doc = DeliveryDocument::with('deliveryBoy')->findOrFail($id);
        $doc->update(['status'=>'rejected','remarks'=>$request->remarks]);

        $boy = $doc->deliveryBoy;
        if ($boy->email) {
            $docLabels = [
                'aadhar' => 'Aadhar Card', 'pan' => 'PAN Card',
                'driving_license' => 'Driving License', 'vehicle_rc_front' => 'Vehicle RC (Front)',
                'vehicle_rc_back' => 'Vehicle RC (Back)', 'bank_passbook' => 'Bank Passbook',
                'selfie' => 'Selfie',
            ];
            $label = $docLabels[$doc->doc_type] ?? ucfirst(str_replace('_', ' ', $doc->doc_type));
            Mail::to($boy->email)->send(new DeliveryDocumentRejectedMail($boy->full_name, $label, $request->remarks));
        }

        return back()->with('success','Document rejected.');
    }

    public function markPaid(Request $request)
    {
        $request->validate(['delivery_boy_id'=>'required|exists:delivery_boys,id']);
        DeliveryEarning::where('delivery_boy_id',$request->delivery_boy_id)->where('is_paid',false)->update(['is_paid'=>true,'paid_at'=>now()]);
        return back()->with('success','All pending earnings marked as paid.');
    }

    // ── Wallet Management ──────────────────────────────────────────────
    public function walletIndex(Request $request)
    {
        $boys = DeliveryBoy::select('id','full_name','email','phone_number','status','wallet_limit','wallet_collected','has_pending_submission')
            ->when($request->search, fn($q) => $q->where('full_name','like','%'.$request->search.'%')
                ->orWhere('email','like','%'.$request->search.'%'))
            ->orderByDesc('wallet_collected')
            ->paginate(20)->withQueryString();

        $stats = [
            'total_boys'        => DeliveryBoy::count(),
            'boys_with_limit'   => DeliveryBoy::where('wallet_limit', '>', 0)->count(),
            'pending_submissions'=> DeliveryCashSubmission::where('status','pending')->count(),
            'total_collected'   => (float) DeliveryBoy::sum('wallet_collected'),
        ];

        return view('deliveryboys.wallet-index', compact('boys','stats'));
    }

    public function walletSubmissions(Request $request)
    {
        $query = DeliveryCashSubmission::with('boy:id,full_name,email,phone_number');

        if ($request->boy_id) {
            $query->where('delivery_boy_id', $request->boy_id);
        }
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'pending'  => DeliveryCashSubmission::where('status','pending')->count(),
            'approved' => DeliveryCashSubmission::where('status','approved')->count(),
            'rejected' => DeliveryCashSubmission::where('status','rejected')->count(),
        ];

        return view('deliveryboys.wallet-submissions', compact('submissions','stats'));
    }

    public function walletVerify(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        $submission = DeliveryCashSubmission::findOrFail($id);
        $boy = DeliveryBoy::findOrFail($submission->delivery_boy_id);

        $submission->update([
            'status'      => $request->action,
            'admin_notes' => $request->notes,
            'verified_at' => now(),
        ]);

        // Clear pending flag if no other pending submissions
        $hasOtherPending = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('status', 'pending')
            ->where('id', '!=', $id)
            ->exists();
        $boy->update(['has_pending_submission' => $hasOtherPending]);

        // Send email notification
        try {
            if ($boy->email) {
                if ($request->action === 'approved') {
                    Mail::to($boy->email)->raw("Your payment of ₹{$submission->amount} submitted on {$submission->submission_date} has been APPROVED.", function($m) use ($boy) {
                        $m->subject('Payment Approved - Sweetan');
                    });
                } else {
                    Mail::to($boy->email)->raw("Your payment of ₹{$submission->amount} has been REJECTED. Reason: {$request->notes}", function($m) use ($boy) {
                        $m->subject('Payment Rejected - Sweetan');
                    });
                }
            }
        } catch (\Exception $e) {}

        return back()->with('success', "Submission {$request->action} successfully.");
    }

    public function walletSetLimit(Request $request, int $boyId)
    {
        $request->validate(['wallet_limit' => 'required|numeric|min:0']);

        $boy = DeliveryBoy::findOrFail($boyId);
        $boy->update(['wallet_limit' => $request->wallet_limit]);

        return back()->with('success', "Wallet limit set to ₹{$request->wallet_limit} for {$boy->full_name}.");
    }
}
