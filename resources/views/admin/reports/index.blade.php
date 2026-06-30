
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <h4 class="fw-bold mb-4">Reports</h4>
    <div class="row row-cols-1 row-cols-md-2 g-3">
        @foreach([
            ['label'=>'Order Report','desc'=>'Daily order counts, status breakdown, payment breakdown','icon'=>'fa-shopping-bag','color'=>'primary','route'=>'admin.reports.orders'],
            ['label'=>'Revenue Report','desc'=>'Revenue by day/week/month with GST and discount breakdown','icon'=>'fa-chart-line','color'=>'success','route'=>'admin.reports.revenue'],
            ['label'=>'Vendor Report','desc'=>'Per-vendor revenue, orders, and commission','icon'=>'fa-store','color'=>'warning','route'=>'admin.earnings.vendors'],
            ['label'=>'Delivery Report','desc'=>'Delivery boy performance and payouts','icon'=>'fa-motorcycle','color'=>'info','route'=>'admin.earnings.delivery'],
        ] as $r)
        <div class="col"><a href="{{ route($r['route']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 card-hover">
                <div class="card-body d-flex gap-4 align-items-center">
                    <div class="avatar avatar-60 rounded-3 bg-{{ $r['color'] }}-subtle text-{{ $r['color'] }} d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="fas {{ $r['icon'] }} fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold text-dark">{{ $r['label'] }}</h6>
                        <p class="mb-0 small text-muted">{{ $r['desc'] }}</p>
                    </div>
                </div>
            </div>
        </a></div>
        @endforeach
    </div>
</div>
</x-app-layout>
