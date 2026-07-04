<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
.shop-card {
    background:#fff; border:1.5px solid #e9ecef; border-radius:14px;
    padding:10px 12px; display:flex; align-items:center; gap:10px;
    margin-bottom:8px; transition:box-shadow .15s;
}
.shop-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.09); }
.shop-thumb { width:46px;height:46px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1.5px solid #e9ecef; }
.shop-thumb-placeholder { width:46px;height:46px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.drag-handle { color:#cbd5e1;cursor:grab;font-size:14px; }
.area-zone { background:#f8f9fa;border:2px dashed #dee2e6;border-radius:14px;padding:12px;min-height:80px; }
.area-zone.drag-over { border-color:#3b82f6;background:#eff8ff; }
.area-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6b7280;margin-bottom:8px; }
.popular-shop-card { border-left:3px solid #3b82f6; }
.featured-shop-card { border-left:3px solid #ffd60a; }
</style>
@endpush

<div class="content-inner container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-0">Shop Home Layout</h4>
            <p class="text-muted small mb-0">Configure which shops appear in Featured and Popular sections of the app.</p>
        </div>
    </div>

    <div id="toastArea" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:260px"></div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="shopTabs">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabFeatured">
                ⭐ Featured Shops
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabPopular">
                📍 Popular Shops
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── FEATURED TAB ──────────────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="tabFeatured">
            <div class="row g-4">
                {{-- Left: All active shops --}}
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <div class="fw-semibold mb-1">All Active Shops</div>
                            <p class="text-muted small mb-3">Drag into Featured to add, or drag back to remove.</p>
                            <input type="text" class="form-control form-control-sm mb-3" id="shopSearch" placeholder="Search shops…" oninput="filterShops(this.value)">
                            <div id="allShopsPool" class="area-zone" style="max-height:480px;overflow-y:auto"
                                 ondragover="onDragOver(event,this)" ondragleave="onDragLeave(this)" ondrop="onDropPool(event)">
                                @foreach($allActive as $s)
                                    @if(!$s->is_featured)
                                    <div class="shop-card" draggable="true" data-id="{{ $s->shop_id }}"
                                         data-name="{{ $s->restaurant_name }}" data-city="{{ $s->city }}">
                                        <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                        <div class="shop-thumb-placeholder">🏪</div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-truncate" style="font-size:13px">{{ $s->restaurant_name }}</div>
                                            <div class="text-muted" style="font-size:11px">{{ $s->city }}</div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Featured zone --}}
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-semibold">⭐ Featured Shops <span class="badge bg-warning text-dark ms-1" id="featuredCount">{{ $featured->count() }}</span></div>
                                <button class="btn btn-success btn-sm fw-bold px-3" onclick="saveFeatured()">
                                    <i class="fas fa-save me-1"></i>Save
                                </button>
                            </div>
                            <p class="text-muted small mb-3">Drag to reorder. These appear in the big carousel on the home screen.</p>
                            <div id="featuredZone" class="area-zone" style="min-height:300px"
                                 ondragover="onDragOver(event,this)" ondragleave="onDragLeave(this)" ondrop="onDropFeatured(event)">
                                @forelse($featured as $s)
                                <div class="shop-card featured-shop-card" draggable="true" data-id="{{ $s->shop_id }}"
                                     data-name="{{ $s->restaurant_name }}" data-city="{{ $s->city }}">
                                    <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                    @if($s->images->first())
                                        <img src="{{ $s->images->first()->url }}" class="shop-thumb" alt="">
                                    @else
                                        <div class="shop-thumb-placeholder">🏪</div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate" style="font-size:13px">{{ $s->restaurant_name }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $s->city }}</div>
                                    </div>
                                    <button class="btn btn-link text-danger p-0" onclick="removeFromFeatured(this)" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @empty
                                <div class="text-center text-muted py-4 empty-hint">
                                    <i class="fas fa-arrow-left d-block mb-1 opacity-25"></i>Drag shops here
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── POPULAR TAB ───────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tabPopular">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold mb-0">📍 Popular Shops by Location</div>
                            <p class="text-muted small mb-0">Assign shops to areas and drag to reorder within each area.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" id="newAreaInput" class="form-control form-control-sm" placeholder="New area name…" style="width:180px">
                            <button class="btn btn-outline-primary btn-sm" onclick="addArea()">+ Add Area</button>
                            <button class="btn btn-success btn-sm fw-bold px-3" onclick="savePopular()">
                                <i class="fas fa-save me-1"></i>Save
                            </button>
                        </div>
                    </div>

                    {{-- Area columns --}}
                    <div class="row g-3" id="areasRow">
                        @forelse($popularByArea as $area => $shops)
                        <div class="col-md-4 area-col" data-area="{{ $area }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <input class="form-control form-control-sm area-title-input fw-semibold" value="{{ $area }}" style="border:none;border-bottom:1.5px dashed #ccc;border-radius:0;background:transparent;font-size:13px;color:#1e4d38;width:160px">
                                <button class="btn btn-link text-danger p-0" style="font-size:13px" onclick="removeArea(this)" title="Remove area">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="area-zone popular-zone" id="pop-zone-{{ Str::slug($area) }}"
                                 ondragover="onDragOver(event,this)" ondragleave="onDragLeave(this)"
                                 ondrop="onDropPopular(event,'{{ $area }}')">
                                @foreach($shops as $s)
                                <div class="shop-card popular-shop-card" draggable="true" data-id="{{ $s->shop_id }}"
                                     data-name="{{ $s->restaurant_name }}" data-city="{{ $s->city }}">
                                    <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                    @if($s->images->first())
                                        <img src="{{ $s->images->first()->url }}" class="shop-thumb" alt="">
                                    @else
                                        <div class="shop-thumb-placeholder">🏪</div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate" style="font-size:12px">{{ $s->restaurant_name }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $s->city }}</div>
                                    </div>
                                    <button class="btn btn-link text-danger p-0" onclick="removeFromArea(this)" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-muted text-center py-3" id="noAreasHint">No areas yet. Add one above.</div>
                        @endforelse

                        {{-- Hidden pool for popular tab --}}
                        <div class="col-md-4">
                            <div class="area-label text-secondary">Unassigned Shops</div>
                            <div class="area-zone" id="pop-unassigned" style="max-height:400px;overflow-y:auto"
                                 ondragover="onDragOver(event,this)" ondragleave="onDragLeave(this)"
                                 ondrop="onDropUnassigned(event)">
                                @foreach($allActive as $s)
                                    @if(!$s->is_popular)
                                    <div class="shop-card" draggable="true" data-id="{{ $s->shop_id }}"
                                         data-name="{{ $s->restaurant_name }}" data-city="{{ $s->city }}">
                                        <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                        <div class="shop-thumb-placeholder">🏪</div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-truncate" style="font-size:12px">{{ $s->restaurant_name }}</div>
                                            <div class="text-muted" style="font-size:11px">{{ $s->city }}</div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>

@push('scripts')
<script>
let dragged = null;
const CSRF = '{{ csrf_token() }}';
const FEAT_SAVE_URL = '{{ route("admin.shop-home-layout.featured.save") }}';
const POP_SAVE_URL  = '{{ route("admin.shop-home-layout.popular.save") }}';
let newAreaIdx = 0;

// ── Generic drag helpers ─────────────────────────────────────────────────────

function setupDragListeners(card) {
    card.setAttribute('draggable', 'true');
    card.addEventListener('dragstart', e => {
        dragged = card;
        setTimeout(() => card.style.opacity = '.4', 0);
        e.dataTransfer.effectAllowed = 'move';
    });
    card.addEventListener('dragend', () => {
        if (dragged) dragged.style.opacity = '1';
        dragged = null;
        updateFeaturedCount();
    });
    card.addEventListener('dragover', e => {
        e.preventDefault();
        if (!dragged || dragged === card) return;
        const zone = card.closest('.area-zone');
        if (!zone) return;
        const rect = card.getBoundingClientRect();
        zone.insertBefore(dragged, e.clientY < rect.top + rect.height / 2 ? card : card.nextSibling);
    });
}
document.querySelectorAll('.shop-card').forEach(setupDragListeners);

function onDragOver(e, el) { e.preventDefault(); el.classList.add('drag-over'); }
function onDragLeave(el) { el.classList.remove('drag-over'); }

// ── Featured ─────────────────────────────────────────────────────────────────

function onDropFeatured(e) {
    e.preventDefault();
    const zone = document.getElementById('featuredZone');
    zone.classList.remove('drag-over');
    if (!dragged) return;
    zone.querySelectorAll('.empty-hint').forEach(n => n.remove());
    // Add remove button if coming from pool
    if (!dragged.querySelector('.fa-times')) {
        const btn = document.createElement('button');
        btn.className = 'btn btn-link text-danger p-0';
        btn.title = 'Remove';
        btn.innerHTML = '<i class="fas fa-times"></i>';
        btn.onclick = function() { removeFromFeatured(this); };
        dragged.appendChild(btn);
    }
    dragged.classList.add('featured-shop-card');
    zone.appendChild(dragged);
    updateFeaturedCount();
}

function onDropPool(e) {
    e.preventDefault();
    const pool = document.getElementById('allShopsPool');
    pool.classList.remove('drag-over');
    if (!dragged) return;
    // Remove the ×  button
    dragged.querySelectorAll('button').forEach(b => b.remove());
    dragged.classList.remove('featured-shop-card');
    pool.appendChild(dragged);
    updateFeaturedCount();
}

function removeFromFeatured(btn) {
    const card = btn.closest('.shop-card');
    btn.remove();
    card.classList.remove('featured-shop-card');
    document.getElementById('allShopsPool').appendChild(card);
    updateFeaturedCount();
}

function updateFeaturedCount() {
    const c = document.querySelectorAll('#featuredZone .shop-card').length;
    document.getElementById('featuredCount').textContent = c;
}

function filterShops(q) {
    document.querySelectorAll('#allShopsPool .shop-card').forEach(c => {
        const name = c.dataset.name.toLowerCase();
        c.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
    });
}

function saveFeatured() {
    const items = [];
    document.querySelectorAll('#featuredZone .shop-card').forEach((c, i) => {
        items.push({ id: parseInt(c.dataset.id), is_featured: true, sort_order: i });
    });
    document.querySelectorAll('#allShopsPool .shop-card').forEach(c => {
        items.push({ id: parseInt(c.dataset.id), is_featured: false, sort_order: 0 });
    });
    postSave(FEAT_SAVE_URL, { items });
}

// ── Popular ───────────────────────────────────────────────────────────────────

function addArea() {
    const input = document.getElementById('newAreaInput');
    const name  = input.value.trim();
    if (!name) return;
    input.value = '';

    document.getElementById('noAreasHint')?.remove();
    const row  = document.getElementById('areasRow');
    const slug = 'new_' + (++newAreaIdx);

    const col = document.createElement('div');
    col.className = 'col-md-4 area-col';
    col.dataset.area = name;
    col.innerHTML = `
        <div class="d-flex align-items-center justify-content-between mb-2">
            <input class="form-control form-control-sm area-title-input fw-semibold" value="${name}"
                style="border:none;border-bottom:1.5px dashed #ccc;border-radius:0;background:transparent;font-size:13px;color:#1e4d38;width:160px">
            <button class="btn btn-link text-danger p-0" style="font-size:13px" onclick="removeArea(this)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="area-zone popular-zone" id="pop-zone-${slug}"
             ondragover="onDragOver(event,this)" ondragleave="onDragLeave(this)"
             ondrop="onDropPopular(event,'${name}')">
            <div class="text-center text-muted py-3 empty-hint" style="font-size:12px">Drag shops here</div>
        </div>
    `;
    // Insert before the last col (unassigned)
    const cols = row.querySelectorAll('.col-md-4');
    const lastCol = cols[cols.length - 1];
    row.insertBefore(col, lastCol);
}

function removeArea(btn) {
    const col  = btn.closest('.area-col');
    const zone = col.querySelector('.area-zone');
    const unassigned = document.getElementById('pop-unassigned');
    zone.querySelectorAll('.shop-card').forEach(c => {
        c.querySelectorAll('button').forEach(b => b.remove());
        c.classList.remove('popular-shop-card');
        unassigned.appendChild(c);
    });
    col.remove();
}

function onDropPopular(e, areaName) {
    e.preventDefault();
    // Find closest zone
    const zone = e.currentTarget;
    zone.classList.remove('drag-over');
    if (!dragged) return;
    zone.querySelectorAll('.empty-hint').forEach(n => n.remove());
    // Add remove button
    if (!dragged.querySelector('.fa-times')) {
        const btn = document.createElement('button');
        btn.className = 'btn btn-link text-danger p-0';
        btn.innerHTML = '<i class="fas fa-times"></i>';
        btn.onclick = function() { removeFromArea(this); };
        dragged.appendChild(btn);
    }
    dragged.classList.add('popular-shop-card');
    zone.appendChild(dragged);
}

function onDropUnassigned(e) {
    e.preventDefault();
    const zone = document.getElementById('pop-unassigned');
    zone.classList.remove('drag-over');
    if (!dragged) return;
    dragged.querySelectorAll('button').forEach(b => b.remove());
    dragged.classList.remove('popular-shop-card');
    zone.appendChild(dragged);
}

function removeFromArea(btn) {
    const card = btn.closest('.shop-card');
    btn.remove();
    card.classList.remove('popular-shop-card');
    document.getElementById('pop-unassigned').appendChild(card);
}

function savePopular() {
    const items = [];
    document.querySelectorAll('.area-col').forEach(col => {
        const area = col.querySelector('.area-title-input')?.value?.trim() || col.dataset.area;
        col.querySelectorAll('.shop-card').forEach((c, i) => {
            items.push({ id: parseInt(c.dataset.id), is_popular: true, area, sort_order: i });
        });
    });
    document.querySelectorAll('#pop-unassigned .shop-card').forEach(c => {
        items.push({ id: parseInt(c.dataset.id), is_popular: false, area: null, sort_order: 0 });
    });
    postSave(POP_SAVE_URL, { items });
}

// ── Shared ────────────────────────────────────────────────────────────────────

function postSave(url, body) {
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(res => showToast(res.status ? 'success' : 'danger', res.message || 'Error'))
    .catch(() => showToast('danger', 'Network error. Please try again.'));
}

function showToast(type, msg) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} alert-dismissible fade show shadow`;
    t.style.cssText = 'border-radius:12px;font-size:13px;font-weight:600;';
    t.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.getElementById('toastArea').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>
@endpush
</x-app-layout>
