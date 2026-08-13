<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Vendors</h4>
            <p class="text-muted mb-0 small">{{ $vendors->total() }} total vendors</p>
        </div>
        <a href="{{ route('admin.vendors.pending') }}" class="btn btn-sm btn-warning">
            <i class="fas fa-clock me-1"></i>Pending
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search name, email, city..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                        <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-sm btn-primary w-100">Search</button>
                </div>
                <div class="col-12 col-md-auto">
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Store</th>
                            <th>Contact</th>
                            <th>City</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($vendors as $v)
                    <tr>
                        <td>{{ $v->shop_id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($v->images->first())
                                <img src="{{ asset('storage/' . $v->images->first()->image_path) }}" class="rounded" width="40" height="40" style="object-fit:cover">
                                @else
                                <div class="avatar avatar-40 bg-primary-subtle rounded d-flex align-items-center justify-content-center"><i class="fas fa-store text-primary"></i></div>
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
                    <tr><td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-store fs-2 d-block mb-2 opacity-25"></i>No vendors found
                    </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @forelse($vendors as $v)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        @if($v->images->first())
                        <img src="{{ asset('storage/' . $v->images->first()->image_path) }}" class="rounded flex-shrink-0" width="44" height="44" style="object-fit:cover">
                        @else
                        <div class="avatar avatar-44 bg-primary-subtle rounded d-flex align-items-center justify-content-center flex-shrink-0"><i class="fas fa-store text-primary"></i></div>
                        @endif
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-truncate">{{ $v->restaurant_name }}</p>
                            <small class="text-muted text-truncate d-block">{{ $v->email }}</small>
                        </div>
                    </div>
                    @php $sc=['active'=>'success','inactive'=>'secondary','pending'=>'warning','rejected'=>'danger']; @endphp
                    <span class="badge bg-{{ $sc[$v->status] ?? 'secondary' }} flex-shrink-0 ms-2">{{ ucfirst($v->status) }}</span>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3 small text-muted">
                    <span><i class="fas fa-phone me-1"></i>{{ $v->phone_number ?? '—' }}</span>
                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $v->city ?? '—' }}</span>
                    <span><i class="fas fa-box me-1"></i>{{ $v->items->count() ?? 0 }} items</span>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.vendors.show', $v->shop_id) }}" class="btn btn-sm btn-primary flex-grow-1">View</a>
                    <button type="button" class="btn btn-sm btn-{{ $v->status==='active'?'outline-warning':'outline-success' }} flex-grow-1"
                        onclick="confirmAction('toggle','{{ route('admin.vendors.toggle', $v->shop_id) }}','{{ $v->restaurant_name }}','{{ $v->status==='active'?'Deactivate':'Activate' }}')">
                        {{ $v->status==='active'?'Deactivate':'Activate' }}
                    </button>
                    @if($v->status==='pending')
                    <button type="button" class="btn btn-sm btn-success flex-grow-1"
                        onclick="confirmAction('approve','{{ route('admin.vendors.approve', $v->shop_id) }}','{{ $v->restaurant_name }}','Approve')">
                        Approve
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-store fs-2 d-block mb-2 opacity-25"></i>No vendors found
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-3">{{ $vendors->links('pagination::bootstrap-5') }}</div>
</div>

@push('scripts')
<script>
function confirmAction(type, url, name, action) {
    const isApprove = type === 'approve';
    const isDeactivate = type === 'toggle' && action === 'Deactivate';
    Swal.fire({
        title: `${action} Vendor?`,
        html: `Are you sure you want to <strong>${action.toLowerCase()}</strong> <strong>"${name}"</strong>?`,
        icon: isDeactivate ? 'warning' : isApprove ? 'question' : 'question',
        showCancelButton: true,
        confirmButtonColor: isDeactivate ? '#dc3545' : isApprove ? '#198754' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action}`,
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
</x-app-layout>
