
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.earnings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Vendor Earnings</h4>
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
            <thead class="table-light"><tr><th>Vendor</th><th>City</th><th>Orders</th><th>Gross Revenue</th><th>GST</th><th>Discounts</th><th>Net Approx</th><th></th></tr></thead>
            <tbody>
            @forelse($vendors as $v)
            @php $net = $v->gross_revenue - $v->total_gst - $v->total_discount; @endphp
            <tr>
                <td><strong>{{ $v->restaurant_name }}</strong></td>
                <td>{{ $v->city ?? '—' }}</td>
                <td><span class="badge bg-info-subtle text-info">{{ $v->order_count }}</span></td>
                <td>₹{{ number_format($v->gross_revenue,2) }}</td>
                <td>₹{{ number_format($v->total_gst,2) }}</td>
                <td class="text-danger">₹{{ number_format($v->total_discount,2) }}</td>
                <td><strong>₹{{ number_format($net,2) }}</strong></td>
                <td><a href="{{ route('admin.vendors.earnings',$v->shop_id) }}" class="btn btn-sm btn-outline-primary py-0">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">No data.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $vendors->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
