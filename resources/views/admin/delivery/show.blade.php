<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:36px;height:36px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0">{{ $boy->full_name }}</h4>
                <small class="text-muted">{{ $boy->email }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($boy->is_verified)
                <span class="badge bg-success-subtle text-success px-3 py-2 fs-12"><i class="fas fa-check-circle me-1"></i> Verified</span>
            @else
                <span class="badge bg-warning-subtle text-warning px-3 py-2 fs-12"><i class="fas fa-clock me-1"></i> Unverified</span>
            @endif
            @php $sc=['online'=>'success','offline'=>'secondary','blocked'=>'danger']; @endphp
            <span class="badge bg-{{ $sc[$boy->status]??'secondary' }}-subtle text-{{ $sc[$boy->status]??'secondary' }} px-3 py-2 fs-12">
                @if($boy->status==='online')<i class="fas fa-circle text-success me-1" style="font-size:8px;"></i>
                @elseif($boy->status==='blocked')<i class="fas fa-ban me-1"></i>
                @else<i class="fas fa-minus-circle me-1"></i>
                @endif
                {{ ucfirst($boy->status) }}
            </span>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── LEFT COLUMN ────────────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Profile Card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center py-4">
                    @if($boy->picture)
                        <img src="{{ asset('storage/'.$boy->picture) }}" alt="{{ $boy->full_name }}"
                             class="rounded-circle mb-3" style="width:80px;height:80px;object-fit:cover;border:3px solid #e8f5e9;">
                    @else
                        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                             style="width:80px;height:80px;background:linear-gradient(135deg,#1B4332,#2D6A4F);">
                            <span class="text-white fw-bold fs-2">{{ strtoupper(substr($boy->full_name,0,1)) }}</span>
                        </div>
                    @endif
                    <h5 class="fw-bold mb-1">{{ $boy->full_name }}</h5>
                    <p class="text-muted mb-3 small">{{ $boy->working_city ?? '—' }} &bull; {{ ucfirst($boy->vehicle_type ?? '—') }}</p>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="p-2 rounded" style="background:#f0fdf4;">
                                <small class="text-muted d-block" style="font-size:10px;">Today</small>
                                <strong class="text-success">₹{{ number_format($earningStats['today'],0) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded" style="background:#eff6ff;">
                                <small class="text-muted d-block" style="font-size:10px;">This Week</small>
                                <strong class="text-primary">₹{{ number_format($earningStats['week'],0) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded" style="background:#fefce8;">
                                <small class="text-muted d-block" style="font-size:10px;">This Month</small>
                                <strong class="text-warning">₹{{ number_format($earningStats['month'],0) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        @if($boy->is_verified)
                            @if($boy->status === 'blocked')
                            <form method="POST" action="{{ route('admin.delivery.activate',$boy->id) }}">@csrf
                                <button class="btn btn-success btn-sm px-3" onclick="return confirm('Activate this delivery boy?')">
                                    <i class="fas fa-check me-1"></i> Activate
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.delivery.deactivate',$boy->id) }}">@csrf
                                <button class="btn btn-danger btn-sm px-3" onclick="return confirm('Deactivate this delivery boy?')">
                                    <i class="fas fa-ban me-1"></i> Deactivate
                                </button>
                            </form>
                            @endif
                        @else
                        <form method="POST" action="{{ route('admin.delivery.verify',$boy->id) }}">@csrf
                            <button class="btn btn-success btn-sm px-3" onclick="return confirm('Verify and activate this delivery boy?')">
                                <i class="fas fa-check-double me-1"></i> Verify & Activate
                            </button>
                        </form>
                        @if($boy->status === 'blocked')
                        <form method="POST" action="{{ route('admin.delivery.toggle',$boy->id) }}">@csrf
                            <button class="btn btn-outline-secondary btn-sm px-3">Unblock</button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Personal Details --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user me-2 text-success"></i>Personal Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:45%;">Full Name</td><td class="fw-semibold">{{ $boy->full_name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Email</td><td class="fw-semibold">{{ $boy->email ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Phone</td><td class="fw-semibold">{{ $boy->phone_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Vehicle</td><td class="fw-semibold">{{ ucfirst($boy->vehicle_type ?? '—') }}</td></tr>
                        <tr><td class="text-muted">Working City</td><td class="fw-semibold">{{ $boy->working_city ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Joined</td><td class="fw-semibold">{{ $boy->created_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Last Login</td><td class="fw-semibold">{{ $boy->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</td></tr>
                        <tr><td class="text-muted">Active Orders</td><td class="fw-semibold">{{ $boy->current_active_orders ?? 0 }} / {{ $boy->max_active_orders ?? 3 }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Bank / UPI Details (Full for Admin) --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-university me-2 text-primary"></i>Payment Details</h6>
                </div>
                <div class="card-body">
                    @if($boy->payment_type)
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:45%;">Method</td>
                            <td><span class="badge bg-{{ $boy->payment_type==='upi'?'info':'primary' }}-subtle text-{{ $boy->payment_type==='upi'?'info':'primary' }}">{{ strtoupper($boy->payment_type) }}</span></td></tr>
                        @if($boy->payment_type === 'upi')
                        <tr><td class="text-muted">UPI ID</td><td class="fw-semibold">{{ $boy->upi_id ?? '—' }}</td></tr>
                        @else
                        <tr><td class="text-muted">Account Holder</td><td class="fw-semibold">{{ $boy->bank_account_name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Account No.</td><td class="fw-semibold">{{ $boy->bank_account_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted">IFSC</td><td class="fw-semibold">{{ $boy->bank_ifsc ?? '—' }}</td></tr>
                        @endif
                    </table>
                    @else
                    <p class="text-muted mb-0 small"><i class="fas fa-info-circle me-1"></i> No payment details added yet</p>
                    @endif
                </div>
            </div>

            {{-- Total Earnings --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-wallet me-2 text-warning"></i>Earnings Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Total Earned</span>
                        <strong class="text-dark">₹{{ number_format($earningStats['total'],2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Pending Payout</span>
                        <strong class="text-warning">₹{{ number_format($earningStats['pending'],2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Orders</span>
                        <strong class="text-dark">{{ $assignments->count() }}</strong>
                    </div>
                    <div class="mt-3">
                        <form method="POST" action="{{ route('admin.delivery.payouts.paid') }}">@csrf
                            <input type="hidden" name="delivery_boy_id" value="{{ $boy->id }}">
                            <button class="btn btn-success btn-sm w-100" onclick="return confirm('Mark all pending earnings as paid?')">
                                <i class="fas fa-check-double me-1"></i> Mark All Paid
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Wallet Management --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-piggy-bank me-2 text-info"></i>Wallet</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:#f0fdf4;">
                                <small class="text-muted d-block" style="font-size:10px;">Limit</small>
                                <strong class="text-success fs-6">₹{{ number_format($boy->wallet_limit ?? 0, 0) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:#fffbeb;">
                                <small class="text-muted d-block" style="font-size:10px;">Collected</small>
                                <strong class="text-warning fs-6">₹{{ number_format($boy->wallet_collected ?? 0, 0) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:{{ ($boy->has_pending_submission ?? false) ? '#fef2f2' : '#f0fdf4' }};">
                                <small class="text-muted d-block" style="font-size:10px;">Remaining</small>
                                <strong class="text-{{ ($boy->has_pending_submission ?? false) ? 'danger' : 'success' }} fs-6">₹{{ number_format(max(0, ($boy->wallet_limit ?? 0) - ($boy->wallet_collected ?? 0)), 0) }}</strong>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.delivery.wallet.limit', $boy->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="wallet_limit" class="form-control"
                                   value="{{ $boy->wallet_limit ?? 0 }}" min="0" step="100" required>
                            <button type="submit" class="btn btn-primary">Set</button>
                        </div>
                    </form>
                    <a href="{{ route('admin.delivery.wallet.submissions', ['delivery_boy_id' => $boy->id]) }}"
                       class="btn btn-outline-info btn-sm mt-2 w-100">
                        <i class="fas fa-receipt me-1"></i> View Submissions
                    </a>
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN ───────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Documents --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-success"></i>Documents
                        @php
                            $approvedCount = $boy->documents->where('status','approved')->count();
                            $totalCount = $boy->documents->count();
                        @endphp
                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $approvedCount }}/{{ $totalCount }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($boy->documents->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open text-muted" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No documents uploaded yet</p>
                    </div>
                    @else
                    <div class="row g-2">
                        @foreach($boy->documents as $doc)
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 small">{{ ucfirst(str_replace('_',' ',$doc->doc_type)) }}</h6>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}</small>
                                    </div>
                                    @php $dc=['approved'=>'success','rejected'=>'danger','pending'=>'warning']; @endphp
                                    <span class="badge bg-{{ $dc[$doc->status]??'secondary' }}-subtle text-{{ $dc[$doc->status]??'secondary' }}">{{ ucfirst($doc->status) }}</span>
                                </div>
                                @if($doc->remarks)
                                <p class="small text-muted mb-2 fst-italic">"{{ $doc->remarks }}"</p>
                                @endif
                                <div class="d-flex gap-1">
                                    @if($doc->file_path)
                                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    @endif
                                    @if($doc->status==='pending')
                                    <form method="POST" action="{{ route('admin.delivery.docs.approve',$doc->id) }}" class="d-inline flex-fill">
                                        @csrf
                                        <button class="btn btn-sm btn-success w-100"><i class="fas fa-check me-1"></i> Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}"><i class="fas fa-times"></i></button>
                                    @endif
                                    @if($doc->status==='rejected' && $doc->remarks)
                                    <div class="mt-2 w-100">
                                        <div class="alert alert-danger py-1 px-2 mb-0 small">
                                            <i class="fas fa-info-circle me-1"></i>{{ $doc->remarks }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recent Assignments --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pb-0">
                    <h6 class="fw-bold mb-0"><i class="fas fa-truck me-2 text-primary"></i>Recent Assignments</h6>
                    <a href="{{ route('admin.delivery.orders',$boy->id) }}" class="btn btn-sm btn-outline-primary py-0">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Order #</th>
                                    <th>Store</th>
                                    <th>Status</th>
                                    <th class="pe-3">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($assignments as $a)
                            <tr>
                                <td class="ps-3 fw-semibold">#{{ $a->order_id }}</td>
                                <td>{{ $a->order?->owner?->restaurant_name ?? '—' }}</td>
                                @php $ac=['assigned'=>'info','picked'=>'primary','delivered'=>'success','rejected'=>'danger']; @endphp
                                <td><span class="badge bg-{{ $ac[$a->status]??'secondary' }}-subtle text-{{ $ac[$a->status]??'secondary' }}">{{ ucfirst($a->status) }}</span></td>
                                <td class="small text-muted pe-3">{{ $a->created_at?->format('d M, h:i A') ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox me-1"></i> No assignments yet
                            </td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rejection Modals --}}
@foreach($boy->documents as $doc)
@if($doc->status==='pending')
<div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.delivery.docs.reject',$doc->id) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold"><i class="fas fa-times-circle text-danger me-2"></i>Reject Document</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">You are rejecting: <strong>{{ ucfirst(str_replace('_',' ',$doc->doc_type)) }}</strong></p>
                    <label for="remarks{{ $doc->id }}" class="form-label small fw-semibold">Reason for rejection <span class="text-danger">*</span></label>
                    <textarea name="remarks" id="remarks{{ $doc->id }}" class="form-control" rows="3" required placeholder="e.g. Image is blurry, document is expired..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
</x-app-layout>
