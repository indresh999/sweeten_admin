
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Items</h4>
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Item</a>
    </div>
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Item name..." value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="shop_id" class="form-select form-select-sm">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)<option value="{{ $v->shop_id }}" {{ request('shop_id')==$v->shop_id?'selected':'' }}>{{ $v->restaurant_name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->category_name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
            <div class="col-auto"><a href="{{ route('admin.items.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
        </form>
    </div></div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Image</th><th>Name</th><th>Vendor</th><th>Category</th><th>Variants</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
            <tr>
                <td>
                    @php $imgs = is_array($item->images) ? $item->images : json_decode($item->images??'[]',true); @endphp
                    @if(!empty($imgs))<img src="{{ asset('storage/'.$imgs[0]) }}" width="50" height="50" class="rounded" style="object-fit:cover">
                    @else<div class="avatar avatar-50 bg-primary-subtle rounded d-flex align-items-center justify-content-center"><span style="font-size:22px">🎂</span></div>@endif
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($item->is_veg)<span style="display:inline-block;width:14px;height:14px;border:1.5px solid #2D7A2D;border-radius:2px;background:transparent" title="Veg"><span style="display:block;margin:2px auto;width:8px;height:8px;border-radius:50%;background:#2D7A2D"></span></span>
                        @else<span style="display:inline-block;width:14px;height:14px;border:1.5px solid #D32F2F;border-radius:2px" title="Non-Veg"><span style="display:block;margin:2px auto;width:8px;height:8px;border-radius:50%;background:#D32F2F"></span></span>@endif
                        <strong>{{ $item->item_name }}</strong>
                    </div>
                </td>
                <td>{{ $item->owner?->restaurant_name ?? '—' }}</td>
                <td>{{ $item->category?->category_name ?? '—' }}</td>
                <td>
                    @foreach($item->variants->take(2) as $v)
                    <span class="badge bg-light text-dark me-1">{{ $v->label }}: ₹{{ $v->offer_price ?: $v->price }}</span>
                    @endforeach
                    @if($item->variants->count() > 2)<span class="badge bg-secondary">+{{ $item->variants->count()-2 }}</span>@endif
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.items.toggle',$item->id) }}" class="d-inline">@csrf
                        <button class="badge border-0 bg-{{ $item->status==='active'?'success':'secondary' }}">{{ ucfirst($item->status) }}</button>
                    </form>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.items.edit',$item->id) }}" class="btn btn-sm btn-outline-primary py-0">Edit</a>
                        <form method="POST" action="{{ route('admin.items.destroy',$item->id) }}" class="d-inline" onsubmit="return confirm('Delete item?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No items found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $items->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
