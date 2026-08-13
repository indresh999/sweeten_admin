<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Policies</h4>
            <p class="text-muted mb-0 small">Manage privacy policy, terms, refund policy and other legal pages shown in the app</p>
        </div>
        <a href="{{ route('admin.policies.create') }}" class="btn btn-primary px-4">
            <i class="fas fa-plus me-1"></i> New Policy
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
                            <th style="width:60px">#</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Content Preview</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($policies as $p)
                    <tr>
                        <td class="text-muted">{{ $p->sort_order }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $p->title }}</div>
                        </td>
                        <td>
                            <code class="small">{{ $p->slug }}</code>
                        </td>
                        <td>
                            <span class="text-muted small">{{ Str::limit(strip_tags($p->content ?? ''), 80) ?: '—' }}</span>
                        </td>
                        <td>
                            @if($p->is_active)
                                <span class="badge bg-success"><span class="status-dot bg-white" style="width:6px;height:6px"></span> Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $p->updated_at?->diffForHumans() ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.policies.edit', $p->id) }}"
                                   class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.policies.destroy', $p->id) }}"
                                      class="d-inline" onsubmit="return confirm('Delete this policy?')">
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
                            <i class="fas fa-file-contract fa-2x mb-3 d-block opacity-25"></i>
                            No policies yet. <a href="{{ route('admin.policies.create') }}">Create your first policy →</a>
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($policies->hasPages())
            <div class="px-4 py-3 border-top">{{ $policies->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
