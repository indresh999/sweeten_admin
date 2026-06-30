
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Delivery Boys</h4>
        <a href="{{ route('admin.delivery.docs.pending') }}" class="btn btn-warning btn-sm">Pending Documents</a>
    </div>

    {{-- Stats --}}
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @foreach([
            ['label'=>'Total','value'=>$stats['total'],'color'=>'primary'],
            ['label'=>'Online','value'=>$stats['online'],'color'=>'success'],
            ['label'=>'Verified','value'=>$stats['verified'],'color'=>'info'],
            ['label'=>'Pending Docs','value'=>$stats['pending_docs'],'color'=>'warning'],
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
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name / Phone..." value="{{ request('search') }}"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="online" {{ request('status')=='online'?'selected':'' }}>Online</option>
                    <option value="offline" {{ request('status')=='offline'?'selected':'' }}>Offline</option>
                    <option value="blocked" {{ request('status')=='blocked'?'selected':'' }}>Blocked</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="verified" class="form-select form-select-sm">
                    <option value="">Verification</option>
                    <option value="1" {{ request('verified')=='1'?'selected':'' }}>Verified</option>
                    <option value="0" {{ request('verified')=='0'?'selected':'' }}>Unverified</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
            <div class="col-auto"><a href="{{ route('admin.delivery.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
        </form>
    </div></div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Phone</th><th>Vehicle</th><th>Orders</th><th>Status</th><th>Verified</th><th>Actions</th></tr></thead>
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
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('admin.delivery.show',$b->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                        <form method="POST" action="{{ route('admin.delivery.toggle',$b->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm py-0 btn-{{ $b->status==='blocked'?'outline-success':'outline-danger' }}">{{ $b->status==='blocked'?'Unblock':'Block' }}</button>
                        </form>
                        @if(!$b->is_verified)
                        <form method="POST" action="{{ route('admin.delivery.verify',$b->id) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-info py-0">✓ Verify</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">No delivery boys found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $boys->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
