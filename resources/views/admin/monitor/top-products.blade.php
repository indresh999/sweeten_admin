<x-app-layout :assets="$assets ?? []">
<div class="content-inner pb-0 container-fluid" id="page_layout">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Top Products</h4>
            <p class="text-muted mb-0">{{ $mode === 'sold' ? 'Most sold products by quantity and revenue.' : 'Most viewed products by page hits.' }}</p>
        </div>
        <a href="{{ route('admin.monitor.index') }}" class="btn btn-sm btn-outline-secondary">← Monitor Home</a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">Mode</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="sold" {{ $mode==='sold'?'selected':'' }}>Top Sold</option>
                        <option value="viewed" {{ $mode==='viewed'?'selected':'' }}>Most Viewed</option>
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
                @if($mode === 'viewed')
                <div class="col-auto">
                    <label class="form-label mb-0 small fw-bold">City</label>
                    <select name="city" class="form-select form-select-sm" style="min-width:120px">
                        <option value="">All Cities</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $cityFilter == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-auto d-flex gap-1">
                    <button class="btn btn-sm btn-primary mt-3">Apply</button>
                    <a href="{{ route('admin.monitor.top-products') }}" class="btn btn-sm btn-outline-secondary mt-3">Reset</a>
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
                            <th>Product</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            @if($mode === 'sold')
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                            @else
                                <th class="text-end">Views</th>
                                <th class="text-end">Unique</th>
                            @endif
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        @php $item = $items[$row->item_id] ?? null; @endphp
                        <tr>
                            <td>
                                <span class="badge {{ ($rows->firstItem() + $i) <= 3 ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                    {{ $rows->firstItem() + $i }}
                                </span>
                            </td>
                            <td>
                                <p class="mb-0 fw-semibold">{{ $item?->item_name ?? 'Deleted Item #'.$row->item_id }}</p>
                            </td>
                            <td class="text-muted small">{{ $item?->category?->category_name ?? '—' }}</td>
                            <td class="text-muted small">{{ $item?->owner?->restaurant_name ?? '—' }}</td>
                            <td>
                                @if($item)
                                    @if($item->offer_price)
                                        <span class="text-danger fw-bold">₹{{ number_format($item->offer_price,0) }}</span>
                                        <small class="text-muted text-decoration-line-through ms-1">₹{{ number_format($item->price,0) }}</small>
                                    @else
                                        <span class="fw-bold">₹{{ number_format($item->price,0) }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            @if($mode === 'sold')
                                <td class="text-end fw-bold">{{ number_format($row->total_qty) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->order_count) }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($row->total_revenue,0) }}</td>
                            @else
                                <td class="text-end fw-bold">{{ number_format($row->view_count) }}</td>
                                <td class="text-end text-muted">{{ number_format($row->unique_visitors) }}</td>
                            @endif
                            <td>
                                @if($item)
                                    <span class="badge bg-{{ $item->status==='active'?'success':'secondary' }}">{{ ucfirst($item->status) }}</span>
                                @else
                                    <span class="badge bg-danger">Deleted</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">No data found for selected filters.</td></tr>
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
