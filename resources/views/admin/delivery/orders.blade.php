
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.delivery.show',$boy->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $boy->full_name }} — Assignments</h4>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Order #</th><th>Customer</th><th>Store</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($assignments as $a)
            <tr>
                <td><a href="{{ route('admin.orders.show',$a->order_id) }}" class="text-primary fw-semibold">#{{ $a->order_id }}</a></td>
                <td>{{ $a->order?->user?->full_name ?? '—' }}</td>
                <td>{{ $a->order?->owner?->restaurant_name ?? '—' }}</td>
                <td>₹{{ $a->order?->final_amount }}</td>
                @php $ac=['assigned'=>'info','picked'=>'primary','delivered'=>'success','rejected'=>'danger']; @endphp
                <td><span class="badge bg-{{ $ac[$a->status]??'secondary' }}">{{ ucfirst($a->status) }}</span></td>
                <td class="small text-muted">{{ $a->created_at->format('d M Y, h:i A') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4 text-muted">No assignments.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $assignments->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
