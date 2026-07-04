<x-app-layout :assets="$assets ?? []">
@push('styles')
<style>
.banner-preview { width:120px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #e9ecef; background:#f8f9fa; cursor:pointer; transition:transform .15s; }
.banner-preview:hover { transform:scale(1.05); }
.banner-preview-video { width:120px; height:64px; object-fit:cover; border-radius:8px; border:1px solid #e9ecef; cursor:pointer; }
.media-badge { font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; text-transform:uppercase; }
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
.stat-chip { background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:4px 10px; font-size:12px; font-weight:600; }

/* Lightbox */
.banner-lightbox { display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; }
.banner-lightbox.show { display:flex; }
.banner-lightbox .lb-content { position:relative; max-width:90vw; max-height:85vh; display:flex; align-items:center; justify-content:center; }
.banner-lightbox img { max-width:90vw; max-height:85vh; border-radius:10px; box-shadow:0 8px 40px rgba(0,0,0,0.5); object-fit:contain; }
.banner-lightbox video { max-width:90vw; max-height:85vh; border-radius:10px; box-shadow:0 8px 40px rgba(0,0,0,0.5); outline:none; }
.banner-lightbox .lb-close { position:fixed; top:18px; right:24px; z-index:10000; background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:22px; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
.banner-lightbox .lb-close:hover { background:rgba(255,255,255,0.3); }
.banner-lightbox .lb-title { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); color:#fff; font-size:14px; font-weight:600; background:rgba(0,0,0,0.5); padding:6px 18px; border-radius:20px; white-space:nowrap; }
</style>
@endpush

<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Banners</h4>
            <p class="text-muted mb-0 small">Manage home-page banners, hero slides, and promotional strips</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary px-4">
            <i class="fas fa-plus me-1"></i> New Banner
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:140px">Preview</th>
                            <th>Content</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($banners as $b)
                    <tr>
                        {{-- Preview --}}
                        <td>
                            @if($b->media_url)
                                @if($b->media_type === 'video')
                                    <div class="position-relative d-inline-block" onclick="openLightbox('video','{{ $b->media_url }}','{{ addslashes($b->heading ?? $b->title ?? 'Video Banner') }}')" style="cursor:pointer">
                                        @if($b->thumbnail_url)
                                            <img src="{{ $b->thumbnail_url }}" class="banner-preview" alt="">
                                        @else
                                            <video src="{{ $b->media_url }}" class="banner-preview-video" muted></video>
                                        @endif
                                        <span class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px">
                                            <i class="fas fa-play text-white" style="font-size:8px;margin-left:2px"></i>
                                        </span>
                                    </div>
                                @elseif($b->media_type === 'gif')
                                    <div onclick="openLightbox('image','{{ $b->media_url }}','{{ addslashes($b->heading ?? $b->title ?? 'GIF Banner') }}')" style="cursor:pointer">
                                        <img src="{{ $b->media_url }}" class="banner-preview" alt="">
                                        <div><span class="media-badge bg-warning text-dark mt-1">GIF</span></div>
                                    </div>
                                @else
                                    <div onclick="openLightbox('image','{{ $b->media_url }}','{{ addslashes($b->heading ?? $b->title ?? 'Image Banner') }}')" style="cursor:pointer">
                                        <img src="{{ $b->media_url }}" class="banner-preview" alt="">
                                    </div>
                                @endif
                            @else
                                <div class="banner-preview d-flex align-items-center justify-content-center text-muted">
                                    <i class="fas fa-image fa-lg"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Content --}}
                        <td>
                            <div class="d-flex align-items-start gap-2">
                                @if($b->is_sponsored)
                                    <span class="badge bg-warning text-dark mt-1" style="font-size:10px">Sponsored</span>
                                @endif
                                <div>
                                    @if($b->heading)
                                        <div class="fw-bold text-dark">{{ Str::limit($b->heading, 40) }}</div>
                                    @endif
                                    @if($b->title)
                                        <div class="small text-muted">{{ Str::limit($b->title, 40) }}</div>
                                    @endif
                                    @if($b->subtitle)
                                        <div class="small text-muted">{{ Str::limit($b->subtitle, 50) }}</div>
                                    @endif
                                    @if($b->cta_label)
                                        <span class="badge bg-light text-dark border mt-1" style="font-size:10px">{{ $b->cta_label }}</span>
                                    @endif
                                    @if(!$b->heading && !$b->title && !$b->subtitle)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Type --}}
                        <td>
                            <span class="badge bg-light text-dark border mb-1">{{ ucfirst($b->banner_type) }}</span>
                            @if($b->media_type !== 'image')
                                <br><span class="media-badge bg-info text-dark">{{ strtoupper($b->media_type) }}</span>
                            @endif
                            @if($b->target_type && $b->target_type !== 'none')
                                <br><span class="text-muted" style="font-size:11px"><i class="fas fa-link me-1"></i>{{ ucfirst($b->target_type) }}</span>
                            @endif
                        </td>

                        {{-- Schedule --}}
                        <td class="text-muted small">
                            @if($b->start_date || $b->end_date)
                                <div><i class="fas fa-calendar-alt me-1 text-success"></i>{{ $b->start_date?->format('d M Y') ?? '∞' }}</div>
                                <div><i class="fas fa-calendar-times me-1 text-danger"></i>{{ $b->end_date?->format('d M Y') ?? '∞' }}</div>
                            @else
                                <span class="text-success fw-semibold">Always active</span>
                            @endif
                        </td>

                        {{-- Stats --}}
                        <td>
                            <div class="stat-chip">
                                <i class="fas fa-mouse-pointer me-1 text-muted"></i>{{ number_format($b->click_count) }} clicks
                            </div>
                            <div class="text-muted small mt-1">#{{ $b->sort_order }} order</div>
                        </td>

                        {{-- Status --}}
                        <td>
                            @php
                                $today = now()->toDateString();
                                $expired = $b->end_date && $b->end_date->toDateString() < $today;
                                $scheduled = $b->start_date && $b->start_date->toDateString() > $today;
                            @endphp
                            @if($b->status === 'active' && $expired)
                                <span class="badge bg-secondary">Expired</span>
                            @elseif($b->status === 'active' && $scheduled)
                                <span class="badge bg-warning text-dark">Scheduled</span>
                            @elseif($b->status === 'active')
                                <span class="badge bg-success"><span class="status-dot bg-white" style="width:6px;height:6px"></span>Active</span>
                            @elseif($b->status === 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.banners.edit', $b->id) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.banners.destroy', $b->id) }}"
                                      onsubmit="return confirm('Delete this banner?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-image fa-2x mb-3 d-block opacity-25"></i>
                            No banners yet. <a href="{{ route('admin.banners.create') }}">Create your first banner →</a>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($banners->hasPages())
            <div class="px-4 py-3 border-top">{{ $banners->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Lightbox Modal --}}
