
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.earnings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Platform Revenue</h4>
        <a href="{{ route('admin.earnings.export', request()->query()) }}" class="btn btn-sm btn-outline-success ms-auto"><i class="fas fa-download me-1"></i>Export</a>
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
            <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Vendor</th><th>Subtotal</th><th>GST</th><th>Delivery</th><th>Discount</th><th>Wallet</th><th>Final</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($data as $o)
            <tr>
                <td><a href="{{ route('admin.orders.show',$o->id) }}" class="text-primary">#{{ $o->id }}</a></td>
                <td>{{ $o->user?->full_name ?? '—' }}</td>
                <td>{{ $o->owner?->restaurant_name ?? '—' }}</td>
                <td>₹{{ $o->total_amount }}</td>
                <td>₹{{ $o->gst_amount }}</td>
                <td>₹{{ $o->delivery_charge }}</td>
                <td class="text-danger">-₹{{ $o->discount_amount }}</td>
                <td class="text-info">-₹{{ $o->wallet_used }}</td>
                <td><strong>₹{{ $o->final_amount }}</strong></td>
                <td class="small text-muted">{{ \Carbon\Carbon::parse($o->created_at)->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center py-4 text-muted">No data for this range.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $data->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
