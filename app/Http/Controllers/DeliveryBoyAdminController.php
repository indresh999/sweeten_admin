<?php

namespace App\Http\Controllers;

use App\Models\DeliveryDocument;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;

class DeliveryBoyAdminController extends Controller
{
    // LIST OF PENDING DOCUMENTS
    public function pendingDocuments()
    {
        $documents = DeliveryDocument::with('deliveryBoy')
            ->where('status', 'pending')
            ->get();

        return view('deliveryboys.pending-documents', compact('documents'));
    }

    // SHOW DOCUMENT DETAILS
    public function documentShow($id)
    {
        $document = DeliveryDocument::with('deliveryBoy')->findOrFail($id);
        return view('deliveryboys.document-show', compact('document'));
    }

    // APPROVE / REJECT DOCUMENT
    public function verifyDocument(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $doc = DeliveryDocument::findOrFail($id);
        $doc->update($request->only('status', 'remarks'));

        if ($request->status == 'approved') {
            $doc->deliveryBoy->update(['is_verified' => true]);
        }

        return redirect()->route('admin.delivery.pending')
            ->with('success', 'Document updated successfully.');
    }

    // DELIVERY BOYS LIST
    public function list()
    {
        $boys = DeliveryBoy::with('documents')->get();
        return view('deliveryboys.boys-list', compact('boys'));
    }

    // BLOCK / UNBLOCK
    public function toggleActive($id)
    {
        $boy = DeliveryBoy::findOrFail($id);
        $boy->status = $boy->status === 'blocked' ? 'online' : 'blocked';
        $boy->save();

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    // FULL PROFILE PAGE
    public function profile($id)
    {
        $boy = DeliveryBoy::with(['documents', 'assignments'])->findOrFail($id);
        return view('deliveryboys.profile', compact('boy'));
    }

    // ALL ORDERS FOR A DELIVERY BOY (WITH FILTERS)
    public function orders(Request $request, $id)
    {
        $boy = DeliveryBoy::findOrFail($id);

        $status = $request->get('status');

        $query = DeliveryAssignment::with(['order.user', 'order.owner'])
            ->where('delivery_boy_id', $id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $assignments = $query->paginate(10);

        return view('deliveryboys.orders', compact('assignments', 'boy', 'status'));
    }

    // ORDER DETAILS
    public function orderDetails($id)
    {
        $assignment = DeliveryAssignment::with([
            'order.items.item',
            'order.user',
            'order.owner',
            'boy'
        ])->where('order_id', $id)->firstOrFail();

        return view('deliveryboys.order-details', compact('assignment'));
    }

    public function earnings($id)
    {
        $boy = DeliveryBoy::findOrFail($id);

        // Completed orders only
        $assignments = DeliveryAssignment::with('order')
            ->where('delivery_boy_id', $id)
            ->where('status', 'completed')
            ->get();

        $totalOrders = $assignments->count();
        $totalEarnings = $assignments->sum(function ($as) {
            return $as->order->delivery_charge ?? 0;
        });

        $monthEarnings = $assignments->whereBetween('delivered_at', [
            now()->startOfMonth(), now()->endOfMonth()
        ])->sum(function ($as) {
            return $as->order->delivery_charge ?? 0;
        });

        return view('deliveryboys.earnings', compact(
            'boy', 'assignments', 'totalOrders', 'totalEarnings', 'monthEarnings'
        ));
    }

    public function timeline($orderId)
    {
        $assignment = DeliveryAssignment::with([
            'order.user', 'order.owner', 'boy'
        ])->where('order_id', $orderId)->firstOrFail();

        return view('deliveryboys.timeline', compact('assignment'));
    }
}