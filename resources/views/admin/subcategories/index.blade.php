<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Subcategories</h4>
            <p class="text-muted mb-0 small">{{ $subcategories->total() }} total subcategories</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>Add Subcategory
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row row-cols-3 g-3 mb-4">
        @foreach([['Total','total','primary','fa-list'],['Active','active','success','fa-check-circle'],['Inactive','inactive','secondary','fa-pause-circle']] as [$label,$key,$color,$icon])
        <div class="col">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="mb-0 small text-muted">{{ $label }}</p>
                        <h5 class="mb-0 fw-bold">{{ $stats[$key] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search name, HSN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option>
                        <option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-sm btn-primary w-100">Search</button>
                </div>
                <div class="col-12 col-md-auto">
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                <span class="small text-muted">{{ $subcategories->total() }} subcategories</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subcategory</th>
                            <th>Category</th>
                            <th>HSN / SAC</th>
                            <th>Tax</th>
                            <th>Commission</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($subcategories as $sub)
                    @php $tax = $sub->effective_tax; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($sub->image)
                                <img src="{{ asset($sub->image) }}" width="36" height="36" class="rounded-2 border" style="object-fit:cover">
                                @else
                                <div class="avatar avatar-36 bg-secondary-subtle rounded-2 d-flex align-items-center justify-content-center"><i class="fas fa-tag text-secondary small"></i></div>
                                @endif
                                <div>
                                    <p class="mb-0 fw-semibold">{{ $sub->name }}</p>
                                    @if($sub->is_featured)<span class="badge bg-warning-subtle text-warning" style="font-size:10px">Featured</span>@endif
                                </div>
                            </div>
                        </td>
                        <td><a href="{{ route('admin.categories.show',$sub->category_id) }}" class="text-decoration-none small">{{ $sub->category?->category_name }}</a></td>
                        <td>
                            @if($sub->hsn_code)<code class="small">{{ $sub->hsn_code }}</code>
                            @elseif($sub->sac_code)<span class="small text-muted">SAC: {{ $sub->sac_code }}</span>
                            @else<span class="text-muted small">Inherited</span>@endif
                        </td>
                        <td>
                            @if($tax['total'] > 0)
                            <span class="badge bg-primary-subtle text-primary" style="font-size:10px">CGST {{ $tax['cgst'] }}%</span>
                            <span class="badge bg-success-subtle text-success" style="font-size:10px">SGST {{ $tax['sgst'] }}%</span>
                            @if($tax['cess']>0)<span class="badge bg-danger-subtle text-danger" style="font-size:10px">+{{ $tax['cess'] }}%</span>@endif
                            @else<span class="badge bg-success-subtle text-success">Exempt</span>@endif
                        </td>
                        <td>
                            @if($sub->commission_percent)
                            <span class="badge bg-dark-subtle text-dark">{{ $sub->commission_type==='fixed'?'₹':'' }}{{ $sub->commission_percent }}{{ $sub->commission_type!=='fixed'?'%':'' }}</span>
                            @else<span class="text-muted small">Inherited</span>@endif
                        </td>
                        <td><span class="badge bg-info-subtle text-info">{{ $sub->items_count }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.subcategories.toggle',$sub->id) }}" class="d-inline">@csrf
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" {{ $sub->status?'checked':'' }} onchange="this.form.submit()" style="cursor:pointer">
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.subcategories.edit',$sub->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('{{ route('admin.subcategories.destroy',$sub->id) }}', '{{ $sub->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-tags fa-3x d-block mb-3 opacity-25"></i>
                        <p class="mb-1 fw-semibold">No subcategories yet</p>
                        <a href="{{ route('admin.subcategories.create') }}" class="text-primary">Create first subcategory</a>
                    </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @forelse($subcategories as $sub)
        @php $tax = $sub->effective_tax; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                {{-- Top: Image + Name + Toggle --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        @if($sub->image)
                        <img src="{{ asset($sub->image) }}" class="rounded-2 flex-shrink-0" width="48" height="48" style="object-fit:cover">
                        @else
                        <div class="avatar avatar-48 bg-secondary-subtle rounded-2 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fas fa-tag text-secondary"></i>
                        </div>
                        @endif
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-truncate">{{ $sub->name }}</p>
                            <a href="{{ route('admin.categories.show',$sub->category_id) }}" class="text-muted small text-decoration-none">{{ $sub->category?->category_name }}</a>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.subcategories.toggle',$sub->id) }}" class="d-inline flex-shrink-0 ms-2">@csrf
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" {{ $sub->status?'checked':'' }} onchange="this.form.submit()" style="cursor:pointer">
                        </div>
                    </form>
                </div>

                {{-- Badges --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @if($sub->hsn_code)<span class="badge bg-light text-dark">HSN: {{ $sub->hsn_code }}</span>@endif
                    @if($tax['total'] > 0)
                    <span class="badge bg-primary-subtle text-primary">CGST {{ $tax['cgst'] }}%</span>
                    <span class="badge bg-success-subtle text-success">SGST {{ $tax['sgst'] }}%</span>
                    @else<span class="badge bg-success-subtle text-success">Tax Exempt</span>@endif
                    @if($tax['igst'] > 0)<span class="badge bg-warning-subtle text-warning">IGST {{ $tax['igst'] }}%</span>@endif
                    @if($sub->commission_percent)
                    <span class="badge bg-dark-subtle text-dark">{{ $sub->commission_type==='fixed'?'₹':'' }}{{ $sub->commission_percent }}{{ $sub->commission_type!=='fixed'?'%':'' }}</span>
                    @endif
                    <span class="badge bg-info-subtle text-info">{{ $sub->items_count }} items</span>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.subcategories.edit',$sub->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="fas fa-pen me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1"
                        onclick="confirmDelete('{{ route('admin.subcategories.destroy',$sub->id) }}', '{{ $sub->name }}')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-tags fa-3x d-block mb-3 opacity-25"></i>
            <p class="mb-1 fw-semibold">No subcategories yet</p>
            <a href="{{ route('admin.subcategories.create') }}" class="text-primary">Create first subcategory</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-3">{{ $subcategories->links('pagination::bootstrap-5') }}</div>
</div>

@push('scripts')
<script>
// SweetAlert Delete Confirmation
function confirmDelete(url, name) {
    Swal.fire({
        title: 'Delete Subcategory?',
        html: `Are you sure you want to delete <strong>"${name}"</strong>?<br><small class="text-muted">This will also remove all associated items.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-1"></i>Yes, Delete',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '@csrf <input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
</x-app-layout>
