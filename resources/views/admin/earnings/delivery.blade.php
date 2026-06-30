
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.earnings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Delivery Boy Earnings</h4>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
        </form>
    </div></div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Rider</th><th>Deliveries</th><th>Total Earned</th><th>Pending Payout</th><th>Paid Out</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($boys as $b)
            <tr>
                <td><strong>{{ $b->full_name }}</strong><br><small class="text-muted">{{ $b->phone_number }}</small></td>
                <td><span class="badge bg-info-subtle text-info">{{ $b->delivery_count }}</span></td>
                <td>₹{{ number_format($b->total_earned,2) }}</td>
                <td><span class="badge bg-warning-subtle text-warning">₹{{ number_format($b->pending_amount,2) }}</span></td>
                <td>₹{{ number_format($b->total_earned - $b->pending_amount,2) }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.delivery.earnings',$b->id) }}" class="btn btn-sm btn-outline-primary py-0">Detail</a>
                        @if($b->pending_amount > 0)
                        <form method="POST" action="{{ route('admin.earnings.payout') }}" class="d-inline" onsubmit="return confirm('Mark ₹{{ number_format($b->pending_amount,2) }} as paid for {{ $b->full_name }}?')">@csrf
                            <input type="hidden" name="delivery_boy_id" value="{{ $b->id }}">
                            <button class="btn btn-sm btn-success py-0">💰 Pay</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">No data.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $boys->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
