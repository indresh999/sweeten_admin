
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <h4 class="fw-bold mb-4">Customers</h4>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name / Email / Phone..." value="{{ request('search') }}"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="blocked" {{ request('status')=='blocked'?'selected':'' }}>Blocked</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
            <div class="col-auto"><a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
        </form>
    </div></div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Wallet</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($customers as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td><strong>{{ $c->full_name ?: '(No name)' }}</strong></td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->phone_number ?? '—' }}</td>
                <td><span class="badge bg-info-subtle text-info">{{ $c->orders_count }}</span></td>
                <td>₹{{ number_format($c->wallet_balance,2) }}</td>
                <td>
                    @if($c->is_blocked)<span class="badge bg-danger">Blocked</span>
                    @else<span class="badge bg-success">Active</span>@endif
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.customers.show', $c->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                        <form method="POST" action="{{ route('admin.customers.toggle', $c->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm py-0 btn-{{ $c->is_blocked?'outline-success':'outline-danger' }}">{{ $c->is_blocked?'Unblock':'Block' }}</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $customers->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
