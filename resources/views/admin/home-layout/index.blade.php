<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
/* ── Layout editor ─────────────────────────────────────────── */
.sections-scroll {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 8px;
    align-items: flex-start;
}
.section-col {
    flex-shrink: 0;
    width: 270px;
}
.section-col-inner {
    background: #f0fdf4;
    border: 2px solid #c3e6cb;
    border-radius: 16px;
    padding: 14px 12px 12px;
    min-height: 160px;
    transition: border-color .2s, background .2s;
}
.section-col-inner.drag-over { border-color: #22c55e; background: #dcfce7; }

.hidden-zone-wrap {
    background: #fafafa;
    border: 2px dashed #e2e2e2;
    border-radius: 16px;
    padding: 14px 12px 12px;
    min-height: 80px;
    transition: border-color .2s, background .2s;
}
.hidden-zone-wrap.drag-over { border-color: #94a3b8; background: #f1f5f9; }

.zone-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.zone-count {
    background: rgba(0,0,0,.07);
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 10px;
}

/* Section title input — looks like text until focused */
.section-title-input {
    background: transparent;
    border: none;
    border-bottom: 1.5px dashed transparent;
    outline: none;
    font-size: 13px;
    font-weight: 700;
    color: #166534;
    width: 160px;
    padding: 2px 4px;
    transition: border-color .15s;
}
.section-title-input:focus {
    border-bottom-color: #22c55e;
    background: rgba(255,255,255,.6);
    border-radius: 4px 4px 0 0;
}

/* Category cards */
.cat-card {
    background: #fff;
    border: 1.5px solid #e9ecef;
    border-radius: 14px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: grab;
    transition: box-shadow .15s, transform .15s;
    user-select: none;
    margin-bottom: 8px;
}
.cat-card:active { cursor: grabbing; }
.cat-card.dragging { opacity: .4; transform: scale(.97); }
.cat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.10); transform: translateY(-1px); }
.cat-card.in-section  { border-left: 3px solid #22c55e; }
.cat-card.hidden-card { border-left: 3px solid #d1d5db; }

.cat-img {
    width: 44px; height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
    flex-shrink: 0;
}
.cat-img-placeholder {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 2px solid #e9ecef;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: #94a3b8; font-size: 18px;
}
.drag-handle { color: #cbd5e1; cursor: grab; font-size: 14px; }

/* App preview */
.app-preview {
    background: linear-gradient(135deg, #1b4332, #2d6a4f);
    border-radius: 18px;
    padding: 14px 12px 10px;
}
.preview-section-label { font-size: 10px; color: rgba(255,255,255,.55); font-weight: 600; margin-bottom: 6px; }
.preview-chips { display: flex; gap: 8px; overflow: hidden; }
.preview-chip { text-align: center; flex-shrink: 0; }
.preview-chip img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,.3); display: block; margin: 0 auto 3px; }
.preview-chip-placeholder { width: 42px; height: 42px; border-radius: 50%; background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center; font-size: 16px; margin: 0 auto 3px; }
.preview-chip-name { font-size: 9px; color: rgba(255,255,255,.7); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 48px; }
.preview-section + .preview-section { margin-top: 12px; }

/* Add section button col */
.add-section-col {
    flex-shrink: 0;
    width: 120px;
    display: flex;
    align-items: flex-start;
    padding-top: 8px;
}
</style>
@endpush

<div class="content-inner container-fluid pb-5">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-0">Home Layout</h4>
            <p class="text-muted mb-0 small">Drag categories between sections. Click section titles to rename. <strong>Save Layout</strong> to apply.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span id="saveStatus" class="text-muted small d-none">
                <span class="spinner-border spinner-border-sm me-1"></span>Saving…
            </span>
            <button id="saveBtn" class="btn btn-success px-4 fw-bold" onclick="saveLayout()">
                <i class="fas fa-save me-2"></i>Save Layout
            </button>
        </div>
    </div>

    <div id="toastArea" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:260px"></div>

    {{-- App Preview --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="fw-semibold mb-1">Live App Preview</div>
                    <p class="text-muted small mb-0">How sections appear in the Sweetan app.</p>
                </div>
                <div class="col-md-9">
                    <div class="app-preview" id="appPreview">
                        @forelse($sections as $sec)
                        <div class="preview-section" data-section-id="{{ $sec->id }}">
                            <div class="preview-section-label">{{ $sec->title }}</div>
                            <div class="preview-chips">
                                @forelse($sec->categories as $cat)
                                <div class="preview-chip" data-id="{{ $cat->id }}">
                                    @if($cat->image)
                                        <img src="{{ asset($cat->image) }}" alt="">
                                    @else
                                        <div class="preview-chip-placeholder">🛍️</div>
                                    @endif
                                    <div class="preview-chip-name">{{ Str::limit($cat->category_name, 7) }}</div>
                                </div>
                                @empty
                                <div class="text-white-50 small">Empty</div>
                                @endforelse
                            </div>
                        </div>
                        @empty
                        <div class="text-white-50 small">No sections yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sections (scrollable) --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-semibold">Visible Sections</span>
                <button class="btn btn-outline-success btn-sm fw-bold" onclick="addSection()">
                    <i class="fas fa-plus me-1"></i>Add Section
                </button>
            </div>
            <div class="sections-scroll" id="sectionsRow">
                @forelse($sections as $sec)
                <div class="section-col" data-temp-id="s_{{ $sec->id }}" data-server-id="{{ $sec->id }}">
                    <div class="d-flex align-items-center justify-content-between mb-2 gap-1">
                        <input class="section-title-input" value="{{ $sec->title }}" placeholder="Section title" title="Click to rename">
                        <button class="btn btn-link text-danger p-0" style="font-size:13px" onclick="deleteSection(this)" title="Delete section">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="section-col-inner"
                         id="zone-s_{{ $sec->id }}"
                         ondragover="onDragOver(event,this)"
                         ondragleave="onDragLeave(this)"
                         ondrop="onDrop(event,'s_{{ $sec->id }}')">
                        @forelse($sec->categories as $cat)
                            @include('admin.home-layout._card', ['cat' => $cat, 'rowClass' => 'in-section'])
                        @empty
                        <div class="text-center text-muted py-3 empty-hint" style="font-size:12px">
                            <i class="fas fa-arrow-down d-block mb-1 opacity-25"></i>Drag here
                        </div>
                        @endforelse
                    </div>
                    <div class="text-muted mt-1" style="font-size:10px;text-align:right">
                        <span class="zone-count-label" id="count-s_{{ $sec->id }}">{{ $sec->categories->count() }}</span> categories
                    </div>
                </div>
                @empty
                <div class="text-muted small py-3">No sections yet. Click <strong>Add Section</strong> to create one.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Hidden zone --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="zone-label text-secondary mb-2">
                <i class="fas fa-eye-slash"></i> Hidden
                <span class="zone-count" id="count-hidden">{{ $hidden->count() }}</span>
            </div>
            <div class="hidden-zone-wrap"
                 id="zone-hidden"
                 ondragover="onDragOver(event,this)"
                 ondragleave="onDragLeave(this)"
                 ondrop="onDrop(event,'hidden')">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($hidden as $cat)
                        @include('admin.home-layout._card', ['cat' => $cat, 'rowClass' => 'hidden-card'])
                    @empty
                    <div class="text-muted small empty-hint"><i class="fas fa-eye-slash me-1"></i>Drag categories here to hide them from the app</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-3">
        <i class="fas fa-info-circle me-1"></i>
        Drag cards between sections or into Hidden. Reorder within a section by dragging up/down. Click a section title to rename it.
    </p>
</div>

@push('scripts')
<script>
let dragged        = null;
let newSectionIdx  = 0;
const deletedSectionIds = [];
const SAVE_URL = '{{ route("admin.home-layout.save") }}';
const CSRF     = '{{ csrf_token() }}';

// ── Drag Source ───────────────────────────────────────────────────────────────

function addDragListeners(card) {
    card.setAttribute('draggable', 'true');
    card.addEventListener('dragstart', e => {
        dragged = card;
        setTimeout(() => card.classList.add('dragging'), 0);
        e.dataTransfer.effectAllowed = 'move';
    });
    card.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('dragging');
        dragged = null;
        updatePreview();
        updateAllCounts();
    });
    card.addEventListener('dragover', e => {
        e.preventDefault();
        if (!dragged || dragged === card) return;
        const zone = card.closest('.section-col-inner, .hidden-zone-wrap');
        if (!zone) return;
        const rect = card.getBoundingClientRect();
        zone.insertBefore(dragged, e.clientY < rect.top + rect.height / 2 ? card : card.nextSibling);
    });
}

document.querySelectorAll('.cat-card').forEach(addDragListeners);

// ── Drop Zones ────────────────────────────────────────────────────────────────

function onDragOver(e, el) {
    e.preventDefault();
    el.classList.add('drag-over');
}
function onDragLeave(el) {
    el.classList.remove('drag-over');
}
function onDrop(e, zoneKey) {
    e.preventDefault();
    if (!dragged) return;

    let zone;
    if (zoneKey === 'hidden') {
        zone = document.getElementById('zone-hidden');
    } else {
        zone = document.getElementById('zone-' + zoneKey);
    }
    if (!zone) return;

    zone.classList.remove('drag-over');
    zone.querySelectorAll('.empty-hint').forEach(n => n.remove());

    if (zoneKey === 'hidden') {
        dragged.classList.remove('in-section');
        dragged.classList.add('hidden-card');
        // Hidden zone uses flex-wrap, just append
        zone.querySelector('.d-flex').appendChild(dragged);
    } else {
        dragged.classList.remove('hidden-card');
        dragged.classList.add('in-section');
        zone.appendChild(dragged);
    }

    updatePreview();
    updateAllCounts();
}

// ── Add / Delete Section ──────────────────────────────────────────────────────

function addSection() {
    const tempId = 'new_' + (++newSectionIdx);

    const col = document.createElement('div');
    col.className = 'section-col';
    col.dataset.tempId   = tempId;
    col.dataset.serverId = '';

    col.innerHTML = `
        <div class="d-flex align-items-center justify-content-between mb-2 gap-1">
            <input class="section-title-input" value="New Section" placeholder="Section title" title="Click to rename">
            <button class="btn btn-link text-danger p-0" style="font-size:13px" onclick="deleteSection(this)" title="Delete section">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="section-col-inner"
             id="zone-${tempId}"
             ondragover="onDragOver(event,this)"
             ondragleave="onDragLeave(this)"
             ondrop="onDrop(event,'${tempId}')">
            <div class="text-center text-muted py-3 empty-hint" style="font-size:12px">
                <i class="fas fa-arrow-down d-block mb-1 opacity-25"></i>Drag here
            </div>
        </div>
        <div class="text-muted mt-1" style="font-size:10px;text-align:right">
            <span class="zone-count-label" id="count-${tempId}">0</span> categories
        </div>
    `;
    document.getElementById('sectionsRow').appendChild(col);
    col.querySelector('.section-title-input').focus();
    col.querySelector('.section-title-input').select();
    updatePreview();
}

function deleteSection(btn) {
    const col      = btn.closest('.section-col');
    const serverId = col.dataset.serverId;
    const zone     = col.querySelector('.section-col-inner');
    const cards    = zone.querySelectorAll('.cat-card');

    // Move all cards to hidden
    if (cards.length) {
        const hiddenFlex = document.querySelector('#zone-hidden .d-flex');
        cards.forEach(c => {
            c.classList.remove('in-section');
            c.classList.add('hidden-card');
            hiddenFlex.appendChild(c);
        });
    }

    if (serverId) deletedSectionIds.push(parseInt(serverId));
    col.remove();
    updatePreview();
    updateAllCounts();
}

// ── Counts ────────────────────────────────────────────────────────────────────

function updateAllCounts() {
    document.querySelectorAll('.section-col').forEach(col => {
        const tempId = col.dataset.tempId;
        const zone   = document.getElementById('zone-' + tempId);
        const count  = zone ? zone.querySelectorAll('.cat-card').length : 0;
        const el     = document.getElementById('count-' + tempId);
        if (el) el.textContent = count;
    });

    const hiddenCount = document.querySelectorAll('#zone-hidden .cat-card').length;
    const hiddenEl = document.getElementById('count-hidden');
    if (hiddenEl) hiddenEl.textContent = hiddenCount;
}

// ── Preview ───────────────────────────────────────────────────────────────────

function updatePreview() {
    const preview = document.getElementById('appPreview');
    preview.innerHTML = '';

    document.querySelectorAll('.section-col').forEach(col => {
        const title  = col.querySelector('.section-title-input')?.value || 'Section';
        const tempId = col.dataset.tempId;
        const zone   = document.getElementById('zone-' + tempId);
        const cards  = zone ? zone.querySelectorAll('.cat-card') : [];

        const sec = document.createElement('div');
        sec.className = 'preview-section';

        const label = document.createElement('div');
        label.className = 'preview-section-label';
        label.textContent = title;
        sec.appendChild(label);

        const chips = document.createElement('div');
        chips.className = 'preview-chips';

        if (cards.length === 0) {
            chips.innerHTML = '<div class="text-white-50 small">Empty</div>';
        } else {
            cards.forEach(card => {
                const img  = card.dataset.img;
                const name = card.dataset.name;
                const chip = document.createElement('div');
                chip.className = 'preview-chip';
                chip.innerHTML = img
                    ? `<img src="${img}" alt=""><div class="preview-chip-name">${name.substring(0,7)}</div>`
                    : `<div class="preview-chip-placeholder">🛍️</div><div class="preview-chip-name">${name.substring(0,7)}</div>`;
                chips.appendChild(chip);
            });
        }
        sec.appendChild(chips);
        preview.appendChild(sec);
    });

    if (!preview.children.length) {
        preview.innerHTML = '<div class="text-white-50 small">No sections yet</div>';
    }
}

// Live preview update on title change
document.getElementById('sectionsRow').addEventListener('input', e => {
    if (e.target.classList.contains('section-title-input')) updatePreview();
});

// ── Save ──────────────────────────────────────────────────────────────────────

function saveLayout() {
    const sections   = [];
    const categories = [];
    let sectionOrder = 0;

    document.querySelectorAll('.section-col').forEach(col => {
        const tempId   = col.dataset.tempId;
        const serverId = col.dataset.serverId;
        const title    = col.querySelector('.section-title-input')?.value?.trim() || 'Section';
        const zone     = document.getElementById('zone-' + tempId);

        sections.push({
            temp_id:    tempId,
            server_id:  serverId ? parseInt(serverId) : null,
            title:      title,
            sort_order: sectionOrder++,
        });

        if (zone) {
            zone.querySelectorAll('.cat-card').forEach((card, idx) => {
                categories.push({
                    id:               parseInt(card.dataset.id),
                    section_temp_id:  tempId,
                    sort_order:       idx,
                });
            });
        }
    });

    document.querySelectorAll('#zone-hidden .cat-card').forEach(card => {
        categories.push({ id: parseInt(card.dataset.id), section_temp_id: null, sort_order: 0 });
    });

    document.getElementById('saveStatus').classList.remove('d-none');
    document.getElementById('saveBtn').disabled = true;

    fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body:    JSON.stringify({ sections, categories, deleted_section_ids: deletedSectionIds }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            // After save, update server IDs if new sections were created
            // (requires page reload for true sync — toast + reload)
            showToast('success', res.message || 'Saved!');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('danger', res.message || 'Error saving.');
        }
    })
    .catch(() => showToast('danger', 'Network error. Please try again.'))
    .finally(() => {
        document.getElementById('saveStatus').classList.add('d-none');
        document.getElementById('saveBtn').disabled = false;
    });
}

function showToast(type, msg) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} alert-dismissible fade show shadow`;
    t.style.cssText = 'border-radius:12px;font-size:13px;font-weight:600;';
    t.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.getElementById('toastArea').appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

// Init
updatePreview();
updateAllCounts();
</script>
@endpush
</x-app-layout>
