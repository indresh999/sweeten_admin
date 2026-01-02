<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\DeliveryAssignment;

class AdminDeliveryController extends Controller
{
    public function assignDeliveryBoy(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
        ]);

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $request->order_id],
            [
                'delivery_boy_id' => $request->delivery_boy_id,
                'status' => 'assigned',
                'expected_delivery' => now()->addMinutes(30),
            ]
        );

        addOrderTimeline(
            $request->order_id,
            'delivery_assigned',
            'Delivery partner assigned',
            [
                'delivery_boy_id' => $request->delivery_boy_id
            ]
        );

        return back()->with('success', 'Delivery boy assigned');
    }
}