
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Revenue Report</h4>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><label class="form-label mb-0 small">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><label class="form-label mb-0 small">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto"><label class="form-label mb-0 small">Group By</label>
                <select name="group" class="form-select form-select-sm">
                    <option value="day" {{ $group=='day'?'selected':'' }}>Day</option>
                    <option value="week" {{ $group=='week'?'selected':'' }}>Week</option>
                    <option value="month" {{ $group=='month'?'selected':'' }}>Month</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary mt-3">Apply</button></div>
        </form>
    </div></div>

    <div class="card shadow-sm mb-4"><div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Revenue Chart</h6></div>
    <div class="card-body"><canvas id="revChart" height="140"></canvas></div></div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Period</th><th>Orders</th><th>Revenue</th><th>GST</th><th>Delivery</th><th>Discount</th></tr></thead>
            <tbody>
            @foreach($data as $d)
            <tr>
                <td>{{ $d->period }}</td>
                <td>{{ $d->orders }}</td>
                <td><strong>₹{{ number_format($d->revenue,2) }}</strong></td>
                <td>₹{{ number_format($d->gst,2) }}</td>
                <td>₹{{ number_format($d->delivery,2) }}</td>
                <td class="text-danger">₹{{ number_format($d->discount,2) }}</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr><td>Total</td><td>{{ $data->sum('orders') }}</td><td>₹{{ number_format($data->sum('revenue'),2) }}</td><td>₹{{ number_format($data->sum('gst'),2) }}</td><td>₹{{ number_format($data->sum('delivery'),2) }}</td><td class="text-danger">₹{{ number_format($data->sum('discount'),2) }}</td></tr>
            </tfoot>
        </table></div>
    </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const rev = @json($data);
new Chart(document.getElementById('revChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: rev.map(d => d.period),
        datasets: [{ label: 'Revenue (₹)', data: rev.map(d => d.revenue), borderColor: '#1da461', backgroundColor: 'rgba(29,164,97,0.1)', fill: true, tension: 0.4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => '₹'+v.toLocaleString() } } } }
});
</script>
</x-app-layout>
