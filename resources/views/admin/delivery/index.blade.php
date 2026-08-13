<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Delivery Boys</h4>
            <p class="text-muted mb-0 small">{{ $boys->total() }} total riders</p>
        </div>
        <a href="{{ route('admin.delivery.docs.pending') }}" class="btn btn-sm btn-warning">
            <i class="fas fa-file-alt me-1"></i>Pending Docs
        </a>
    </div>

    {{-- Stats --}}
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @foreach([
            ['label'=>'Total','value'=>$stats['total'],'icon'=>'fa-users','color'=>'primary'],
            ['label'=>'Online','value'=>$stats['online'],'icon'=>'fa-circle','color'=>'success'],
            ['label'=>'Verified','value'=>$stats['verified'],'icon'=>'fa-check-circle','color'=>'info'],
            ['label'=>'Pending Docs','value'=>$stats['pending_docs'],'icon'=>'fa-file','color'=>'warning'],
        ] as $s)
        <div class="col">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-{{ $s['color'] }}-subtle text-{{ $s['color'] }}">
                        <i class="fas {{ $s['icon'] }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="mb-0 small text-muted">{{ $s['label'] }}</p>
                        <h5 class="mb-0 fw-bold">{{ $s['value'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search name, phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status')=='online'?'selected':'' }}>Online</option>
                        <option value="offline" {{ request('status')=='offline'?'selected':'' }}>Offline</option>
                        <option value="blocked" {{ request('status')=='blocked'?'selected':'' }}>Blocked</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="verified" class="form-select form-select-sm">
                        <option value="">Verification</option>
                        <option value="1" {{ request('verified')=='1'?'selected':'' }}>Verified</option>
                        <option value="0" {{ request('verified')=='0'?'selected':'' }}>Unverified</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
                <div class="col-6 col-md-auto">
                    <a href="{{ route('admin.delivery.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
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
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Orders</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($boys as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td><strong>{{ $b->full_name }}</strong></td>
                        <td>{{ $b->phone_number ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark">{{ ucfirst($b->vehicle_type ?? '—') }}</span></td>
                        <td><span class="badge bg-info-subtle text-info">{{ $b->assignments_count }}</span></td>
                        <td>
                            @php $sc=['online'=>'success','offline'=>'secondary','blocked'=>'danger']; @endphp
                            <span class="badge bg-{{ $sc[$b->status]??'secondary' }}">{{ ucfirst($b->status) }}</span>
                        </td>
                        <td>
                            @if($b->is_verified)<span class="badge bg-success"><i class="fas fa-check"></i> Yes</span>
                            @else<span class="badge bg-warning text-dark">No</span>@endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.delivery.show',$b->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($b->is_verified)
                                    @if($b->status === 'blocked')
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Activate"
                                        onclick="confirmDeliveryAction('{{ route('admin.delivery.activate',$b->id) }}','{{ $b->full_name }}','Activate','Activate this rider?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Deactivate"
                                        onclick="confirmDeliveryAction('{{ route('admin.delivery.deactivate',$b->id) }}','{{ $b->full_name }}','Deactivate','Deactivate this rider?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endif
                                @else
                                <button type="button" class="btn btn-sm btn-outline-{{ $b->status==='blocked'?'success':'danger' }}" title="{{ $b->status==='blocked'?'Unblock':'Block' }}"
                                    onclick="confirmDeliveryAction('{{ route('admin.delivery.toggle',$b->id) }}','{{ $b->full_name }}','{{ $b->status==='blocked'?'Unblock':'Block' }}','{{ $b->status==='blocked'?'Unblock':'Block' }} this rider?')">
                                    <i class="fas fa-{{ $b->status==='blocked'?'unlock':'lock' }}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" title="Verify"
                                    onclick="confirmDeliveryAction('{{ route('admin.delivery.verify',$b->id) }}','{{ $b->full_name }}','Verify','Verify this rider?')">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-motorcycle fa-3x d-block mb-3 opacity-25"></i>
                        <p class="mb-1 fw-semibold">No delivery boys found</p>
                    </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @forelse($boys as $b)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="min-w-0">
                        <p class="mb-0 fw-semibold">{{ $b->full_name }}</p>
                        <small class="text-muted">{{ $b->phone_number ?? '—' }}</small>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0 ms-2">
                        @php $sc=['online'=>'success','offline'=>'secondary','blocked'=>'danger']; @endphp
                        <span class="badge bg-{{ $sc[$b->status]??'secondary' }}">{{ ucfirst($b->status) }}</span>
                        @if($b->is_verified)<span class="badge bg-success"><i class="fas fa-check"></i></span>@endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3 small text-muted">
                    <span><i class="fas fa-motorcycle me-1"></i>{{ ucfirst($b->vehicle_type ?? '—') }}</span>
                    <span><i class="fas fa-shopping-bag me-1"></i>{{ $b->assignments_count }} orders</span>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.delivery.show',$b->id) }}" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="fas fa-eye me-1"></i>View
                    </a>
                    @if($b->is_verified)
                        @if($b->status === 'blocked')
                        <button type="button" class="btn btn-sm btn-outline-success flex-grow-1"
                            onclick="confirmDeliveryAction('{{ route('admin.delivery.activate',$b->id) }}','{{ $b->full_name }}','Activate','Activate this rider?')">
                            <i class="fas fa-check me-1"></i>Activate
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1"
                            onclick="confirmDeliveryAction('{{ route('admin.delivery.deactivate',$b->id) }}','{{ $b->full_name }}','Deactivate','Deactivate this rider?')">
                            <i class="fas fa-ban me-1"></i>Deactivate
                        </button>
                        @endif
                    @else
                    <button type="button" class="btn btn-sm btn-outline-info flex-grow-1"
                        onclick="confirmDeliveryAction('{{ route('admin.delivery.verify',$b->id) }}','{{ $b->full_name }}','Verify','Verify this rider?')">
                        <i class="fas fa-check-double me-1"></i>Verify
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-motorcycle fa-3x d-block mb-3 opacity-25"></i>
            <p class="mb-1 fw-semibold">No delivery boys found</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-3">{{ $boys->links('pagination::bootstrap-5') }}</div>
</div>

@push('scripts')
<script>
function confirmDeliveryAction(url, name, action, message) {
    const isDangerous = action === 'Deactivate' || action === 'Block';
    Swal.fire({
        title: `${action} Rider?`,
        html: `${message}<br><strong>"${name}"</strong>`,
        icon: isDangerous ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: isDangerous ? '#dc3545' : action === 'Verify' ? '#0dcaf0' : '#198754',
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
