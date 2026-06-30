
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Earnings Overview</h4>
        <a href="{{ route('admin.earnings.export', request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export CSV</a>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><label class="form-label mb-0 small fw-bold">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><label class="form-label mb-0 small fw-bold">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto"><button class="btn btn-sm btn-primary mt-3">Apply</button></div>
        </form>
    </div></div>

    {{-- Summary Cards --}}
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @php
        $cards = [
            ['label'=>'Total Revenue','value'=>'₹'.number_format($summary['total_revenue'],2),'color'=>'success','icon'=>'fa-rupee-sign'],
            ['label'=>'Total Orders','value'=>number_format($summary['total_orders']),'color'=>'primary','icon'=>'fa-shopping-bag'],
            ['label'=>'Total GST','value'=>'₹'.number_format($summary['total_gst'],2),'color'=>'info','icon'=>'fa-file-invoice'],
            ['label'=>'Delivery Fees','value'=>'₹'.number_format($summary['total_delivery_fee'],2),'color'=>'secondary','icon'=>'fa-motorcycle'],
            ['label'=>'Handling Fees','value'=>'₹'.number_format($summary['total_handling'],2),'color'=>'secondary','icon'=>'fa-box'],
            ['label'=>'Total Discounts','value'=>'₹'.number_format($summary['total_discount'],2),'color'=>'danger','icon'=>'fa-tag'],
            ['label'=>'Delivery Payouts','value'=>'₹'.number_format($summary['delivery_payouts'],2),'color'=>'warning','icon'=>'fa-money-bill'],
            ['label'=>'Pending Payouts','value'=>'₹'.number_format($summary['pending_payouts'],2),'color'=>'danger','icon'=>'fa-clock'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="col"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="avatar avatar-40 bg-{{ $c['color'] }}-subtle text-{{ $c['color'] }} rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="fas {{ $c['icon'] }}"></i>
            </div>
            <div><p class="mb-0 small text-muted">{{ $c['label'] }}</p><h6 class="mb-0 fw-bold">{{ $c['value'] }}</h6></div>
        </div></div></div>
        @endforeach
    </div>

    {{-- Revenue Chart --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Revenue by Day</h6></div>
        <div class="card-body"><canvas id="revChart" height="120"></canvas></div>
    </div>

    {{-- Sub-module links --}}
    <div class="row row-cols-1 row-cols-md-3 g-3">
        @foreach([
            ['label'=>'Platform Revenue','desc'=>'Per-order detailed breakdown','icon'=>'fa-chart-bar','color'=>'primary','route'=>'admin.earnings.platform'],
            ['label'=>'Vendor Earnings','desc'=>'Revenue per shop/vendor','icon'=>'fa-store','color'=>'success','route'=>'admin.earnings.vendors'],
            ['label'=>'Delivery Earnings','desc'=>'Rider payouts and pending','icon'=>'fa-motorcycle','color'=>'warning','route'=>'admin.earnings.delivery'],
        ] as $mod)
        <div class="col"><a href="{{ route($mod['route']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="avatar avatar-50 bg-{{ $mod['color'] }}-subtle text-{{ $mod['color'] }} rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="fas {{ $mod['icon'] }} fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $mod['label'] }}</h6>
                        <p class="mb-0 small text-muted">{{ $mod['desc'] }}</p>
                    </div>
                </div>
            </div>
        </a></div>
        @endforeach
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const revData = @json($revenueByDay);
new Chart(document.getElementById('revChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: revData.map(d => d.date),
        datasets: [{
            label: 'Revenue (₹)',
            data: revData.map(d => d.revenue),
            backgroundColor: 'rgba(58,87,232,0.18)',
            borderColor: '#3a57e8',
            borderWidth: 1.5,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => '₹'+v.toLocaleString() } } }
    }
});
</script>
</x-app-layout>
