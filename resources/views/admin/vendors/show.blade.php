
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $vendor->restaurant_name }}</h4>
        @php $sc=['active'=>'success','inactive'=>'secondary','pending'=>'warning']; @endphp
        <span class="badge bg-{{ $sc[$vendor->status] ?? 'secondary' }}">{{ ucfirst($vendor->status) }}</span>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><span>{{ session('success') }}</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-3">
        {{-- Stats --}}
        <div class="col-12">
            <div class="row row-cols-2 row-cols-md-5 g-3">
                @foreach([
                    ['label'=>'Total Orders','value'=>$stats['total_orders'],'color'=>'primary'],
                    ['label'=>'Revenue','value'=>'₹'.number_format($stats['total_revenue'],0),'color'=>'success'],
                    ['label'=>'Today Orders','value'=>$stats['today_orders'],'color'=>'info'],
                    ['label'=>'Pending','value'=>$stats['pending_orders'],'color'=>'warning'],
                    ['label'=>'Total Items','value'=>$stats['total_items'],'color'=>'secondary'],
                ] as $s)
                <div class="col"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2">
                    <h5 class="mb-0 fw-bold text-{{ $s['color'] }}">{{ $s['value'] }}</h5>
                    <small class="text-muted">{{ $s['label'] }}</small>
                </div></div></div>
                @endforeach
            </div>
        </div>

        {{-- Info + Actions --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Store Info</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted fw-semibold w-40">Owner</td><td>{{ $vendor->full_name }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Email</td><td>{{ $vendor->email }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Phone</td><td>{{ $vendor->phone_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Address</td><td>{{ $vendor->restaurant_address ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">City / State</td><td>{{ $vendor->city }}, {{ $vendor->state }}</td></tr>
                        <tr><td class="text-muted fw-semibold">GST</td><td>{{ $vendor->gst_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">FSSAI</td><td>{{ $vendor->fssai_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Registered</td><td>{{ optional($vendor->created_at)->format('d M Y') ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Actions</h6></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->shop_id) }}">@csrf
                        <button class="btn btn-sm btn-{{ $vendor->status==='active'?'warning':'success' }}">
                            {{ $vendor->status==='active' ? '⏸ Deactivate' : '▶ Activate' }}
                        </button>
                    </form>
                    @if($vendor->status==='pending')
                    <form method="POST" action="{{ route('admin.vendors.approve', $vendor->shop_id) }}">@csrf
                        <button class="btn btn-sm btn-success">✓ Approve Store</button>
                    </form>
                    @endif
                    <a href="{{ route('admin.vendors.orders', $vendor->shop_id) }}" class="btn btn-sm btn-outline-primary">📦 Orders</a>
                    <a href="{{ route('admin.vendors.earnings', $vendor->shop_id) }}" class="btn btn-sm btn-outline-success">💰 Earnings</a>
                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#emailModal">✉ Send Email</button>
                </div>
            </div>
            {{-- Shop Photos --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Shop Photos</h6></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($vendor->images as $img)
                        <img src="{{ asset($img->image_path) }}" class="rounded border" width="80" height="80" style="object-fit:cover" title="{{ $img->tag }}">
                        @empty
                        <span class="text-muted">No photos uploaded</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">Recent Orders</h6>
                    <a href="{{ route('admin.vendors.orders', $vendor->shop_id) }}" class="btn btn-sm btn-outline-primary">All Orders</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                        @forelse($recentOrders as $o)
                        <tr>
                            <td>#{{ $o->id }}</td>
                            <td>{{ $o->user?->full_name ?? '—' }}</td>
                            <td>₹{{ number_format($o->final_amount,2) }}</td>
                            <td>@php $c=['pending'=>'warning','delivered'=>'success','cancelled'=>'danger']; @endphp<span class="badge bg-{{ $c[$o->status]??'secondary' }}">{{ ucfirst($o->status) }}</span></td>
                            <td class="small text-muted">{{ $o->created_at->format('d M') }}</td>
                            <td><a href="{{ route('admin.orders.show',$o->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">No orders yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Email Modal --}}
<div class="modal fade" id="emailModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Send Email to Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.vendors.email', $vendor->shop_id) }}">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-semibold">Subject</label><input type="text" name="subject" class="form-control" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary btn-sm">Send Email</button></div>
    </form>
</div></div></div>
</x-app-layout>
