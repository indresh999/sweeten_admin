
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Coupon Usage — <code>{{ $coupon->code }}</code></h4>
    </div>
    <div class="alert alert-info">Total used: <strong>{{ $coupon->used_count }}</strong> times | Savings given: <strong>₹{{ number_format($usages->sum('discount_given'),2) }}</strong></div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Order</th><th>Discount Given</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($usages as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>{{ $u->user?->full_name }}<br><small class="text-muted">{{ $u->user?->email }}</small></td>
                <td><a href="{{ route('admin.orders.show',$u->order_id) }}" class="text-primary">#{{ $u->order_id }}</a><br><small class="text-muted">₹{{ $u->order?->final_amount }}</small></td>
                <td><strong class="text-success">₹{{ $u->discount_given }}</strong></td>
                <td class="small text-muted">{{ $u->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">Not used yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $usages->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
