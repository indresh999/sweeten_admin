
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.delivery.show',$boy->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $boy->full_name }} — Earnings</h4>
    </div>
    <div class="row row-cols-3 g-3 mb-4">
        @foreach([['label'=>'Total Earned','value'=>$summary['total'],'color'=>'success'],['label'=>'Pending Payout','value'=>$summary['pending'],'color'=>'warning'],['label'=>'Paid Out','value'=>$summary['paid'],'color'=>'primary']] as $s)
        <div class="col"><div class="card shadow-sm border-0 text-center"><div class="card-body py-2">
            <h5 class="mb-0 fw-bold text-{{ $s['color'] }}">₹{{ number_format($s['value'],2) }}</h5>
            <small class="text-muted">{{ $s['label'] }}</small>
        </div></div></div>
        @endforeach
    </div>
    @if($summary['pending'] > 0)
    <div class="mb-3">
        <form method="POST" action="{{ route('admin.delivery.payouts.paid') }}">@csrf
            <input type="hidden" name="delivery_boy_id" value="{{ $boy->id }}">
            <button class="btn btn-success btn-sm" onclick="return confirm('Mark ₹{{ number_format($summary[\'pending\'],2) }} as paid?')">💰 Mark All Pending as Paid</button>
        </form>
    </div>
    @endif
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Order #</th><th>Base Earning</th><th>Net Earning</th><th>Paid?</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($earnings as $e)
            <tr>
                <td>#{{ $e->order_id }}</td>
                <td>₹{{ $e->base_earning }}</td>
                <td><strong>₹{{ $e->net_earning }}</strong></td>
                <td>@if($e->is_paid)<span class="badge bg-success">Paid</span>@else<span class="badge bg-warning text-dark">Pending</span>@endif</td>
                <td class="small text-muted">{{ $e->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No earnings yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $earnings->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
