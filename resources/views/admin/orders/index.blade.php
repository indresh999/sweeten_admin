
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Orders</h4>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export CSV</a>
    </div>

    {{-- Summary Cards --}}
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        @foreach([
            ['label'=>'Total','value'=>$summary['total'],'color'=>'primary'],
            ['label'=>'Pending','value'=>$summary['pending'],'color'=>'warning'],
            ['label'=>'Delivered','value'=>$summary['delivered'],'color'=>'success'],
            ['label'=>'Cancelled','value'=>$summary['cancelled'],'color'=>'danger'],
            ['label'=>'Revenue','value'=>'₹'.number_format($summary['revenue'],0),'color'=>'info'],
        ] as $s)
        <div class="col"><div class="card shadow-sm border-0 text-center"><div class="card-body py-2">
            <h5 class="mb-0 fw-bold text-{{ $s['color'] }}">{{ $s['value'] }}</h5>
            <small class="text-muted">{{ $s['label'] }}</small>
        </div></div></div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Order #" value="{{ request('search') }}"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="payment" class="form-select form-select-sm">
                    <option value="">Payment</option>
                    <option value="cod" {{ request('payment')=='cod'?'selected':'' }}>COD</option>
                    <option value="online" {{ request('payment')=='online'?'selected':'' }}>Online</option>
                    <option value="wallet" {{ request('payment')=='wallet'?'selected':'' }}>Wallet</option>
                </select>
            </div>
            <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
            <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
            <div class="col-auto"><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
        </form>
    </div></div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Store</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($orders as $o)
            <tr>
                <td><a href="{{ route('admin.orders.show',$o->id) }}" class="fw-semibold text-primary">#{{ $o->id }}</a></td>
                <td>{{ $o->user?->full_name ?? '—' }}<br><small class="text-muted">{{ $o->user?->phone_number }}</small></td>
                <td>{{ $o->owner?->restaurant_name ?? '—' }}</td>
                <td><strong>₹{{ number_format($o->final_amount,2) }}</strong></td>
                <td><span class="badge bg-light text-dark">{{ strtoupper($o->payment_method) }}</span></td>
                <td>
                    @php $c=['pending'=>'warning','confirmed'=>'info','preparing'=>'info','out_for_delivery'=>'primary','delivered'=>'success','cancelled'=>'danger']; @endphp
                    <span class="badge bg-{{ $c[$o->status]??'secondary' }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span>
                </td>
                <td class="small text-muted">{{ $o->created_at->format('d M, h:i A') }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.orders.show',$o->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                        @if(!in_array($o->status,['delivered','cancelled']))
                        <form method="POST" action="{{ route('admin.orders.auto-assign',$o->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2" title="Auto-assign delivery">🛵</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
