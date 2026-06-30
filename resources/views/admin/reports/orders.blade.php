
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Order Report</h4>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-sm btn-outline-success ms-auto"><i class="fas fa-download me-1"></i>Export CSV</a>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><label class="form-label mb-0 small">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><label class="form-label mb-0 small">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto"><button class="btn btn-sm btn-primary mt-3">Apply</button></div>
        </form>
    </div></div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm"><div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Daily Orders</h6></div>
            <div class="card-body"><canvas id="orderChart" height="200"></canvas></div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-3"><div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Status Breakdown</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    @foreach($statusBreakdown as $s)
                    <tr><td>{{ ucfirst(str_replace('_',' ',$s->status)) }}</td><td class="text-end fw-bold">{{ $s->count }}</td></tr>
                    @endforeach
                </table>
            </div></div>
            <div class="card shadow-sm"><div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Payment Breakdown</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    @foreach($paymentBreakdown as $p)
                    <tr><td>{{ strtoupper($p->payment_method) }}</td><td>{{ $p->count }} orders</td><td class="text-end fw-bold">₹{{ number_format($p->total,0) }}</td></tr>
                    @endforeach
                </table>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Total Orders</th><th>Delivered</th><th>Cancelled</th></tr></thead>
            <tbody>
            @foreach($daily as $d)
            <tr>
                <td>{{ $d->date }}</td>
                <td>{{ $d->total }}</td>
                <td><span class="badge bg-success-subtle text-success">{{ $d->delivered }}</span></td>
                <td><span class="badge bg-danger-subtle text-danger">{{ $d->cancelled }}</span></td>
            </tr>
            @endforeach
            </tbody>
        </table></div>
    </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const data = @json($daily);
new Chart(document.getElementById('orderChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: data.map(d => d.date),
        datasets: [
            { label: 'Total', data: data.map(d => d.total), backgroundColor: 'rgba(58,87,232,0.3)', borderColor: '#3a57e8', borderWidth: 1 },
            { label: 'Delivered', data: data.map(d => d.delivered), backgroundColor: 'rgba(29,164,97,0.3)', borderColor: '#1da461', borderWidth: 1 },
            { label: 'Cancelled', data: data.map(d => d.cancelled), backgroundColor: 'rgba(229,57,53,0.3)', borderColor: '#e53935', borderWidth: 1 },
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
</x-app-layout>
