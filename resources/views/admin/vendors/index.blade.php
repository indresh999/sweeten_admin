
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Vendors</h4>
        <a href="{{ route('admin.vendors.pending') }}" class="btn btn-warning btn-sm">Pending Approvals</a>
    </div>
    {{-- Filters --}}
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search name/email/city..." value="{{ request('search') }}"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
            <div class="col-auto"><a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
        </form>
    </div></div>
    {{-- Table --}}
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Store</th><th>Contact</th><th>City</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($vendors as $v)
            <tr>
                <td>{{ $v->shop_id }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($v->images->first())
                        <img src="{{ asset($v->images->first()->image_path) }}" class="rounded" width="38" height="38" style="object-fit:cover">
                        @else
                        <div class="avatar avatar-38 bg-primary-subtle rounded d-flex align-items-center justify-content-center"><i class="fas fa-store text-primary"></i></div>
                        @endif
                        <div>
                            <p class="mb-0 fw-semibold">{{ $v->restaurant_name }}</p>
                            <small class="text-muted">{{ $v->email }}</small>
                        </div>
                    </div>
                </td>
                <td>{{ $v->phone_number ?? '—' }}</td>
                <td>{{ $v->city ?? '—' }}</td>
                <td><span class="badge bg-info-subtle text-info">{{ $v->items->count() ?? 0 }}</span></td>
                <td>
                    @php $sc=['active'=>'success','inactive'=>'secondary','pending'=>'warning','rejected'=>'danger']; @endphp
                    <span class="badge bg-{{ $sc[$v->status] ?? 'secondary' }}">{{ ucfirst($v->status) }}</span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.vendors.show', $v->shop_id) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                        <form method="POST" action="{{ route('admin.vendors.toggle', $v->shop_id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-{{ $v->status==='active'?'outline-warning':'outline-success' }} py-0 px-2">{{ $v->status==='active'?'Deactivate':'Activate' }}</button>
                        </form>
                        @if($v->status==='pending')
                        <form method="POST" action="{{ route('admin.vendors.approve', $v->shop_id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success py-0 px-2">Approve</button></form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No vendors found.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-3 pb-2">{{ $vendors->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
