
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Banners</h4>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Banner</a>
    </div>
    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Preview</th><th>Title</th><th>Type</th><th>Target</th><th>Valid</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($banners as $b)
            <tr>
                <td><img src="{{ $b->image_url }}" alt="" width="100" height="50" style="object-fit:cover;border-radius:6px"></td>
                <td>{{ $b->title ?: '—' }}@if($b->is_sponsored)<br><span class="badge bg-warning text-dark small">Sponsored</span>@endif</td>
                <td><span class="badge bg-light text-dark">{{ ucfirst($b->banner_type) }}</span></td>
                <td class="small text-muted">{{ ucfirst($b->target_type) }}{{ $b->target_id ? ' #'.$b->target_id : '' }}</td>
                <td class="small text-muted">
                    @if($b->start_date){{ \Carbon\Carbon::parse($b->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($b->end_date)->format('d M Y') }}@else<span class="text-muted">Always</span>@endif
                </td>
                <td><span class="badge bg-{{ $b->status==='active'?'success':'secondary' }}">{{ ucfirst($b->status) }}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.banners.edit',$b->id) }}" class="btn btn-sm btn-outline-primary py-0">Edit</a>
                        <form method="POST" action="{{ route('admin.banners.destroy',$b->id) }}" class="d-inline" onsubmit="return confirm('Delete banner?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No banners yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 pb-2">{{ $banners->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
