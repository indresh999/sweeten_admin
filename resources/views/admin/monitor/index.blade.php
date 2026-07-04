<x-app-layout :assets="$assets ?? []">
<div class="content-inner pb-0 container-fluid" id="page_layout">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Monitor Panel</h4>
            <p class="text-muted mb-0">Real-time analytics — product views, shop visits, and sales performance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.monitor.top-products') }}" class="btn btn-sm btn-outline-primary">Top Products</a>
            <a href="{{ route('admin.monitor.top-shops') }}" class="btn btn-sm btn-outline-primary">Top Shops</a>
            <a href="{{ route('admin.monitor.location') }}" class="btn btn-sm btn-outline-primary">Location View</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">Category</label>
                    <select name="category_id" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">Vendor</label>
                    <select name="shop_id" class="form-select form-select-sm" style="min-width:160px">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->shop_id }}" {{ $vendorFilter == $v->shop_id ? 'selected' : '' }}>{{ $v->restaurant_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">City</label>
                    <select name="city" class="form-select form-select-sm" style="min-width:120px">
                        <option value="">All Cities</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $cityFilter == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex gap-1">
                    <button class="btn btn-sm btn-primary mt-3">Apply</button>
                    <a href="{{ route('admin.monitor.index') }}" class="btn btn-sm btn-outline-secondary mt-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary KPIs --}}
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        @foreach([
            ['label'=>'Item Views','value'=>number_format($summary['total_item_views']),'icon'=>'fa-eye','color'=>'info'],
            ['label'=>'Shop Visits','value'=>number_format($summary['total_shop_visits']),'icon'=>'fa-store','color'=>'primary'],
            ['label'=>'Total Orders','value'=>number_format($summary['total_orders']),'icon'=>'fa-shopping-bag','color'=>'warning'],
            ['label'=>'Delivered','value'=>number_format($summary['delivered_orders']),'icon'=>'fa-check-circle','color'=>'success'],
            ['label'=>'Revenue','value'=>'₹'.number_format($summary['total_revenue'],0),'icon'=>'fa-rupee-sign','color'=>'success'],
        ] as $s)
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-45 rounded-3 bg-{{ $s['color'] }}-subtle text-{{ $s['color'] }} d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="fas {{ $s['icon'] }} fs-5"></i>
                    </div>
                    <div>
                        <p class="mb-0 small text-muted">{{ $s['label'] }}</p>
                        <h5 class="mb-0 fw-bold">{{ $s['value'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Date-wise Trend</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-chart="revenue">Revenue</button>
                        <button type="button" class="btn btn-outline-primary" data-chart="views">Views</button>
                        <button type="button" class="btn btn-outline-primary" data-chart="visits">Shop Visits</button>
                    </div>
                </div>
                <div class="card-body"><canvas id="trendChart" height="200"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Views by City</h6></div>
                <div class="card-body"><canvas id="cityChart" height="230"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Top Sold & Top Viewed --}}
    <div class="row g-3 mb-4">
        {{-- Top Sold --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Top Sold Products</h6>
                    <a href="{{ route('admin.monitor.top-products', array_merge(request()->all(), ['mode'=>'sold'])) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>Product</th><th>Vendor</th><th class="text-end">Qty</th><th class="text-end">Revenue</th></tr></thead>
                        <tbody>
                            @forelse($topSold as $i => $row)
                            <tr>
                                <td><span class="badge {{ $i < 3 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $i+1 }}</span></td>
                                <td>
                                    <p class="mb-0 fw-semibold text-truncate" style="max-width:160px">{{ $row->item?->item_name ?? 'Deleted Item' }}</p>
                                    <span class="text-muted">{{ $row->order_count }} orders</span>
                                </td>
                                <td class="text-muted text-truncate" style="max-width:120px">{{ $row->item?->owner?->restaurant_name ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->total_qty) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($row->total_revenue,0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No sales data in this period</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Viewed --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Most Viewed Products</h6>
                    <a href="{{ route('admin.monitor.top-products', array_merge(request()->all(), ['mode'=>'viewed'])) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>Product</th><th>Vendor</th><th class="text-end">Views</th><th class="text-end">Unique</th></tr></thead>
                        <tbody>
                            @forelse($topViewed as $i => $row)
                            <tr>
                                <td><span class="badge {{ $i < 3 ? 'bg-info text-dark' : 'bg-light text-dark' }}">{{ $i+1 }}</span></td>
                                <td>
                                    <p class="mb-0 fw-semibold text-truncate" style="max-width:160px">{{ $row->item?->item_name ?? 'Deleted' }}</p>
                                    <span class="text-muted small">{{ $row->item?->owner?->restaurant_name ?? '—' }}</span>
                                </td>
                                <td class="text-muted text-truncate" style="max-width:100px">{{ $row->item?->owner?->restaurant_name ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->view_count) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->unique_visitors) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No view data in this period</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Most Visited Shops --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Most Visited Shops</h6>
            <a href="{{ route('admin.monitor.top-shops') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>#</th><th>Shop</th><th>City</th><th class="text-end">Visits</th><th class="text-end">Unique Visitors</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($topShops as $i => $row)
                    <tr>
                        <td><span class="badge {{ $i < 3 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $i+1 }}</span></td>
                        <td>
                            <a href="{{ route('admin.vendors.show', $row->shop_id) }}" class="fw-semibold text-decoration-none">
                                {{ $row->shop?->restaurant_name ?? 'Unknown Shop' }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $row->shop?->city ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->visit_count) }}</td>
                        <td class="text-end text-muted">{{ number_format($row->unique_visitors) }}</td>
                        <td>
                            @php $st=$row->shop?->status; @endphp
                            <span class="badge bg-{{ $st==='active'?'success':($st==='pending'?'warning':'secondary') }}">{{ ucfirst($st ?? '—') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No visit data in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const revenueData = @json($dateChart);
const viewData    = @json($viewChart);
const cityLabels  = @json($viewsByCity->pluck('city'));
const cityCounts  = @json($viewsByCity->pluck('view_count'));
const COLORS = ['#3a57e8','#08b1ba','#fa896b','#13c296','#ffc107','#6f42c1','#e83e8c','#fd7e14','#20c997','#6c757d'];

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
let trendChart  = new Chart(trendCtx, buildTrendDataset('revenue'));

function buildTrendDataset(type) {
    if (type === 'revenue') {
        return { type:'line', data:{ labels: revenueData.map(d=>d.date), datasets:[{
            label:'Revenue (₹)', data: revenueData.map(d=>d.revenue),
            borderColor:'#3a57e8', backgroundColor:'rgba(58,87,232,0.07)', fill:true, tension:0.4, pointRadius:3
        }]}, options: chartOpts('₹') };
    } else if (type === 'views') {
        return { type:'bar', data:{ labels: viewData.map(d=>d.date), datasets:[{
            label:'Item Views', data: viewData.map(d=>d.views),
            backgroundColor:'rgba(8,177,186,0.7)', borderRadius:4
        }]}, options: chartOpts() };
    } else {
        const from = '{{ $from }}', to = '{{ $to }}';
        return { type:'bar', data:{ labels:[], datasets:[{label:'Shop Visits',data:[],backgroundColor:'rgba(250,137,107,0.7)',borderRadius:4}] }, options: chartOpts() };
    }
}

function chartOpts(prefix) {
    return { responsive:true, plugins:{legend:{display:false}},
        scales:{ y:{ beginAtZero:true, ticks:{ callback: v => prefix ? prefix+v.toLocaleString() : v.toLocaleString() } } } };
}

document.querySelectorAll('[data-chart]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-chart]').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        const type = this.dataset.chart;
        if (type === 'visits') {
            fetch('{{ route("admin.monitor.chart-data") }}?from={{ $from }}&to={{ $to }}&type=visits')
                .then(r=>r.json()).then(res => {
                    trendChart.destroy();
                    trendChart = new Chart(trendCtx, {
                        type:'bar',
                        data:{ labels:res.data.map(d=>d.date), datasets:[{
                            label:'Shop Visits', data:res.data.map(d=>d.value),
                            backgroundColor:'rgba(250,137,107,0.7)', borderRadius:4
                        }]},
                        options: chartOpts()
                    });
                });
        } else {
            trendChart.destroy();
            trendChart = new Chart(trendCtx, buildTrendDataset(type));
        }
    });
});

// City Doughnut
new Chart(document.getElementById('cityChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: cityLabels, datasets:[{ data: cityCounts, backgroundColor: COLORS, borderWidth:1 }] },
    options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } } } }
});
</script>
@endpush
</x-app-layout>
