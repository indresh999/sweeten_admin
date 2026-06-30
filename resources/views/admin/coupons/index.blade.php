
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Coupons</h4>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Coupon</a>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Coupon code..." value="{{ request('search') }}"></div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="percent" {{ request('type')=='percent'?'selected':'' }}>Percentage</option>
                    <option value="flat" {{ request('type')=='flat'?'selected':'' }}>Flat</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
        </form>
    </div></div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Code</th><th>Title</th><th>Discount</th><th>Min Order</th><th>Valid Until</th><th>Used</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($coupons as $c)
            <tr>
                <td><code class="fs-6">{{ $c->code }}</code></td>
                <td>{{ $c->title }}</td>
                <td>
                    @if($c->discount_type=='percent')
                    <span class="badge bg-primary-subtle text-primary">{{ $c->discount_value }}%{{ $c->max_discount_amount ? ' (max ₹'.$c->max_discount_amount.')' : '' }}</span>
                    @else
                    <span class="badge bg-success-subtle text-success">₹{{ $c->discount_value }} OFF</span>
                    @endif
                </td>
                <td>₹{{ $c->min_order_amount }}</td>
                <td class="{{ now()->gt($c->valid_until)?'text-danger':'' }}">{{ \Carbon\Carbon::parse($c->valid_until)->format('d M Y') }}</td>
                <td>{{ $c->usages_count }} @if($c->usage_limit)/ {{ $c->usage_limit }}@endif</td>
                <td>@if($c->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('admin.coupons.edit',$c->id) }}" class="btn btn-sm btn-outline-primary py-0">Edit</a>
                        <a href="{{ route('admin.coupons.usage',$c->id) }}" class="btn btn-sm btn-outline-secondary py-0">Usage</a>
                        <form method="POST" action="{{ route('admin.coupons.toggle',$c->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm py-0 btn-{{ $c->is_active?'outline-warning':'outline-success' }}">{{ $c->is_active?'Disable':'Enable' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.coupons.destroy',$c->id) }}" class="d-inline" onsubmit="return confirm('Delete this coupon?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">No coupons yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $coupons->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
