
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.vendors.show',$vendor->shop_id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $vendor->restaurant_name }} — Earnings</h4>
    </div>
    <div class="alert alert-success">Total Delivered Revenue: <strong>₹{{ number_format($total,2) }}</strong></div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Subtotal</th><th>GST</th><th>Delivery</th><th>Discount</th><th>Final</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($orders as $o)
            <tr>
                <td>#{{ $o->id }}</td>
                <td>₹{{ $o->total_amount }}</td>
                <td>₹{{ $o->gst_amount }}</td>
                <td>₹{{ $o->delivery_charge }}</td>
                <td>₹{{ $o->discount_amount }}</td>
                <td><strong>₹{{ $o->final_amount }}</strong></td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($o->created_at)->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No delivered orders yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
