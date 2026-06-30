
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.orders.show',$order->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Invoice — Order #{{ $order->id }}</h4>
        <a href="{{ route('admin.billing.pdf',$order->id) }}" target="_blank" class="btn btn-sm btn-primary ms-auto"><i class="fas fa-print me-1"></i>Print / PDF</a>
    </div>
    <div class="card shadow-sm" style="max-width:700px">
        <div class="card-body p-4">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-bold text-primary mb-1">🍰 Sweetan</h4>
                    <p class="text-muted small mb-0">Tax Invoice</p>
                </div>
                <div class="text-end">
                    <p class="mb-0 fw-bold">Invoice #{{ str_pad($order->id,6,'0',STR_PAD_LEFT) }}</p>
                    <p class="text-muted small mb-0">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                    @php $c=['pending'=>'warning','delivered'=>'success','cancelled'=>'danger']; @endphp
                    <span class="badge bg-{{ $c[$order->status]??'secondary' }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="row mb-4">
                <div class="col-6">
                    <p class="fw-bold mb-1 small text-muted text-uppercase">From (Seller)</p>
                    <p class="mb-0 fw-semibold">{{ $order->owner?->restaurant_name }}</p>
                    <p class="text-muted small mb-0">{{ $order->owner?->restaurant_address }}</p>
                    @if($order->owner?->gst_number)<p class="text-muted small mb-0">GSTIN: {{ $order->owner->gst_number }}</p>@endif
                </div>
                <div class="col-6 text-end">
                    <p class="fw-bold mb-1 small text-muted text-uppercase">Billed To</p>
                    <p class="mb-0 fw-semibold">{{ $order->user?->full_name }}</p>
                    <p class="text-muted small mb-0">{{ $order->address_line }}</p>
                    <p class="text-muted small mb-0">{{ $order->city }}, {{ $order->state }} {{ $order->pincode }}</p>
                    <p class="text-muted small mb-0">{{ $order->user?->phone_number }}</p>
                </div>
            </div>

            {{-- Items --}}
            <table class="table table-sm mb-3">
                <thead class="table-light"><tr><th>Item</th><th>Qty</th><th>Rate</th><th>GST</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ ($item->item['name'] ?? 'Item') }}<br><small class="text-muted">{{ $item->item['variant_label'] ?? '' }}</small></td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ $item->offer_price ?? $item->price }}</td>
                    <td>{{ $item->item['gst_percent'] ?? 0 }}%<br><small class="text-muted">₹{{ $item->gst_amount }}</small></td>
                    <td class="text-end">₹{{ $item->item_total }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="row justify-content-end">
                <div class="col-6">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Subtotal</td><td class="text-end">₹{{ $order->total_amount }}</td></tr>
                        <tr><td class="text-muted">GST</td><td class="text-end">₹{{ $order->gst_amount }}</td></tr>
                        <tr><td class="text-muted">Delivery Charge</td><td class="text-end">₹{{ $order->delivery_charge }}</td></tr>
                        @if($order->handling_fee > 0)<tr><td class="text-muted">Handling Fee</td><td class="text-end">₹{{ $order->handling_fee }}</td></tr>@endif
                        @if($order->packing_fee > 0)<tr><td class="text-muted">Packing Fee</td><td class="text-end">₹{{ $order->packing_fee }}</td></tr>@endif
                        @if($order->discount_amount > 0)<tr><td class="text-success">Coupon Discount<br><code class="small">{{ $order->coupon_code }}</code></td><td class="text-end text-success">-₹{{ $order->discount_amount }}</td></tr>@endif
                        @if($order->wallet_used > 0)<tr><td class="text-info">Wallet Used</td><td class="text-end text-info">-₹{{ $order->wallet_used }}</td></tr>@endif
                        <tr class="border-top"><td><strong>Total Payable</strong></td><td class="text-end"><strong>₹{{ $order->final_amount }}</strong></td></tr>
                        <tr><td class="text-muted small">Payment Method</td><td class="text-end small">{{ strtoupper($order->payment_method) }}</td></tr>
                    </table>
                </div>
            </div>

            <hr>
            <p class="text-center text-muted small mb-0">Thank you for ordering from Sweetan! For queries: support@sweetan.in</p>
        </div>
    </div>
</div>
</x-app-layout>
