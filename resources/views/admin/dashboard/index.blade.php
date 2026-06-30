
<x-app-layout :assets="$assets ?? []">
    <div class="content-inner pb-0 container-fluid" id="page_layout">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Sweetan Admin</h4>
                <p class="text-muted mb-0">Welcome back! Here's what's happening today.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Order</a>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-bell me-1"></i>Notify</a>
            </div>
        </div>

        {{-- Date filter --}}
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-auto"><label class="form-label mb-0 small fw-bold">From</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
                    <div class="col-auto"><label class="form-label mb-0 small fw-bold">To</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
                    <div class="col-auto"><button class="btn btn-sm btn-primary mt-3">Apply</button></div>
                    <div class="col-auto"><a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary mt-3">Reset</a></div>
                </form>
            </div>
        </div>

        {{-- Stats Row 1 --}}
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-3">
            @foreach([
                ['label'=>'Total Customers','value'=>number_format($stats['total_customers']),'icon'=>'fa-users','color'=>'primary','link'=>route('admin.customers.index')],
                ['label'=>'Active Vendors','value'=>$stats['active_vendors'].' / '.$stats['total_vendors'],'icon'=>'fa-store','color'=>'success','link'=>route('admin.vendors.index')],
                ['label'=>'Today Orders','value'=>number_format($stats['today_orders']),'icon'=>'fa-shopping-bag','color'=>'info','link'=>route('admin.orders.index')],
                ['label'=>'Pending Orders','value'=>number_format($stats['pending_orders']),'icon'=>'fa-clock','color'=>'warning','link'=>route('admin.orders.index','status=pending')],
            ] as $s)
            <div class="col">
                <a href="{{ $s['link'] }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm">
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
                </a>
            </div>
            @endforeach
        </div>

        {{-- Stats Row 2 --}}
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
            @foreach([
                ['label'=>'Today Revenue','value'=>'₹'.number_format($stats['today_revenue'],2),'icon'=>'fa-rupee-sign','color'=>'success'],
                ['label'=>'Month Revenue','value'=>'₹'.number_format($stats['month_revenue'],2),'icon'=>'fa-chart-line','color'=>'primary'],
                ['label'=>'Range Revenue','value'=>'₹'.number_format($stats['range_revenue'],2),'icon'=>'fa-calendar','color'=>'info'],
                ['label'=>'Online Riders','value'=>$stats['online_delivery_boys'].' / '.$stats['total_delivery_boys'],'icon'=>'fa-motorcycle','color'=>'warning'],
            ] as $s)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
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

        <div class="row g-3 mb-4">
            {{-- Revenue Chart --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="fw-bold mb-0">Revenue (Last 30 days)</h6>
                    </div>
                    <div class="card-body"><canvas id="revenueChart" height="220"></canvas></div>
                </div>
            </div>
            {{-- Top Vendors --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0"><h6 class="fw-bold mb-0">Top Vendors</h6></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($topVendors as $v)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 small fw-semibold">{{ $v->restaurant_name }}</p>
                                    <span class="badge bg-primary-subtle text-primary">{{ $v->order_count }} orders</span>
                                </div>
                                <strong>₹{{ number_format($v->revenue,0) }}</strong>
                            </li>
                            @empty
                            <li class="list-group-item text-muted text-center py-4">No data yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Recent Orders</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Vendor</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                            @forelse($recentOrders as $o)
                            <tr>
                                <td><span class="badge bg-light text-dark">#{{ $o->id }}</span></td>
                                <td>{{ $o->user?->full_name ?? '—' }}</td>
                                <td>{{ $o->owner?->restaurant_name ?? '—' }}</td>
                                <td><strong>₹{{ number_format($o->final_amount,2) }}</strong></td>
                                <td>
                                    @php $colors=['pending'=>'warning','confirmed'=>'info','delivered'=>'success','cancelled'=>'danger','out_for_delivery'=>'primary']; @endphp
                                    <span class="badge bg-{{ $colors[$o->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span>
                                </td>
                                <td class="small text-muted">{{ $o->created_at->format('d M, h:i A') }}</td>
                                <td><a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No orders in this range</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const chartData = @json($chartData);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Revenue (₹)',
                data: chartData.map(d => d.revenue),
                borderColor: '#3a57e8',
                backgroundColor: 'rgba(58,87,232,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '₹'+v.toLocaleString() } } }
        }
    });
    </script>
    @endpush
</x-app-layout>
