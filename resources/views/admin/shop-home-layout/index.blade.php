<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
.shop-card {
    background:#fff; border:1.5px solid #e9ecef; border-radius:14px;
    padding:10px 12px; display:flex; align-items:center; gap:10px;
    margin-bottom:8px; transition:box-shadow .15s, transform .15s, opacity .15s;
    cursor: grab;
}
.shop-card:active { cursor: grabbing; }
.shop-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.09); transform:translateY(-1px); }
.shop-thumb { width:46px;height:46px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1.5px solid #e9ecef; }
.shop-thumb-placeholder { width:46px;height:46px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.drag-handle { color:#cbd5e1;cursor:grab;font-size:14px;transition:color .15s; }
.shop-card:hover .drag-handle { color:#3b82f6; }

/* SortableJS feedback */
.shop-card.sortable-ghost { opacity:.35;transform:scale(.96);box-shadow:0 0 0 2px #3b82f6; }
.shop-card.sortable-chosen { box-shadow:0 8px 25px rgba(0,0,0,.18);transform:scale(1.02);z-index:10; }
.shop-card.sortable-drag { opacity:.9;box-shadow:0 12px 35px rgba(0,0,0,.22); }

.area-zone {
    background:#f8f9fa;border:2px dashed #dee2e6;border-radius:14px;padding:12px;min-height:80px;
    transition:border-color .2s,background .2s;
}
.area-zone.sortable-over { border-color:#3b82f6;background:#eff8ff; }
.area-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6b7280;margin-bottom:8px; }
.popular-shop-card { border-left:3px solid #3b82f6; }
.featured-shop-card { border-left:3px solid #ffd60a; }

.empty-hint { pointer-events:none; }
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
                            <div id="allShopsPool" class="area-zone" style="max-height:480px;overflow-y:auto">
                                @foreach($allActive as $s)
                                    @if(!$s->is_featured)
                                    <div class="shop-card" data-id="{{ $s->shop_id }}"
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
                            <div id="featuredZone" class="area-zone" style="min-height:300px">
                                @forelse($featured as $s)
                                <div class="shop-card featured-shop-card" data-id="{{ $s->shop_id }}"
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
                            <div class="area-zone popular-zone" id="pop-zone-{{ Str::slug($area) }}">
                                @foreach($shops as $s)
                                <div class="shop-card popular-shop-card" data-id="{{ $s->shop_id }}"
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
                            <div class="area-zone" id="pop-unassigned" style="max-height:400px;overflow-y:auto">
                                @foreach($allActive as $s)
                                    @if(!$s->is_popular)
                                    <div class="shop-card" data-id="{{ $s->shop_id }}"
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
<script src="{{ asset('vendor/sortable/Sortable.min.js') }}"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const FEAT_SAVE_URL = '{{ route("admin.shop-home-layout.featured.save") }}';
const POP_SAVE_URL  = '{{ route("admin.shop-home-layout.popular.save") }}';
let newAreaIdx = 0;

// ── Featured SortableJS ──────────────────────────────────────────────────────

function initFeaturedSortables() {
    const pool = document.getElementById('allShopsPool');
    const featured = document.getElementById('featuredZone');

    if (pool && !pool._sortable) {
        pool._sortable = Sortable.create(pool, {
            group: { name: 'featured-shops', pull: false, put: true },
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            fallbackTolerance: 3,
            onAdd: (evt) => {
                const card = evt.item;
                // Remove × button when moving back to pool
                card.querySelectorAll('button').forEach(b => b.remove());
                card.classList.remove('featured-shop-card');
                cleanEmptyHints(pool);
                updateFeaturedCount();
            },
        });
    }

    if (featured && !featured._sortable) {
        featured._sortable = Sortable.create(featured, {
            group: { name: 'featured-shops', pull: true, put: true },
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            fallbackTolerance: 3,
            onAdd: (evt) => {
                const card = evt.item;
                // Add × button if coming from pool
                if (!card.querySelector('.fa-times')) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-link text-danger p-0';
                    btn.title = 'Remove';
                    btn.innerHTML = '<i class="fas fa-times"></i>';
                    btn.onclick = function() { removeFromFeatured(this); };
                    card.appendChild(btn);
                }
                card.classList.add('featured-shop-card');
                cleanEmptyHints(featured);
                updateFeaturedCount();
            },
            onRemove: () => {
                cleanEmptyHints(featured);
                updateFeaturedCount();
            },
        });
    }
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
    const query = q.toLowerCase();
    document.querySelectorAll('#allShopsPool .shop-card').forEach(c => {
        const name = c.dataset.name.toLowerCase();
        const city = c.dataset.city.toLowerCase();
        c.style.display = (name.includes(query) || city.includes(query)) ? '' : 'none';
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

// ── Popular SortableJS ───────────────────────────────────────────────────────

function initPopularSortables() {
    // Each popular zone and the unassigned zone share a group
    document.querySelectorAll('.popular-zone, #pop-unassigned').forEach(zone => {
        if (zone._sortable) return;
        zone._sortable = Sortable.create(zone, {
            group: { name: 'popular-shops', pull: true, put: true },
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            fallbackTolerance: 3,
            onAdd: (evt) => {
                const card = evt.item;
                const toZone = evt.to;
                const isUnassigned = toZone.id === 'pop-unassigned';

                if (isUnassigned) {
                    // Moving back to unassigned
                    card.querySelectorAll('button').forEach(b => b.remove());
                    card.classList.remove('popular-shop-card');
                } else {
                    // Moving to an area zone
                    if (!card.querySelector('.fa-times')) {
                        const btn = document.createElement('button');
                        btn.className = 'btn btn-link text-danger p-0';
                        btn.innerHTML = '<i class="fas fa-times"></i>';
                        btn.onclick = function() { removeFromArea(this); };
                        card.appendChild(btn);
                    }
                    card.classList.add('popular-shop-card');
                }
                cleanEmptyHints(toZone);
            },
        });
    });
}

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
        <div class="area-zone popular-zone" id="pop-zone-${slug}"></div>
    `;

    // Insert before the last col (unassigned)
    const cols = row.querySelectorAll('.col-md-4');
    const lastCol = cols[cols.length - 1];
    row.insertBefore(col, lastCol);

    // Initialize Sortable on the new zone
    const zone = col.querySelector('.popular-zone');
    zone._sortable = Sortable.create(zone, {
        group: { name: 'popular-shops', pull: true, put: true },
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        handle: '.drag-handle',
        fallbackTolerance: 3,
        onAdd: (evt) => {
            const card = evt.item;
            if (!card.querySelector('.fa-times')) {
                const btn = document.createElement('button');
                btn.className = 'btn btn-link text-danger p-0';
                btn.innerHTML = '<i class="fas fa-times"></i>';
                btn.onclick = function() { removeFromArea(this); };
                card.appendChild(btn);
            }
            card.classList.add('popular-shop-card');
            cleanEmptyHints(zone);
        },
    });
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
    if (zone._sortable) { zone._sortable.destroy(); zone._sortable = null; }
    col.remove();
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

function cleanEmptyHints(container) {
    if (!container) return;
    container.querySelectorAll('.empty-hint').forEach(h => {
        if (container.querySelectorAll('.shop-card').length > 0) h.remove();
    });
}

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

// ── Init ──────────────────────────────────────────────────────────────────────
initFeaturedSortables();
initPopularSortables();
</script>
@endpush
</x-app-layout>