<div class="banner-lightbox" id="bannerLightbox" onclick="closeLightbox(event)">
    <button class="lb-close" onclick="closeLightbox(event)">&times;</button>
    <div class="lb-content" onclick="event.stopPropagation()">
        <img id="lbImage" class="d-none" alt="Banner" onclick="event.stopPropagation()">
        <video id="lbVideo" class="d-none" controls playsinline onclick="event.stopPropagation()"></video>
    </div>
    <div class="lb-title" id="lbTitle"></div>
</div>

@push('scripts')
<script>
function openLightbox(type, url, title) {
    const lb = document.getElementById('bannerLightbox');
    const img = document.getElementById('lbImage');
    const vid = document.getElementById('lbVideo');
    const ttl = document.getElementById('lbTitle');

    img.classList.add('d-none');
    vid.classList.add('d-none');
    img.src = '';
    vid.src = '';

    ttl.textContent = title || '';

    if (type === 'video') {
        vid.src = url;
        vid.classList.remove('d-none');
        vid.play();
    } else {
        img.src = url;
        img.classList.remove('d-none');
    }
    lb.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    const lb = document.getElementById('bannerLightbox');
    const vid = document.getElementById('lbVideo');
    lb.classList.remove('show');
    vid.pause();
    vid.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox(e);
});
</script>
@endpush
</x-app-layout>
