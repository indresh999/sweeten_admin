<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryCharge;

class DeliveryChargeController extends Controller
{
    public function index()
    {
        $charges = DeliveryCharge::orderBy('priority')->get();
        return view('admin.delivery_charge.index', compact('charges'));
    }

    public function create()
    {
        return view('admin.delivery_charge.create');
    }

    public function store(Request $request)
    {
        // ✅ Strong validation
        $request->validate([
            'min_distance' => 'required|numeric|min:0',
            'max_distance' => 'required|numeric|gt:min_distance',
            'charge_amount' => 'required|numeric|min:0',
            'free_above_amount' => 'nullable|numeric|min:0',
            'priority' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        // 🚨 Prevent overlapping slabs
        $exists = DeliveryCharge::where(function ($q) use ($request) {
            $q->whereBetween('min_distance', [$request->min_distance, $request->max_distance])
              ->orWhereBetween('max_distance', [$request->min_distance, $request->max_distance]);
        })->exists();

        if ($exists) {
            return back()->withErrors([
                'min_distance' => 'Distance range overlaps with existing slab'
            ])->withInput();
        }

        // ✅ Safe create (avoid mass assignment junk)
        DeliveryCharge::create([
            'min_distance' => $request->min_distance,
            'max_distance' => $request->max_distance,
            'charge_amount' => $request->charge_amount,
            'free_above_amount' => $request->free_above_amount,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('delivery-charge.index')
            ->with('success', 'Delivery Charge Created');
    }

    public function edit($id)
    {
        $charge = DeliveryCharge::findOrFail($id);
        return view('admin.delivery_charge.edit', compact('charge'));
    }

    public function update(Request $request, $id)
    {
        $charge = DeliveryCharge::findOrFail($id);

        // ✅ Validation
        $request->validate([
            'min_distance' => 'required|numeric|min:0',
            'max_distance' => 'required|numeric|gt:min_distance',
            'charge_amount' => 'required|numeric|min:0',
            'free_above_amount' => 'nullable|numeric|min:0',
            'priority' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        // 🚨 Prevent overlap (exclude current record)
        $exists = DeliveryCharge::where('id', '!=', $id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('min_distance', [$request->min_distance, $request->max_distance])
                  ->orWhereBetween('max_distance', [$request->min_distance, $request->max_distance]);
            })->exists();

        if ($exists) {
            return back()->withErrors([
                'min_distance' => 'Distance range overlaps with existing slab'
            ])->withInput();
        }

        // ✅ Safe update
        $charge->update([
            'min_distance' => $request->min_distance,
            'max_distance' => $request->max_distance,
            'charge_amount' => $request->charge_amount,
            'free_above_amount' => $request->free_above_amount,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('delivery-charge.index')
            ->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        $charge = DeliveryCharge::find($id);

        if (!$charge) {
            return back()->with('error', 'Record not found');
        }

        $charge->delete();

        return back()->with('success', 'Deleted Successfully');
    }
}