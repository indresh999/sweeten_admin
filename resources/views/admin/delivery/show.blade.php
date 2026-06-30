
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $boy->full_name }}</h4>
        @if($boy->is_verified)<span class="badge bg-success">Verified</span>@else<span class="badge bg-warning text-dark">Unverified</span>@endif
        @php $sc=['online'=>'success','offline'=>'secondary','blocked'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$boy->status]??'secondary' }}">{{ ucfirst($boy->status) }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-4">
            {{-- Profile --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Profile</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Phone</td><td>{{ $boy->phone_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Email</td><td>{{ $boy->email ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Vehicle</td><td>{{ ucfirst($boy->vehicle_type ?? '—') }}</td></tr>
                        <tr><td class="text-muted">Active Orders</td><td>{{ $boy->current_active_orders }}</td></tr>
                        <tr><td class="text-muted">Max Orders</td><td>{{ $boy->max_active_orders ?? 3 }}</td></tr>
                        <tr><td class="text-muted">Joined</td><td>{{ $boy->created_at->format('d M Y') }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Earnings --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Earnings</h6></div>
                <div class="card-body">
                    @foreach([['label'=>'Today','value'=>$earningStats['today']],['label'=>'This Week','value'=>$earningStats['week']],['label'=>'This Month','value'=>$earningStats['month']],['label'=>'Total','value'=>$earningStats['total']],['label'=>'Pending Payout','value'=>$earningStats['pending'],'color'=>'warning']] as $e)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted small">{{ $e['label'] }}</span>
                        <strong class="text-{{ $e['color']??'dark' }}">₹{{ number_format($e['value'],2) }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Actions</h6></div>
                <div class="card-body d-grid gap-2">
                    <form method="POST" action="{{ route('admin.delivery.toggle',$boy->id) }}">@csrf
                        <button class="btn btn-sm w-100 btn-{{ $boy->status==='blocked'?'success':'danger' }}">{{ $boy->status==='blocked'?'Unblock':'Block' }}</button>
                    </form>
                    @if(!$boy->is_verified)
                    <form method="POST" action="{{ route('admin.delivery.verify',$boy->id) }}">@csrf
                        <button class="btn btn-sm btn-info w-100">✓ Verify Boy</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.delivery.payouts.paid') }}">@csrf
                        <input type="hidden" name="delivery_boy_id" value="{{ $boy->id }}">
                        <button class="btn btn-sm btn-success w-100" onclick="return confirm('Mark all pending earnings as paid?')">💰 Mark All Paid</button>
                    </form>
                    <a href="{{ route('admin.delivery.orders',$boy->id) }}" class="btn btn-sm btn-outline-primary">📦 All Orders</a>
                    <a href="{{ route('admin.delivery.earnings',$boy->id) }}" class="btn btn-sm btn-outline-success">💰 Earnings Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Documents --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Documents</h6></div>
                <div class="card-body">
                    @forelse($boy->documents as $doc)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <p class="mb-0 fw-semibold">{{ ucfirst(str_replace('_',' ',$doc->doc_type)) }}</p>
                            <small class="text-muted">Uploaded {{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y') }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @php $dc=['approved'=>'success','rejected'=>'danger','pending'=>'warning']; @endphp
                            <span class="badge bg-{{ $dc[$doc->status]??'secondary' }}">{{ ucfirst($doc->status) }}</span>
                            @if($doc->file_path)<a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0">View</a>@endif
                            @if($doc->status==='pending')
                            <form method="POST" action="{{ route('admin.delivery.docs.approve',$doc->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success py-0">✓</button></form>
                            <form method="POST" action="{{ route('admin.delivery.docs.reject',$doc->id) }}" class="d-inline">@csrf<input type="hidden" name="remarks" value="Document not accepted"><button class="btn btn-sm btn-danger py-0">✗</button></form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No documents uploaded</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Assignments --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">Recent Assignments</h6>
                    <a href="{{ route('admin.delivery.orders',$boy->id) }}" class="btn btn-sm btn-outline-primary py-0">All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Order #</th><th>Store</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($assignments as $a)
                        <tr>
                            <td>#{{ $a->order_id }}</td>
                            <td>{{ $a->order?->owner?->restaurant_name ?? '—' }}</td>
                            @php $ac=['assigned'=>'info','picked'=>'primary','delivered'=>'success','rejected'=>'danger']; @endphp
                            <td><span class="badge bg-{{ $ac[$a->status]??'secondary' }}">{{ ucfirst($a->status) }}</span></td>
                            <td class="small text-muted">{{ $a->created_at->format('d M') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">No assignments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
