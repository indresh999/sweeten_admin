<x-app-layout :assets="$assets ?? []">
<div class="content-inner pb-0 container-fluid" id="page_layout">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Location Analytics</h4>
            <p class="text-muted mb-0">Where your customers are viewing products, visiting shops, and placing orders.</p>
        </div>
        <a href="{{ route('admin.monitor.index') }}" class="btn btn-sm btn-outline-secondary">← Monitor Home</a>
    </div>

    {{-- Date filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto"><label class="form-label mb-0 small fw-bold">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
                <div class="col-auto"><label class="form-label mb-0 small fw-bold">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
                <div class="col-auto d-flex gap-1"><button class="btn btn-sm btn-primary mt-3">Apply</button><a href="{{ route('admin.monitor.location') }}" class="btn btn-sm btn-outline-secondary mt-3">Reset</a></div>
            </form>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Item Views by City</h6></div>
                <div class="card-body"><canvas id="viewCityChart" height="260"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Shop Visits by City</h6></div>
                <div class="card-body"><canvas id="visitCityChart" height="260"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Orders by City</h6></div>
                <div class="card-body"><canvas id="orderCityChart" height="260"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Tables row --}}
    <div class="row g-3">
        {{-- Item views --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Item Views by City</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>City</th><th class="text-end">Views</th><th class="text-end">Users</th></tr></thead>
                        <tbody>
                            @forelse($itemViewsByCity as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td class="fw-semibold">{{ $row->city }}</td>
                                <td class="text-end">{{ number_format($row->views) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->unique_users) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Shop visits --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Shop Visits by City</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>City</th><th class="text-end">Visits</th><th class="text-end">Users</th></tr></thead>
                        <tbody>
                            @forelse($shopVisitsByCity as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td class="fw-semibold">{{ $row->city }}</td>
                                <td class="text-end">{{ number_format($row->visits) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->unique_users) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Orders --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Orders by City</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>City</th><th class="text-end">Orders</th><th class="text-end">Revenue</th></tr></thead>
                        <tbody>
                            @forelse($ordersByCity as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td class="fw-semibold">{{ $row->city }}</td>
                                <td class="text-end">{{ number_format($row->orders) }}</td>
                                <td class="text-end text-success fw-bold">₹{{ number_format($row->revenue,0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const COLORS = ['#3a57e8','#08b1ba','#fa896b','#13c296','#ffc107','#6f42c1','#e83e8c','#fd7e14','#20c997','#6c757d'];

function barChart(id, labels, values, color, prefix) {
    new Chart(document.getElementById(id).getContext('2d'), {
        type: 'bar',
        data: { labels: labels, datasets:[{ data: values, backgroundColor: color+'cc', borderRadius:4 }] },
        options: { indexAxis:'y', responsive:true, plugins:{legend:{display:false}},
            scales:{ x:{ beginAtZero:true, ticks:{ callback: v => prefix ? prefix+v.toLocaleString() : v.toLocaleString() } } } }
    });
}

barChart('viewCityChart',
    @json($itemViewsByCity->pluck('city')),
    @json($itemViewsByCity->pluck('views')),
    '#08b1ba'
);
barChart('visitCityChart',
    @json($shopVisitsByCity->pluck('city')),
    @json($shopVisitsByCity->pluck('visits')),
    '#3a57e8'
);
barChart('orderCityChart',
    @json($ordersByCity->pluck('city')),
    @json($ordersByCity->pluck('revenue')),
    '#13c296', '₹'
);
</script>
@endpush
</x-app-layout>
