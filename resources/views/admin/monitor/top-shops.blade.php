<x-app-layout :assets="$assets ?? []">
<div class="content-inner pb-0 container-fluid" id="page_layout">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Top Shops</h4>
            <p class="text-muted mb-0">Shops ranked by {{ $mode === 'visits' ? 'customer visits' : ($mode === 'revenue' ? 'revenue generated' : 'order count') }}.</p>
        </div>
        <a href="{{ route('admin.monitor.index') }}" class="btn btn-sm btn-outline-secondary">← Monitor Home</a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">Rank By</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="visits"  {{ $mode==='visits'?'selected':'' }}>Page Visits</option>
                        <option value="orders"  {{ $mode==='orders'?'selected':'' }}>Order Count</option>
                        <option value="revenue" {{ $mode==='revenue'?'selected':'' }}>Revenue</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
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
                    <a href="{{ route('admin.monitor.top-shops') }}" class="btn btn-sm btn-outline-secondary mt-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Shop</th>
                            <th>City</th>
                            <th>Status</th>
                            @if($mode === 'visits')
                                <th class="text-end">Total Visits</th>
                                <th class="text-end">Unique Visitors</th>
                            @elseif($mode === 'orders')
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                            @else
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Orders</th>
                            @endif
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        @php $shop = $shops[$row->shop_id] ?? null; @endphp
                        <tr>
                            <td>
                                <span class="badge {{ ($rows->firstItem() + $i) <= 3 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                    {{ $rows->firstItem() + $i }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $shop?->restaurant_name ?? 'Shop #'.$row->shop_id }}</td>
                            <td class="text-muted">{{ $shop?->city ?? '—' }}</td>
                            <td>
                                @php $st = $shop?->status; @endphp
                                <span class="badge bg-{{ $st==='active'?'success':($st==='pending'?'warning':'secondary') }}">{{ ucfirst($st ?? '—') }}</span>
                            </td>
                            @if($mode === 'visits')
                                <td class="text-end fw-bold">{{ number_format($row->visit_count) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->unique_visitors) }}</td>
                            @elseif($mode === 'orders')
                                <td class="text-end fw-bold">{{ number_format($row->order_count) }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($row->revenue,0) }}</td>
                            @else
                                <td class="text-end fw-bold text-success">₹{{ number_format($row->revenue,0) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->order_count) }}</td>
                            @endif
                            <td>
                                @if($shop)
                                <a href="{{ route('admin.vendors.show', $row->shop_id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No data found for selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
        <div class="card-footer bg-transparent">
            {{ $rows->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
</x-app-layout>
