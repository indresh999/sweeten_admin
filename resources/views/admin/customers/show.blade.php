
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $customer->full_name ?: $customer->email }}</h4>
        @if($customer->is_blocked)<span class="badge bg-danger">Blocked</span>@else<span class="badge bg-success">Active</span>@endif
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Customer Info</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Email</td><td>{{ $customer->email }}</td></tr>
                        <tr><td class="text-muted">Phone</td><td>{{ $customer->phone_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Referral</td><td><code>{{ $customer->referral_code ?? '—' }}</code></td></tr>
                        <tr><td class="text-muted">Joined</td><td>{{ $customer->created_at?->format('d M Y') ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Stats</h6></div>
                <div class="card-body">
                    @foreach([['label'=>'Total Orders','value'=>$stats['total_orders']],['label'=>'Total Spent','value'=>'₹'.number_format($stats['total_spent'],2)],['label'=>'Cancelled','value'=>$stats['cancelled_orders']],['label'=>'Wallet','value'=>'₹'.number_format($stats['wallet_balance'],2)]] as $s)
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted small">{{ $s['label'] }}</span><strong>{{ $s['value'] }}</strong></div>
                    @endforeach
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Actions</h6></div>
                <div class="card-body d-grid gap-2">
                    <form method="POST" action="{{ route('admin.customers.toggle', $customer->id) }}">@csrf
                        <button class="btn btn-sm w-100 btn-{{ $customer->is_blocked?'success':'danger' }}">{{ $customer->is_blocked?'Unblock Customer':'Block Customer' }}</button>
                    </form>
                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#emailModal">✉ Send Email</button>
                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#walletModal">💰 Credit Wallet</button>
                    <a href="{{ route('admin.customers.orders', $customer->id) }}" class="btn btn-sm btn-outline-primary">📦 All Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Recent Orders</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Store</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                        @forelse($orders as $o)
                        <tr>
                            <td>#{{ $o->id }}</td>
                            <td>{{ $o->owner?->restaurant_name ?? '—' }}</td>
                            <td>₹{{ $o->final_amount }}</td>
                            <td>@php $c=['pending'=>'warning','delivered'=>'success','cancelled'=>'danger']; @endphp<span class="badge bg-{{ $c[$o->status]??'secondary' }}">{{ ucfirst($o->status) }}</span></td>
                            <td class="small text-muted">{{ $o->created_at?->format('d M Y') ?? '—' }}</td>
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
    <div class="modal-header"><h5 class="modal-title">Send Email</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.customers.email', $customer->id) }}">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary btn-sm">Send</button></div>
    </form>
</div></div></div>

{{-- Wallet Modal --}}
<div class="modal fade" id="walletModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Credit Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.customers.wallet', $customer->id) }}">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Amount (₹)</label><input type="number" name="amount" class="form-control" min="1" max="10000" required></div>
        <div class="mb-3"><label class="form-label">Note</label><input type="text" name="note" class="form-control" placeholder="e.g. Goodwill credit"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success btn-sm">Credit</button></div>
    </form>
</div></div></div>
</x-app-layout>
