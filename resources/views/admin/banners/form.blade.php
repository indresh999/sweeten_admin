<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
.media-tab-btn { cursor:pointer; border:2px solid #dee2e6; border-radius:10px; padding:10px 18px; display:flex; align-items:center; gap:8px; transition:all .2s; background:#fff; }
.media-tab-btn.active { border-color:#0d6efd; background:#ebf3ff; color:#0d6efd; font-weight:600; }
.media-tab-btn:hover:not(.active) { border-color:#adb5bd; }
.media-preview-box { background:#f8f9fa; border:1.5px dashed #dee2e6; border-radius:12px; min-height:140px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; }
.media-preview-box img, .media-preview-box video { max-height:200px; width:100%; object-fit:cover; border-radius:10px; }
.upload-zone { border:2px dashed #dee2e6; border-radius:12px; padding:28px; text-align:center; cursor:pointer; transition:border-color .2s; }
.upload-zone:hover { border-color:#0d6efd; }
.upload-zone input[type=file] { display:none; }
.section-card { background:#fff; border:1px solid #e9ecef; border-radius:14px; padding:20px 22px; margin-bottom:20px; }
.section-card .card-head { font-size:13px; font-weight:700; color:#495057; text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid #f1f3f5; }
</style>
@endpush

<div class="content-inner container-fluid pb-5">
    {{-- Breadcrumb --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Banners
        </a>
        <div>
            <h4 class="fw-bold mb-0">{{ isset($banner) ? 'Edit Banner' : 'New Banner' }}</h4>
            <p class="text-muted mb-0 small">{{ isset($banner) ? 'Update banner settings & media' : 'Upload media and configure banner' }}</p>
        </div>
    </div>

    <form method="POST"
          action="{{ isset($banner) ? route('admin.banners.update', $banner->id) : route('admin.banners.store') }}"
          enctype="multipart/form-data"
          id="bannerForm">
        @csrf
        @if(isset($banner)) @method('PUT') @endif

        @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="row g-4">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">

                {{-- Media Type Selector --}}
                <div class="section-card">
                    <div class="card-head">📎 Media</div>
                    <div class="d-flex gap-2 mb-4 flex-wrap" id="mediaTypeTabs">
                        @foreach(['image' => ['🖼️','Image (JPG/PNG/WebP)'], 'gif' => ['🎞️','Animated GIF'], 'video' => ['🎬','Video (MP4)']] as $mt => [$icon, $label])
                        <div class="media-tab-btn {{ old('media_type', $banner->media_type ?? 'image') == $mt ? 'active' : '' }}"
                             data-type="{{ $mt }}" onclick="switchMedia('{{ $mt }}')">
                            <span>{{ $icon }}</span><span>{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="media_type" id="mediaTypeInput"
                           value="{{ old('media_type', $banner->media_type ?? 'image') }}">

                    {{-- Current media preview (edit mode) --}}
                    @if(isset($banner) && $banner->media_url)
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Current Media</label>
                        <div class="media-preview-box" style="max-width:360px">
                            @if($banner->media_type === 'video')
                                <video src="{{ $banner->media_url }}" controls muted style="max-height:160px;width:100%"></video>
                            @else
                                <img src="{{ $banner->media_url }}" alt="current" style="max-height:140px;object-fit:cover;width:100%;border-radius:10px">
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Upload Zone --}}
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('mediaFile').click()">
                        <input type="file" id="mediaFile" name="media"
                               accept="image/*,video/*,.gif"
                               {{ !isset($banner) ? 'required' : '' }}
                               onchange="previewMedia(this)">
                        <div id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <div class="fw-semibold">Click to upload or drag & drop</div>
                            <div class="text-muted small" id="uploadHint">Images (JPG/PNG/WebP), GIFs, or Videos (MP4) — max 50 MB — auto-detects type</div>
                        </div>
                        <div id="uploadPreview" class="d-none w-100">
                            <img id="imgPreview" class="d-none" style="max-height:180px;width:100%;object-fit:cover;border-radius:10px" alt="">
                            <video id="vidPreview" class="d-none" muted autoplay loop style="max-height:180px;width:100%"></video>
                            <p class="small text-muted mt-2 mb-0" id="fileName"></p>
                        </div>
                    </div>

                    {{-- Video thumbnail --}}
                    <div id="thumbSection" class="{{ old('media_type', $banner->media_type ?? 'image') === 'video' ? '' : 'd-none' }} mt-3">
                        <label class="form-label fw-semibold">Poster / Thumbnail <span class="text-muted fw-normal">(shown while video loads)</span></label>
                        @if(isset($banner) && $banner->thumbnail_url)
                        <div class="mb-2"><img src="{{ $banner->thumbnail_url }}" height="60" class="rounded"></div>
                        @endif
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                </div>

                {{-- Display Text --}}
                <div class="section-card">
                    <div class="card-head">✏️ Display Text</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Heading <span class="text-muted fw-normal">(large bold text)</span></label>
                            <input type="text" name="heading" class="form-control"
                                   value="{{ old('heading', $banner->heading ?? '') }}"
                                   placeholder="e.g. 30 Min Delivery!">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Title / Tag <span class="text-muted fw-normal">(small chip)</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ old('title', $banner->title ?? '') }}"
                                   placeholder="e.g. Flash Sale">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Subtitle</label>
                            <input type="text" name="subtitle" class="form-control"
                                   value="{{ old('subtitle', $banner->subtitle ?? '') }}"
                                   placeholder="e.g. Fresh cakes, balloons & more">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">CTA Button Label</label>
                            <input type="text" name="cta_label" class="form-control"
                                   value="{{ old('cta_label', $banner->cta_label ?? '') }}"
                                   placeholder="e.g. Order Now">
                        </div>
                    </div>
                </div>

                {{-- Target / Deep Link --}}
                <div class="section-card">
                    <div class="card-head">🔗 Tap Action</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Action Type</label>
                            <select name="target_type" class="form-select" onchange="toggleTargetFields(this.value)">
                                @foreach(['none' => 'No action', 'url' => 'Open URL', 'shop' => 'Open Shop', 'category' => 'Open Category', 'item' => 'Open Item'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('target_type', $banner->target_type ?? 'none') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" id="targetIdField" style="{{ in_array(old('target_type', $banner->target_type ?? 'none'), ['shop','category','item']) ? '' : 'display:none' }}">
                            <label class="form-label fw-semibold">ID</label>
                            <input type="number" name="target_id" class="form-control"
                                   value="{{ old('target_id', $banner->target_id ?? '') }}"
                                   placeholder="Shop / Category / Item ID">
                        </div>
                        <div class="col-md-8" id="targetUrlField" style="{{ old('target_type', $banner->target_type ?? 'none') === 'url' ? '' : 'display:none' }}">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="url" name="target_url" class="form-control"
                                   value="{{ old('target_url', $banner->target_url ?? '') }}"
                                   placeholder="https://example.com">
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-4">

                {{-- Settings --}}
                <div class="section-card">
                    <div class="card-head">⚙️ Settings</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Banner Type</label>
                        <select name="banner_type" class="form-select" required>
                            @foreach(['hero' => 'Hero (Main Carousel)', 'strip' => 'Strip Banner', 'popup' => 'Pop-up', 'deals' => 'Deals Section', 'category' => 'Category Banner'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('banner_type', $banner->banner_type ?? 'hero') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ old('status', $banner->status ?? 'active') === 'active'   ? 'selected' : '' }}>🟢 Active</option>
                            <option value="inactive" {{ old('status', $banner->status ?? '') === 'inactive' ? 'selected' : '' }}>⚫ Inactive</option>
                            <option value="draft"    {{ old('status', $banner->status ?? '') === 'draft'    ? 'selected' : '' }}>🟡 Draft</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order <span class="text-muted fw-normal">(lower = first)</span></label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0" max="999">
                    </div>
                    <div class="form-check form-switch pt-1">
                        <input class="form-check-input" type="checkbox" id="sponsoredCheck"
                               name="is_sponsored" value="1"
                               {{ old('is_sponsored', $banner->is_sponsored ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="sponsoredCheck">Mark as Sponsored</label>
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="section-card">
                    <div class="card-head">📅 Schedule</div>
                    <p class="text-muted small mb-3">Leave blank to show always.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control"
                               value="{{ old('start_date', isset($banner->start_date) ? $banner->start_date->format('Y-m-d') : '') }}">
                    </div>
                    <div>
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control"
                               value="{{ old('end_date', isset($banner->end_date) ? $banner->end_date->format('Y-m-d') : '') }}">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">
                        <i class="fas fa-{{ isset($banner) ? 'save' : 'plus' }} me-2"></i>
                        {{ isset($banner) ? 'Update Banner' : 'Create Banner' }}
                    </button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function switchMedia(type) {
    document.getElementById('mediaTypeInput').value = type;
    document.querySelectorAll('.media-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.type === type));
    const hints = {image:'JPG, PNG, WebP — max 50 MB', gif:'Animated GIF — max 50 MB', video:'MP4, MOV — max 50 MB (thumbnail recommended)'};
    document.getElementById('uploadHint').textContent = hints[type] || hints.image;
    const accepts = {image:'image/jpeg,image/png,image/webp', gif:'image/gif', video:'video/mp4,video/quicktime,video/*'};
    document.getElementById('mediaFile').accept = accepts[type] || 'image/*';
    document.getElementById('thumbSection').classList.toggle('d-none', type !== 'video');
    // reset preview
    resetPreview();
}

function resetPreview() {
    document.getElementById('uploadPreview').classList.add('d-none');
    document.getElementById('uploadPlaceholder').classList.remove('d-none');
    document.getElementById('imgPreview').classList.add('d-none');
    document.getElementById('vidPreview').classList.add('d-none');
}

function previewMedia(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const isVideo = file.type.startsWith('video/');
    const isGif = file.type === 'image/gif';

    // Auto-detect media type from file
    if (isVideo) {
        switchMedia('video');
    } else if (isGif) {
        switchMedia('gif');
    } else {
        switchMedia('image');
    }

    document.getElementById('uploadPlaceholder').classList.add('d-none');
    document.getElementById('uploadPreview').classList.remove('d-none');
    document.getElementById('fileName').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
    if (isVideo) {
        const vid = document.getElementById('vidPreview');
        vid.src = URL.createObjectURL(file);
        vid.classList.remove('d-none');
        document.getElementById('imgPreview').classList.add('d-none');
    } else {
        const img = document.getElementById('imgPreview');
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
        document.getElementById('vidPreview').classList.add('d-none');
    }
}

function toggleTargetFields(val) {
    document.getElementById('targetIdField').style.display = ['shop','category','item'].includes(val) ? '' : 'none';
    document.getElementById('targetUrlField').style.display = val === 'url' ? '' : 'none';
}

// Init accept on load
switchMedia(document.getElementById('mediaTypeInput').value);
</script>
@endpush
</x-app-layout>
