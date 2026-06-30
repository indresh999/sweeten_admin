
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Order #{{ $order->id }}</h4>
        @php $c=['pending'=>'warning','confirmed'=>'info','out_for_delivery'=>'primary','delivered'=>'success','cancelled'=>'danger']; @endphp
        <span class="badge bg-{{ $c[$order->status]??'secondary' }} fs-6">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-3">
        {{-- Left: Order details --}}
        <div class="col-md-8">
            {{-- Items --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Order Items</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Item</th><th>Variant</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ ($item->item['name'] ?? null) ?: 'Item #'.$item->item_id }}</td>
                            <td>{{ $item->item['variant_label'] ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ $item->offer_price ?? $item->price }}</td>
                            <td>₹{{ $item->item_total }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td>Subtotal</td><td class="text-end">₹{{ $order->total_amount }}</td></tr>
                                <tr><td>GST</td><td class="text-end">₹{{ $order->gst_amount }}</td></tr>
                                <tr><td>Delivery</td><td class="text-end">₹{{ $order->delivery_charge }}</td></tr>
                                @if($order->handling_fee > 0)<tr><td>Handling</td><td class="text-end">₹{{ $order->handling_fee }}</td></tr>@endif
                                @if($order->discount_amount > 0)<tr><td class="text-success">Discount ({{ $order->coupon_code }})</td><td class="text-end text-success">-₹{{ $order->discount_amount }}</td></tr>@endif
                                @if($order->wallet_used > 0)<tr><td class="text-info">Wallet Used</td><td class="text-end text-info">-₹{{ $order->wallet_used }}</td></tr>@endif
                                <tr class="border-top"><td><strong>Total</strong></td><td class="text-end"><strong>₹{{ $order->final_amount }}</strong></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Order Timeline</h6></div>
                <div class="card-body">
                    <div class="iq-timeline ms-1">
                        @foreach($order->timeline as $tl)
                        <div class="d-flex mb-3 gap-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-32 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-check-circle text-primary small"></i>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold">{{ $tl->message }}</p>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($tl->created_at)->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Info + Actions --}}
        <div class="col-md-4">
            {{-- Customer --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Customer</h6></div>
                <div class="card-body">
                    <p class="mb-1 fw-semibold">{{ $order->user?->full_name }}</p>
                    <p class="mb-1 small text-muted">{{ $order->user?->email }}</p>
                    <p class="mb-1 small text-muted">{{ $order->user?->phone_number }}</p>
                    <hr>
                    <p class="mb-0 small fw-semibold">Delivery Address</p>
                    <p class="mb-0 small text-muted">{{ $order->address_line }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                </div>
            </div>

            {{-- Vendor --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Vendor</h6></div>
                <div class="card-body">
                    <p class="mb-1 fw-semibold">{{ $order->owner?->restaurant_name }}</p>
                    <p class="mb-0 small text-muted">{{ $order->owner?->phone_number }}</p>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Delivery Partner</h6></div>
                <div class="card-body">
                    @if($order->assignment?->boy)
                    <p class="mb-1 fw-semibold">{{ $order->assignment->boy->full_name }}</p>
                    <p class="mb-0 small text-muted">{{ $order->assignment->boy->phone_number }}</p>
                    <span class="badge bg-info-subtle text-info">{{ $order->assignment->boy->vehicle_type }}</span>
                    @else
                    <p class="text-muted mb-2">Not assigned yet</p>
                    @endif

                    @if(!in_array($order->status,['delivered','cancelled']))
                    <form method="POST" action="{{ route('admin.orders.auto-assign',$order->id) }}" class="mb-2">@csrf
                        <button class="btn btn-sm btn-primary w-100">🤖 Auto-Assign</button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.assign',$order->id) }}">@csrf
                        <div class="input-group input-group-sm">
                            <select name="delivery_boy_id" class="form-select form-select-sm" required>
                                <option value="">Select Boy...</option>
                                @foreach($deliveryBoys as $b)
                                <option value="{{ $b->id }}">{{ $b->full_name }} ({{ $b->vehicle_type }})</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-success btn-sm">Assign</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Update Status --}}
            @if(!in_array($order->status,['delivered','cancelled']))
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Update Status</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.status',$order->id) }}" class="d-flex gap-2">@csrf
                        <select name="status" class="form-select form-select-sm">
                            @foreach(['pending','confirmed','preparing','out_for_delivery','delivered'] as $s)
                            <option value="{{ $s }}" {{ $order->status==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary">Update</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Invoice + Cancel --}}
            <div class="d-grid gap-2">
                <a href="{{ route('admin.orders.invoice',$order->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">🧾 View Invoice</a>
                @if(!in_array($order->status,['cancelled','delivered']))
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel Order</button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Cancel Order #{{ $order->id }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.orders.cancel',$order->id) }}">@csrf
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Cancel Reason *</label>
            <select name="reason_id" class="form-select" required>
                <option value="">Select reason...</option>
                @foreach($deliveryBoys as $b)@endforeach
                <option value="1">Customer request</option>
                <option value="2">Item unavailable</option>
                <option value="3">No delivery partner</option>
                <option value="4">Admin cancelled</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Additional Remark</label><textarea name="remark" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button><button class="btn btn-danger btn-sm">Confirm Cancel</button></div>
    </form>
</div></div></div>
</x-app-layout>
