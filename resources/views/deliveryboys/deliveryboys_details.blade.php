<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">

        <!-- HEADER -->
        <h3 class="mb-4">Order Details - #{{ $order->id }}</h3>

        <div class="row">

            <!-- ORDER SUMMARY -->
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong>Order Summary</strong>
                    </div>
                    <div class="card-body">

                        <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                        </p>
                        <p><strong>Total Amount:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                        <p><strong>GST %:</strong> {{ $order->gst_percent }}%</p>
                        <p><strong>GST Amount:</strong> ₹{{ number_format($order->tax_amount, 2) }}</p>
                        <p><strong>Delivery Charge:</strong> ₹{{ number_format($order->delivery_charge, 2) }}</p>
                        <p><strong>Handling Fee:</strong> ₹{{ number_format($order->handling_fee, 2) }}</p>
                        <p><strong>Packing Fee:</strong> ₹{{ number_format($order->packing_fee, 2) }}</p>

                        <p class="mt-3">
                            <strong>Final Payable:</strong>
                            <span class="text-success fw-bold">
                                ₹{{ number_format($order->final_amount, 2) }}
                            </span>
                        </p>

                    </div>
                </div>
            </div>

            <!-- USER DETAILS -->
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <strong>User Details</strong>
                    </div>
                    <div class="card-body">

                        <p><strong>User Name:</strong> {{ $order->user->full_name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $order->user->phone_number ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>

                        <hr>

                        <p><strong>Delivery Address:</strong></p>
                        <p>
                            {{ $order->address_label }}<br>
                            {{ $order->address_line }}<br>
                            {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
                        </p>

                        <p><strong>Coordinates:</strong>
                            {{ $order->lat }}, {{ $order->lng }}
                        </p>

                    </div>
                </div>
            </div>

        </div>




        <!-- SHOP DETAILS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <strong>Shop / Bakery Details</strong>
            </div>
            <div class="card-body">

                <p><strong>Shop Name:</strong> {{ $order->owner->restaurant_name ?? 'N/A' }}</p>
                <p><strong>Shop Owner:</strong> {{ $order->owner->full_name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $order->owner->phone_number ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $order->owner->email ?? 'N/A' }}</p>

                <p><strong>Address:</strong><br>
                    {{ $order->owner->restaurant_address ?? 'N/A' }}<br>
                    {{ $order->owner->city ?? '' }}, {{ $order->owner->state ?? '' }}
                </p>

            </div>
        </div>



        <!-- ORDER ITEMS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <strong>Ordered Items</strong>
            </div>

            <div class="card-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->item->name ?? 'Item Deleted' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->price, 2) }}</td>
                                <td>₹{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>




        <!-- IF CANCELLED -->
        @if ($order->status == 'cancelled')
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <strong>Cancellation Info</strong>
                </div>
                <div class="card-body">
                    <p><strong>Reason:</strong> {{ $order->cancelReason->reason ?? 'N/A' }}</p>
                    <p><strong>Remark:</strong> {{ $order->cancel_remark ?? 'None' }}</p>
                </div>
            </div>
        @endif


        <!-- BACK BUTTON -->
        <a href="{{ route('orders') }}" class="btn btn-secondary mt-3">← Back to Orders</a>

    </div>

</x-app-layout>