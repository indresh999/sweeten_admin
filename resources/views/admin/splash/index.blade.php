<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Splash Screen</h4>
            <p class="text-muted mb-0">Configure the app launch screen — image or video background with overlay text.</p>
        </div>
        <div>
            <span class="badge fs-6 bg-{{ $splash['status'] === '1' ? 'success' : 'secondary' }} px-3 py-2">
                {{ $splash['status'] === '1' ? 'Active' : 'Disabled' }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">

        {{-- LEFT: Media Upload --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0">Splash Media</h6>
                    <small class="text-muted">Upload an image or video (max 50 MB). Recommended: 1080×1920 (portrait).</small>
                </div>
                <div class="card-body">

                    {{-- Current Preview --}}
                    @if($splash['media_path'])
                    <div class="mb-3 position-relative" id="currentPreview">
                        <p class="small fw-semibold text-muted mb-1">Current Media <span class="badge bg-{{ $splash['media_type'] === 'video' ? 'info' : 'primary' }}">{{ ucfirst($splash['media_type']) }}</span></p>
                        @if($splash['media_type'] === 'video')
                            <video class="rounded w-100" style="max-height:260px;object-fit:cover;background:#000"
                                controls muted loop autoplay playsinline>
                                <source src="{{ route('admin.splash.stream') }}" type="video/mp4">
                            </video>
                        @else
                            <img src="{{ asset('storage/' . $splash['media_path']) }}"
                                class="rounded w-100" style="max-height:260px;object-fit:cover" alt="Splash">
                        @endif
                        <form method="POST" action="{{ route('admin.splash.remove') }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Remove splash media?')">
                                <i class="fas fa-trash me-1"></i> Remove Media
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="rounded border border-dashed d-flex align-items-center justify-content-center text-muted mb-3" style="height:200px;border-style:dashed!important">
                        <div class="text-center">
                            <i class="fas fa-photo-video fs-2 mb-2 d-block"></i>
                            <span class="small">No media uploaded</span>
                        </div>
                    </div>
                    @endif

                    {{-- Upload Form --}}
                    <form method="POST" action="{{ route('admin.splash.media') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label fw-semibold small">Upload New Media</label>
                        <input type="file" name="media" id="mediaFile" class="form-control form-control-sm mb-1"
                            accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                            onchange="previewSplashMedia(this)">
                        <p class="text-muted small mb-3">Accepted: JPG, PNG, WEBP, GIF, MP4, MOV, WEBM</p>

                        {{-- Inline preview before upload --}}
                        <div id="newPreviewWrap" class="mb-3 d-none">
                            <p class="small fw-semibold text-muted mb-1">Preview</p>
                            <img id="newImgPreview" class="rounded w-100 d-none" style="max-height:220px;object-fit:cover" alt="">
                            <video id="newVidPreview" class="rounded w-100 d-none" style="max-height:220px;object-fit:cover;background:#000" muted autoplay loop controls></video>
                        </div>

                        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-upload me-1"></i> Upload & Save</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT: Settings + Live Preview --}}
        <div class="col-md-7">

            {{-- Settings Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0">Display Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.splash.update') }}">
                        @csrf
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold small">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="status" id="splashStatus" value="1" {{ $splash['status'] === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="splashStatus">Enable Splash Screen</label>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold small">Allow Skip</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="skip_enabled" id="splashSkip" value="1" {{ $splash['skip_enabled'] !== '0' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="splashSkip">Show Skip Button</label>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold small">Duration (seconds)</label>
                                <input type="number" name="duration" class="form-control form-control-sm"
                                    value="{{ $splash['duration'] }}" min="1" max="30"
                                    oninput="updatePreviewDuration(this.value)">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold small">Overlay Opacity (%)</label>
                                <input type="range" name="overlay_opacity" class="form-range mt-1"
                                    min="0" max="90" step="5"
                                    value="{{ $splash['overlay_opacity'] }}"
                                    oninput="updateOverlay(this.value)">
                                <small class="text-muted" id="opacityLabel">{{ $splash['overlay_opacity'] }}%</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Title Text</label>
                                <input type="text" name="title" class="form-control form-control-sm"
                                    value="{{ $splash['title'] }}" placeholder="e.g. Welcome to Sweetan"
                                    oninput="document.getElementById('previewTitle').textContent=this.value">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Subtitle Text</label>
                                <input type="text" name="subtitle" class="form-control form-control-sm"
                                    value="{{ $splash['subtitle'] }}" placeholder="e.g. Fresh groceries, delivered fast"
                                    oninput="document.getElementById('previewSubtitle').textContent=this.value">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold small">Text Color</label>
                                <input type="color" name="title_color" class="form-control form-control-color form-control-sm"
                                    value="{{ $splash['title_color'] }}"
                                    oninput="document.getElementById('previewTitle').style.color=this.value;document.getElementById('previewSubtitle').style.color=this.value">
                            </div>

                        </div>

                        <hr class="my-3">
                        <button class="btn btn-primary btn-sm px-4"><i class="fas fa-save me-1"></i> Save Settings</button>
                    </form>
                </div>
            </div>

            {{-- Live Preview Phone Mockup --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-bold mb-0">Live Preview</h6>
                    <small class="text-muted">Approximate look on a mobile device</small>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div id="phoneMockup" style="
                        width:200px; height:360px; border-radius:28px;
                        border:4px solid #333; position:relative; overflow:hidden;
                        background:#111; box-shadow:0 8px 32px rgba(0,0,0,0.35)">

                        {{-- media layer --}}
                        @if($splash['media_path'] && $splash['media_type'] === 'video')
                        <video id="previewBgVid"
                            style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"
                            autoplay muted loop playsinline>
                            <source src="{{ route('admin.splash.stream') }}" type="video/mp4">
                        </video>
                        @elseif($splash['media_path'])
                        <img id="previewBgImg"
                            src="{{ asset('storage/' . $splash['media_path']) }}"
                            style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="">
                        @else
                        <div id="previewBgEmpty" style="position:absolute;inset:0;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-photo-video text-white opacity-25 fs-1"></i>
                        </div>
                        @endif

                        {{-- overlay --}}
                        <div id="previewOverlay" style="position:absolute;inset:0;background:rgba(0,0,0,{{ $splash['overlay_opacity'] / 100 }})"></div>

                        {{-- text --}}
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;padding:24px 12px;text-align:center">
                            <p id="previewTitle" style="font-size:15px;font-weight:700;margin:0 0 4px;color:{{ $splash['title_color'] }}">
                                {{ $splash['title'] ?: 'Your App Title' }}
                            </p>
                            <p id="previewSubtitle" style="font-size:10px;margin:0;color:{{ $splash['title_color'] }};opacity:0.85">
                                {{ $splash['subtitle'] ?: 'Tagline goes here' }}
                            </p>
                            @if($splash['skip_enabled'] !== '0')
                            <span style="margin-top:12px;font-size:9px;color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.3);padding:2px 8px;border-radius:20px">Skip</span>
                            @endif
                        </div>

                        {{-- duration badge --}}
                        <div id="previewDuration" style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.5);color:#fff;font-size:9px;padding:2px 6px;border-radius:20px">
                            {{ $splash['duration'] }}s
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- API Response Reference --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">API Endpoint Reference</h6>
            <span class="badge bg-info">GET /api/splash-config</span>
        </div>
        <div class="card-body">
            <pre class="bg-light rounded p-3 mb-0 small"><code>{
  "status": {{ $splash['status'] === '1' ? 'true' : 'false' }},
  "media_type": "{{ $splash['media_type'] }}",
  "media_url": "{{ $splash['media_path'] ? Storage::disk('public')->url($splash['media_path']) : '' }}",
  "duration": {{ $splash['duration'] }},
  "skip_enabled": {{ $splash['skip_enabled'] !== '0' ? 'true' : 'false' }},
  "title": "{{ $splash['title'] }}",
  "subtitle": "{{ $splash['subtitle'] }}",
  "overlay_opacity": {{ $splash['overlay_opacity'] }},
  "title_color": "{{ $splash['title_color'] }}"
}</code></pre>
        </div>
    </div>

</div>

@push('js')
<script>
function previewSplashMedia(input) {
    const wrap   = document.getElementById('newPreviewWrap');
    const img    = document.getElementById('newImgPreview');
    const vid    = document.getElementById('newVidPreview');
    const file   = input.files[0];
    if (!file) return;

    wrap.classList.remove('d-none');
    const url = URL.createObjectURL(file);

    if (file.type.startsWith('video/')) {
        img.classList.add('d-none');
        vid.classList.remove('d-none');
        vid.src = url;
        // also update phone mockup
        updatePhoneBg(url, 'video');
    } else {
        vid.classList.add('d-none');
        img.classList.remove('d-none');
        img.src = url;
        updatePhoneBg(url, 'image');
    }
}

function updatePhoneBg(url, type) {
    const phone = document.getElementById('phoneMockup');
    // remove old bg elements
    ['previewBgVid','previewBgImg','previewBgEmpty'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.remove();
    });

    const overlay = document.getElementById('previewOverlay');
    if (type === 'video') {
        const v = document.createElement('video');
        v.id = 'previewBgVid';
        v.src = url; v.autoplay = true; v.muted = true; v.loop = true;
        v.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover';
        phone.insertBefore(v, overlay);
    } else {
        const i = document.createElement('img');
        i.id = 'previewBgImg'; i.src = url;
        i.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover';
        phone.insertBefore(i, overlay);
    }
}

function updateOverlay(val) {
    document.getElementById('opacityLabel').textContent = val + '%';
    document.getElementById('previewOverlay').style.background = `rgba(0,0,0,${val/100})`;
}

function updatePreviewDuration(val) {
    const el = document.getElementById('previewDuration');
    if (el) el.textContent = val + 's';
}
</script>
@endpush
</x-app-layout>
