<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">

        <h3 class="mb-4">Order Details - #{{ $order->id }}</h3>

        {{-- ================= ORDER + USER INFO ================= --}}
        <div class="row">

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong>Order Summary</strong>
                    </div>
                    <div class="card-body">

                        <p><strong>Status:</strong>
                            <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                        </p>

                        <p>Total Amount: ₹{{ number_format($order->total_amount, 2) }}</p>
                        <p>GST ({{ $order->gst_percent }}%): ₹{{ number_format($order->tax_amount, 2) }}</p>
                        <p>Delivery: ₹{{ number_format($order->delivery_charge, 2) }}</p>
                        <p>Handling: ₹{{ number_format($order->handling_fee, 2) }}</p>
                        <p>Packing: ₹{{ number_format($order->packing_fee, 2) }}</p>

                        <hr>

                        <h5 class="text-success">
                            Final Payable: ₹{{ number_format($order->final_amount, 2) }}
                        </h5>

                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <strong>User Details</strong>
                    </div>
                    <div class="card-body">

                        <p>Name: {{ $order->user->full_name ?? 'N/A' }}</p>
                        <p>Phone: {{ $order->user->phone_number ?? 'N/A' }}</p>
                        <p>Email: {{ $order->user->email ?? 'N/A' }}</p>

                        <hr>

                        <p><strong>Delivery Address</strong></p>
                        <p>
                            {{ $order->address_label }}<br>
                            {{ $order->address_line }}<br>
                            {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
                        </p>

                    </div>
                </div>
            </div>

        </div>

        {{-- ================= SHOP INFO ================= --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning">
                <strong>Shop Details</strong>
            </div>
            <div class="card-body">

                <p>{{ $order->owner->restaurant_name ?? 'N/A' }}</p>
                <p>{{ $order->owner->phone_number ?? '' }}</p>
                <p>{{ $order->owner->restaurant_address ?? '' }}</p>

            </div>
        </div>

        {{-- ================= ORDER ITEMS ================= --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <strong>Ordered Items</strong>
            </div>

            <div class="card-body">
                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Variant</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>GST</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $oi)
                            <tr>
                                <td>{{ $oi->item->item_name ?? 'Deleted Item' }}</td>

                                <td>
                                    @if ($oi->variant)
                                        {{ $oi->variant->label }}
                                        <br>
                                        <small class="text-muted">
                                            HSN: {{ $oi->variant->hsn_code ?? '-' }}
                                        </small>
                                    @else
                                        Standard
                                    @endif
                                </td>

                                <td>{{ $oi->quantity }}</td>

                                <td>
                                    ₹{{ number_format($oi->offer_price ?? $oi->price, 2) }}
                                </td>

                                <td>
                                    {{ $oi->variant->gst_percent ?? '-' }}%
                                </td>

                                <td>
                                    ₹{{ number_format($oi->item_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        @include('orders.partials.tracking')
        
        {{-- ================= CANCEL INFO ================= --}}
        @if ($order->status === 'cancelled')
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <strong>Cancellation</strong>
                </div>
                <div class="card-body">
                    <p>Reason: {{ $order->cancelReason->reason ?? 'N/A' }}</p>
                    <p>Remark: {{ $order->cancel_remark ?? '-' }}</p>
                </div>
            </div>
        @endif

        <a href="{{ route('orders') }}" class="btn btn-secondary">
            ← Back to Orders
        </a>

    </div>
</x-app-layout>
