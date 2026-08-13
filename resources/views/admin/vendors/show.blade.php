
<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
.shop-lightbox { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; }
.shop-lightbox.show { display:flex; }
.shop-lightbox .lb-content { position:relative; max-width:90vw; max-height:85vh; display:flex; align-items:center; justify-content:center; }
.shop-lightbox img { max-width:90vw; max-height:85vh; border-radius:10px; box-shadow:0 8px 40px rgba(0,0,0,0.5); object-fit:contain; }
.shop-lightbox .lb-close { position:fixed; top:18px; right:24px; z-index:10000; background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:22px; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
.shop-lightbox .lb-close:hover { background:rgba(255,255,255,0.3); }
.shop-lightbox .lb-title { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); color:#fff; font-size:14px; font-weight:600; background:rgba(0,0,0,0.5); padding:6px 18px; border-radius:20px; white-space:nowrap; }
.stat-card { cursor:pointer; transition:transform .15s,box-shadow .15s; }
.stat-card:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
</style>
@endpush
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $vendor->restaurant_name }}</h4>
        @php $sc=['active'=>'success','inactive'=>'secondary','pending'=>'warning']; @endphp
        <span class="badge bg-{{ $sc[$vendor->status] ?? 'secondary' }}">{{ ucfirst($vendor->status) }}</span>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><span>{{ session('success') }}</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-3">
        {{-- Stats --}}
        <div class="col-12">
            <div class="row row-cols-2 row-cols-md-5 g-3">
                @php
                $statsList = [
                    ['label'=>'Total Orders','value'=>$stats['total_orders'],'color'=>'primary'],
                    ['label'=>'Revenue','value'=>'₹'.number_format($stats['total_revenue'],0),'color'=>'success'],
                    ['label'=>'Today Orders','value'=>$stats['today_orders'],'color'=>'info'],
                    ['label'=>'Pending','value'=>$stats['pending_orders'],'color'=>'warning'],
                    ['label'=>'Total Items','value'=>$stats['total_items'],'color'=>'secondary','click'=>'shopItemsModal'],
                ];
                @endphp
                @foreach($statsList as $s)
                <div class="col">
                    @if(isset($s['click']))
                    <div class="card border-0 shadow-sm text-center stat-card" data-bs-toggle="modal" data-bs-target="#{{ $s['click'] }}">
                    @else
                    <div class="card border-0 shadow-sm text-center">
                    @endif
                        <div class="card-body py-2">
                            <h5 class="mb-0 fw-bold text-{{ $s['color'] }}">{{ $s['value'] }}</h5>
                            <small class="text-muted">{{ $s['label'] }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info + Actions --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Store Info</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted fw-semibold w-40">Owner</td><td>{{ $vendor->full_name }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Email</td><td>{{ $vendor->email }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Phone</td><td>{{ $vendor->phone_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Address</td><td>{{ $vendor->restaurant_address ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">City / State</td><td>{{ $vendor->city }}, {{ $vendor->state }}</td></tr>
                        <tr><td class="text-muted fw-semibold">GST</td><td>{{ $vendor->gst_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">FSSAI</td><td>{{ $vendor->fssai_number ?? '—' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Registered</td><td>{{ optional($vendor->created_at)->format('d M Y') ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Actions</h6></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.vendors.toggle', $vendor->shop_id) }}">@csrf
                        <button class="btn btn-sm btn-{{ $vendor->status==='active'?'warning':'success' }}">
                            {{ $vendor->status==='active' ? '⏸ Deactivate' : '▶ Activate' }}
                        </button>
                    </form>
                    @if($vendor->status==='pending')
                    <form method="POST" action="{{ route('admin.vendors.approve', $vendor->shop_id) }}">@csrf
                        <button class="btn btn-sm btn-success">✓ Approve Store</button>
                    </form>
                    @endif
                    <a href="{{ route('admin.vendors.orders', $vendor->shop_id) }}" class="btn btn-sm btn-outline-primary">📦 Orders</a>
                    <a href="{{ route('admin.vendors.earnings', $vendor->shop_id) }}" class="btn btn-sm btn-outline-success">💰 Earnings</a>
                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#emailModal">✉ Send Email</button>
                </div>
            </div>
            {{-- Shop Photos --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Shop Photos</h6></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($vendor->images as $img)
                        <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded border" width="80" height="80" style="object-fit:cover;cursor:pointer" title="{{ $img->tag }}" onclick="openShopLightbox('{{ asset('storage/' . $img->image_path) }}','{{ addslashes($img->tag ?? 'Shop Photo') }}')">
                        @empty
                        <span class="text-muted">No photos uploaded</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">Recent Orders</h6>
                    <a href="{{ route('admin.vendors.orders', $vendor->shop_id) }}" class="btn btn-sm btn-outline-primary">All Orders</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                        @php $statusColors=['pending'=>'warning','confirmed'=>'info','preparing'=>'info','out_for_delivery'=>'primary','delivered'=>'success','cancelled'=>'danger']; @endphp
                        @forelse($recentOrders as $o)
                        @php
                            $orderItemsJson = json_encode($o->items->map(function($i) {
                                $snap = is_array($i->item) ? $i->item : [];
                                return [
                                    'name'        => $snap['item_name'] ?? 'Item #'.$i->item_id,
                                    'qty'         => $i->quantity,
                                    'price'       => $i->price,
                                    'offer_price' => $i->offer_price,
                                    'total'       => $i->item_total,
                                ];
                            })->values()->toArray());
                        @endphp
                        <tr>
                            <td>#{{ $o->id }}</td>
                            <td>{{ $o->user?->full_name ?? '—' }}</td>
                            <td>₹{{ number_format($o->final_amount,2) }}</td>
                            <td><span class="badge bg-{{ $statusColors[$o->status]??'secondary' }}">{{ ucfirst(str_replace('_',' ',$o->status)) }}</span></td>
                            <td class="small text-muted">{{ $o->created_at?->format('d M') ?? '—' }}</td>
                            <td class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-info py-0"
                                    data-bs-toggle="modal" data-bs-target="#orderItemsModal"
                                    data-order="#{{ $o->id }}"
                                    data-customer="{{ $o->user?->full_name ?? 'Guest' }}"
                                    data-amount="₹{{ number_format($o->final_amount,2) }}"
                                    data-items="{{ htmlspecialchars($orderItemsJson, ENT_QUOTES) }}">
                                    Items
                                </button>
                                <a href="{{ route('admin.orders.show',$o->id) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">No orders yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Items Modal --}}
<div class="modal fade" id="orderItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="orderItemsTitle">Order Items</h5>
                    <small class="text-muted" id="orderItemsMeta"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody id="orderItemsBody"></tbody>
                    <tfoot id="orderItemsFoot" class="table-light fw-bold"></tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.getElementById('orderItemsModal').addEventListener('show.bs.modal', function(e) {
    const btn      = e.relatedTarget;
    const orderId  = btn.dataset.order;
    const customer = btn.dataset.customer;
    const amount   = btn.dataset.amount;
    const items    = JSON.parse(btn.dataset.items || '[]');  // dataset auto-decodes HTML entities

    document.getElementById('orderItemsTitle').textContent = 'Order ' + orderId + ' — Items';
    document.getElementById('orderItemsMeta').textContent  = 'Customer: ' + customer + '  |  Total: ' + amount;

    const body = document.getElementById('orderItemsBody');
    const foot = document.getElementById('orderItemsFoot');

    if (!items.length) {
        body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No item details available.</td></tr>';
        foot.innerHTML = '';
        return;
    }

    body.innerHTML = items.map((item, i) => {
        const displayPrice = item.offer_price ? item.offer_price : item.price;
        const strikePrice  = item.offer_price
            ? `<small class="text-muted text-decoration-line-through ms-1">₹${parseFloat(item.price).toFixed(2)}</small>`
            : '';
        return `<tr>
            <td class="text-muted small">${i + 1}</td>
            <td><span class="fw-semibold">${item.name}</span></td>
            <td class="text-end">₹${parseFloat(displayPrice).toFixed(2)}${strikePrice}</td>
            <td class="text-center">${item.qty}</td>
            <td class="text-end fw-bold">₹${parseFloat(item.total).toFixed(2)}</td>
        </tr>`;
    }).join('');

    foot.innerHTML = `<tr><td colspan="4" class="text-end">Grand Total</td><td class="text-end">${amount}</td></tr>`;
});
</script>
@endpush

{{-- Email Modal --}}
<div class="modal fade" id="emailModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Send Email to Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('admin.vendors.email', $vendor->shop_id) }}">@csrf
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-semibold">Subject</label><input type="text" name="subject" class="form-control" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary btn-sm">Send Email</button></div>
    </form>
</div></div></div>

{{-- Shop Items Modal --}}
<div class="modal fade" id="shopItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Items — {{ $vendor->restaurant_name }}</h5>
                    <small class="text-muted">{{ $stats['total_items'] }} item(s)</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Variants</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($vendor->items as $item)
                        <tr>
                            <td>
                                @php $imgs = is_array($item->images) ? $item->images : json_decode($item->images??'[]',true); @endphp
                                @php $itemImg = !empty($imgs) ? (str_starts_with($imgs[0], 'http') ? $imgs[0] : asset('storage/'.$imgs[0])) : null; @endphp
                                @if($itemImg)<img src="{{ $itemImg }}" width="40" height="40" class="rounded" style="object-fit:cover">
                                @else<div class="avatar avatar-40 bg-primary-subtle rounded d-flex align-items-center justify-content-center"><span style="font-size:18px">🎂</span></div>@endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->is_veg)<span style="display:inline-block;width:12px;height:12px;border:1.5px solid #2D7A2D;border-radius:2px" title="Veg"><span style="display:block;margin:1.5px auto;width:6px;height:6px;border-radius:50%;background:#2D7A2D"></span></span>
                                    @else<span style="display:inline-block;width:12px;height:12px;border:1.5px solid #D32F2F;border-radius:2px" title="Non-Veg"><span style="display:block;margin:1.5px auto;width:6px;height:6px;border-radius:50%;background:#D32F2F"></span></span>@endif
                                    <strong>{{ $item->item_name }}</strong>
                                </div>
                            </td>
                            <td>{{ $item->category?->category_name ?? '—' }}</td>
                            <td>
                                @foreach($item->variants->take(2) as $v)
                                <span class="badge bg-light text-dark me-1">{{ $v->label }}: ₹{{ $v->offer_price ?: $v->price }}</span>
                                @endforeach
                                @if($item->variants->count() > 2)<span class="badge bg-secondary">+{{ $item->variants->count()-2 }}</span>@endif
                            </td>
                            <td><span class="badge bg-{{ $item->status==='active'?'success':'secondary' }}">{{ ucfirst($item->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No items found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.items.index', ['shop_id' => $vendor->shop_id]) }}" class="btn btn-sm btn-outline-primary">View All in Items Page</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Shop Photo Lightbox --}}
<div class="shop-lightbox" id="shopLightbox" onclick="closeShopLightbox(event)">
    <button class="lb-close" onclick="closeShopLightbox(event)">&times;</button>
    <div class="lb-content" onclick="event.stopPropagation()">
        <img id="shopLbImage" alt="Shop Photo">
    </div>
    <div class="lb-title" id="shopLbTitle"></div>
</div>
</x-app-layout>

<script>
function openShopLightbox(url, title) {
    var lb = document.getElementById('shopLightbox');
    document.getElementById('shopLbImage').src = url;
    document.getElementById('shopLbTitle').textContent = title;
    lb.classList.add('show');
}
function closeShopLightbox(e) {
    document.getElementById('shopLightbox').classList.remove('show');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeShopLightbox(e);
});
</script>
