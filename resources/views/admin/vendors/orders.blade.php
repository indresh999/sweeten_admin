
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.vendors.show',$vendor->shop_id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $vendor->restaurant_name }} — Orders</h4>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($orders as $o)
            <tr>
                <td>#{{ $o->id }}</td>
                <td>{{ $o->user?->full_name ?? '—' }}</td>
                <td>₹{{ number_format($o->final_amount,2) }}</td>
                <td>{{ strtoupper($o->payment_method) }}</td>
                <td>@php $c=['pending'=>'warning','delivered'=>'success','cancelled'=>'danger']; @endphp<span class="badge bg-{{ $c[$o->status]??'secondary' }}">{{ ucfirst($o->status) }}</span></td>
                <td class="small text-muted">{{ $o->created_at->format('d M Y, h:i A') }}</td>
                <td><a href="{{ route('admin.orders.show',$o->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
