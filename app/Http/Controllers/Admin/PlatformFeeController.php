<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlatformFee;

class PlatformFeeController extends Controller
{
    public function index()
    {
        $fees = PlatformFee::orderBy('priority')->get();
        return view('admin.platform_fee.index', compact('fees'));
    }

    public function create()
    {
        return view('admin.platform_fee.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'handling_fee' => 'required|numeric|min:0',
            'packing_fee' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|gt:min_order_amount',
            'priority' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        // 🚨 Prevent overlap
        $exists = PlatformFee::where(function ($q) use ($request) {
            $q->whereBetween('min_order_amount', [$request->min_order_amount ?? 0, $request->max_order_amount ?? 999999])
              ->orWhereBetween('max_order_amount', [$request->min_order_amount ?? 0, $request->max_order_amount ?? 999999]);
        })->exists();

        if ($exists) {
            return back()->withErrors([
                'min_order_amount' => 'Order range overlaps with existing fee'
            ])->withInput();
        }

        PlatformFee::create([
            'handling_fee' => $request->handling_fee,
            'packing_fee' => $request->packing_fee,
            'min_order_amount' => $request->min_order_amount,
            'max_order_amount' => $request->max_order_amount,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('platform-fee.index')
            ->with('success', 'Platform Fee Created');
    }

    public function edit($id)
    {
        $fee = PlatformFee::findOrFail($id);
        return view('admin.platform_fee.edit', compact('fee'));
    }

    public function update(Request $request, $id)
    {
        $fee = PlatformFee::findOrFail($id);

        $request->validate([
            'handling_fee' => 'required|numeric|min:0',
            'packing_fee' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|gt:min_order_amount',
            'priority' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        $exists = PlatformFee::where('id', '!=', $id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('min_order_amount', [$request->min_order_amount ?? 0, $request->max_order_amount ?? 999999])
                  ->orWhereBetween('max_order_amount', [$request->min_order_amount ?? 0, $request->max_order_amount ?? 999999]);
            })->exists();

        if ($exists) {
            return back()->withErrors([
                'min_order_amount' => 'Order range overlaps with existing fee'
            ])->withInput();
        }

        $fee->update([
            'handling_fee' => $request->handling_fee,
            'packing_fee' => $request->packing_fee,
            'min_order_amount' => $request->min_order_amount,
            'max_order_amount' => $request->max_order_amount,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('platform-fee.index')
            ->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        $fee = PlatformFee::find($id);

        if (!$fee) {
            return back()->with('error', 'Record not found');
        }

        $fee->delete();

        return back()->with('success', 'Deleted Successfully');
    }
}