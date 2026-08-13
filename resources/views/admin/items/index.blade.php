
<x-app-layout :assets="$assets ?? []">
<style>
    .item-card{background:#fff;border:1px solid #E8F5E9;border-radius:14px;transition:all .2s;overflow:hidden}
    .item-card:hover{border-color:#95D5B2;box-shadow:0 4px 16px rgba(27,67,50,0.1);transform:translateY(-1px)}
    .item-thumb{width:64px;height:64px;border-radius:12px;object-fit:cover;border:2px solid #E8F5E9;flex-shrink:0}
    .item-thumb-placeholder{width:64px;height:64px;border-radius:12px;background:linear-gradient(135deg,#EBFAF0,#D8F3DC);display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;border:2px solid #E8F5E9}
    .veg-dot{display:inline-block;width:16px;height:16px;border-radius:3px;border:2px solid;position:relative;flex-shrink:0}
    .veg-dot.veg{border-color:#2D7A2D}.veg-dot.veg::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:8px;height:8px;border-radius:50%;background:#2D7A2D}
    .veg-dot.nonveg{border-color:#D32F2F}.veg-dot.nonveg::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:8px;height:8px;border-radius:50%;background:#D32F2F}
    .variant-badge{background:#F1F8E9;border:1px solid #C5E1A5;border-radius:6px;padding:2px 8px;font-size:11px;color:#33691E;font-weight:500;display:inline-block;margin:1px 2px}
    .status-active{background:#E8F5E9;color:#2E7D32;border:1px solid #A5D6A7;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600;cursor:pointer;border:0}
    .status-inactive{background:#F5F5F5;color:#757575;border:1px solid #E0E0E0;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600;cursor:pointer;border:0}
    .filter-card{background:#fff;border:1px solid #E8F5E9;border-radius:14px;padding:16px;margin-bottom:16px}
    .filter-card .form-control,.filter-card .form-select{border-radius:10px;border-color:#C8E4D2;font-size:13px;padding:8px 12px}
    .filter-card .form-control:focus,.filter-card .form-select:focus{border-color:#2D6A4F;box-shadow:0 0 0 3px rgba(45,106,79,0.1)}
    .btn-create{background:linear-gradient(135deg,#40916C,#2D6A4F);border:none;color:#fff;font-weight:700;padding:10px 20px;border-radius:10px;font-size:13px;transition:all .2s}
    .btn-create:hover{background:linear-gradient(135deg,#2D6A4F,#1B4332);color:#fff;transform:translateY(-1px);box-shadow:0 4px 12px rgba(27,67,50,0.25)}
    .item-count{background:#2D6A4F;color:#fff;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700}
    .action-btn{border-radius:8px;font-size:11px;font-weight:600;padding:4px 10px;transition:all .15s}
    .action-btn:hover{transform:scale(1.05)}
    .lightgallery img{cursor:zoom-in}
</style>

<div class="content-inner container-fluid pb-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="fw-bold mb-0">Items</h4>
            <span class="item-count">{{ $items->total() }} total</span>
        </div>
        <a href="{{ route('admin.items.create') }}" class="btn btn-create"><i class="fas fa-plus me-1"></i>Add Item</a>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:12px;color:#3A6B50">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Item name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:12px;color:#3A6B50">Vendor</label>
                <select name="shop_id" class="form-select">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)<option value="{{ $v->shop_id }}" {{ request('shop_id')===$v->shop_id?'selected':'' }}>{{ $v->restaurant_name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:12px;color:#3A6B50">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id')===$c->id?'selected':'' }}>{{ $c->category_name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:12px;color:#3A6B50">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm" style="background:#2D6A4F;color:#fff;border-radius:8px"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            @if(request()->hasAny(['search','shop_id','category_id','status']))
            <div class="col-auto"><a href="{{ route('admin.items.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">Clear</a></div>
            @endif
        </form>
    </div>

    {{-- Items Grid --}}
    <div class="row g-3">
        @forelse($items as $item)
        <div class="col-12">
            @php
                $imgs = is_array($item->images) ? $item->images : json_decode($item->images ?? '[]', true);
                $firstImg = $imgs[0] ?? null;
                if ($firstImg && !str_starts_with($firstImg, 'http')) {
                    $firstImg = asset('storage/' . $firstImg);
                } elseif ($firstImg) {
                    $firstImg = $firstImg;
                }
            @endphp
            <div class="item-card p-3">
                <div class="d-flex align-items-center gap-3">
                    {{-- Thumb --}}
                    @if($firstImg)
                        <img src="{{ $firstImg }}" class="item-thumb" alt="{{ $item->item_name }}" loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                             data-src="{{ $firstImg }}" onclick="openLightbox('{{ $firstImg }}')">
                        <div class="item-thumb-placeholder" style="display:none" onclick="openLightbox('{{ $firstImg }}')">
                            @if($item->is_veg)<i class="fas fa-leaf" style="color:#2D7A2D"></i>@else<i class="fas fa-drumstick-bite" style="color:#D32F2F"></i>@endif
                        </div>
                    @else
                        <div class="item-thumb-placeholder">
                            @if($item->is_veg)<i class="fas fa-leaf" style="color:#2D7A2D"></i>@else<i class="fas fa-drumstick-bite" style="color:#D32F2F"></i>@endif
                        </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="veg-dot {{ $item->is_veg ? 'veg' : 'nonveg' }}"></span>
                            <strong class="text-truncate" style="font-size:15px;color:#1B4332">{{ $item->item_name }}</strong>
                            @if($item->is_featured)<span style="background:#FFF8E1;border:1px solid #FFE082;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700;color:#F57F17">★ Featured</span>@endif
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1" style="font-size:12px;color:#7AAD90">
                            <span><i class="fas fa-store me-1"></i>{{ $item->owner?->restaurant_name ?? '—' }}</span>
                            <span>·</span>
                            <span><i class="fas fa-tag me-1"></i>{{ $item->category?->category_name ?? '—' }}</span>
                            @if($item->subcategory)<span>·</span><span>{{ $item->subcategory->name }}</span>@endif
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($item->variants as $v)
                            <span class="variant-badge">{{ $v->label }}: ₹{{ $v->offer_price ?: $v->price }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="text-end" style="min-width:90px">
                        @php $def = $item->variants->first(); @endphp
                        @if($def)
                        <div style="font-size:18px;font-weight:800;color:#1B4332">₹{{ ($def->offer_price ?? $def->price) }}</div>
                        @if($def->offer_price && $def->offer_price < $def->price)
                        <div style="font-size:11px;color:#7AAD90;text-decoration:line-through">₹{{ $def->price }}</div>
                        @endif
                        @endif
                    </div>

                    {{-- Status --}}
                    <div style="min-width:80px">
                        <form method="POST" action="{{ route('admin.items.toggle',$item->id) }}" class="d-inline">@csrf
                            <button class="{{ $item->status==='active'?'status-active':'status-inactive' }}">{{ ucfirst($item->status) }}</button>
                        </form>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-1" style="min-width:100px">
                        <a href="{{ route('admin.items.edit',$item->id) }}" class="btn action-btn" style="background:#E3F2FD;color:#1565C0"><i class="fas fa-pen me-1"></i>Edit</a>
                        <form method="POST" action="{{ route('admin.items.destroy',$item->id) }}" class="d-inline" onsubmit="return confirm('Delete this item permanently?')">@csrf @method('DELETE')
                            <button class="btn action-btn" style="background:#FFEBEE;color:#C62828"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x mb-3" style="color:#C8E4D2"></i>
                    <h5 class="fw-bold" style="color:#3A6B50">No items found</h5>
                    <p class="text-muted mb-3">Try adjusting your filters or add a new item</p>
                    <a href="{{ route('admin.items.create') }}" class="btn btn-create"><i class="fas fa-plus me-1"></i>Add First Item</a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="mt-3">{{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Lightbox --}}
<div id="lightbox" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.9);cursor:zoom-out;justify-content:center;align-items:center" onclick="closeLightbox()">
    <button style="position:absolute;top:16px;right:16px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;z-index:10000" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" style="max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.5)" onclick="event.stopPropagation()">
</div>
<script>
function openLightbox(src){
    document.getElementById('lightboxImg').src=src;
    document.getElementById('lightbox').style.display='flex';
    document.body.style.overflow='hidden';
}
function closeLightbox(){
    document.getElementById('lightbox').style.display='none';
    document.body.style.overflow='';
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeLightbox();});
</script>
</x-app-layout>
