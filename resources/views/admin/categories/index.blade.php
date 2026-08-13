<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Categories</h4>
            <p class="text-muted mb-0 small">{{ $categories->total() }} total categories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>Add Category
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
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @foreach([['Total','total','primary','fa-list'],['Active','active','success','fa-check-circle'],['Inactive','inactive','secondary','fa-pause-circle'],['Featured','featured','warning','fa-star']] as [$label,$key,$color,$icon])
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
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search name, HSN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option>
                        <option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="normal" {{ request('type')==='normal'?'selected':'' }}>Normal</option>
                        <option value="birthday" {{ request('type')==='birthday'?'selected':'' }}>Birthday</option>
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-sm btn-primary w-100">Search</button>
                </div>
                <div class="col-6 col-md-auto">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <form id="bulkForm" method="POST" action="{{ route('admin.categories.bulk') }}">@csrf
                <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                    <select name="action" class="form-select form-select-sm w-auto">
                        <option value="">Bulk Action</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkApply()">Apply</button>
                    <span class="ms-auto small text-muted">{{ $categories->total() }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="36"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>HSN / SAC</th>
                                <th>Tax</th>
                                <th>Commission</th>
                                <th>Sub / Items</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $cat)
                        @php $tax = $cat->effective_tax; @endphp
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $cat->id }}" class="form-check-input row-check"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($cat->image)
                                    <img src="{{ asset($cat->image) }}" width="40" height="40" class="rounded-2 border" style="object-fit:cover">
                                    @else
                                    <div class="avatar avatar-40 bg-primary-subtle rounded-2 d-flex align-items-center justify-content-center"><i class="fas fa-folder text-primary"></i></div>
                                    @endif
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $cat->category_name }}</p>
                                        <small class="text-muted">{{ $cat->all_subcategories_count }} subcategories</small>
                                        @if($cat->is_featured)<span class="badge bg-warning-subtle text-warning" style="font-size:10px">Featured</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($cat->category_type === 'birthday')
                                <span class="badge bg-info-subtle text-info">Birthday</span>
                                @else<span class="badge bg-secondary-subtle text-secondary">Normal</span>@endif
                            </td>
                            <td>
                                @if($cat->hsn_code)<code class="small">{{ $cat->hsn_code }}</code>
                                @elseif($cat->sac_code)<span class="small text-muted">SAC: {{ $cat->sac_code }}</span>
                                @else<span class="text-muted small">—</span>@endif
                            </td>
                            <td>
                                @if($tax['total'] > 0 || $tax['igst'] > 0)
                                <div class="vstack gap-1">
                                    <span class="badge bg-primary-subtle text-primary" style="font-size:10px">CGST {{ $tax['cgst'] }}%</span>
                                    <span class="badge bg-success-subtle text-success" style="font-size:10px">SGST {{ $tax['sgst'] }}%</span>
                                    @if($tax['igst'] > 0)<span class="badge bg-warning-subtle text-warning" style="font-size:10px">IGST {{ $tax['igst'] }}%</span>@endif
                                    @if($cat->is_tax_inclusive)<span class="badge bg-secondary-subtle text-secondary" style="font-size:10px">Inclusive</span>@endif
                                </div>
                                @else<span class="badge bg-success-subtle text-success">Exempt</span>@endif
                            </td>
                            <td>
                                @if($cat->commission_percent)
                                <span class="badge bg-dark-subtle text-dark">{{ $cat->commission_type==='fixed'?'₹':'' }}{{ $cat->commission_percent }}{{ $cat->commission_type!=='fixed'?'%':'' }}</span>
                                @else<span class="text-muted small">Default</span>@endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info me-1">{{ $cat->all_subcategories_count }}</span>
                                <span class="badge bg-primary-subtle text-primary">{{ $cat->items_count }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.toggle',$cat->id) }}" class="d-inline">@csrf
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ $cat->status ? 'checked' : '' }} onchange="this.form.submit()" style="cursor:pointer">
                                    </div>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.categories.show',$cat->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit',$cat->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                        onclick="confirmDelete('{{ route('admin.categories.destroy',$cat->id) }}', '{{ $cat->category_name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x d-block mb-3 opacity-25"></i>
                            <p class="mb-1 fw-semibold">No categories yet</p>
                            <a href="{{ route('admin.categories.create') }}" class="text-primary">Create first category</a>
                        </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        <form id="bulkFormMobile" method="POST" action="{{ route('admin.categories.bulk') }}">@csrf
            <div class="d-flex align-items-center gap-2 mb-3">
                <select name="action" class="form-select form-select-sm flex-grow-1">
                    <option value="">Bulk Action</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="bulkApplyMobile()">Apply</button>
            </div>
        </form>

        @forelse($categories as $cat)
        @php $tax = $cat->effective_tax; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                {{-- Top: Image + Name + Toggle --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        @if($cat->image)
                        <img src="{{ asset($cat->image) }}" class="rounded-2 flex-shrink-0" width="48" height="48" style="object-fit:cover">
                        @else
                        <div class="avatar avatar-48 bg-primary-subtle rounded-2 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fas fa-folder text-primary"></i>
                        </div>
                        @endif
                        <div class="min-w-0">
                            <p class="mb-0 fw-semibold text-truncate">{{ $cat->category_name }}</p>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                @if($cat->category_type === 'birthday')
                                <span class="badge bg-info-subtle text-info">Birthday</span>
                                @else<span class="badge bg-secondary-subtle text-secondary">Normal</span>@endif
                                @if($cat->is_featured)<span class="badge bg-warning-subtle text-warning">Featured</span>@endif
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.toggle',$cat->id) }}" class="d-inline flex-shrink-0 ms-2">@csrf
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" {{ $cat->status ? 'checked' : '' }} onchange="this.form.submit()" style="cursor:pointer">
                        </div>
                    </form>
                </div>

                {{-- Badges --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @if($cat->hsn_code)<span class="badge bg-light text-dark">HSN: {{ $cat->hsn_code }}</span>@endif
                    @if($tax['total'] > 0)
                    <span class="badge bg-primary-subtle text-primary">CGST {{ $tax['cgst'] }}%</span>
                    <span class="badge bg-success-subtle text-success">SGST {{ $tax['sgst'] }}%</span>
                    @else<span class="badge bg-success-subtle text-success">Tax Exempt</span>@endif
                    @if($cat->commission_percent)
                    <span class="badge bg-dark-subtle text-dark">{{ $cat->commission_type==='fixed'?'₹':'' }}{{ $cat->commission_percent }}{{ $cat->commission_type!=='fixed'?'%':'' }}</span>
                    @endif
                    <span class="badge bg-info-subtle text-info">{{ $cat->all_subcategories_count }} sub</span>
                    <span class="badge bg-primary-subtle text-primary">{{ $cat->items_count }} items</span>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.show',$cat->id) }}" class="btn btn-sm btn-outline-secondary flex-grow-1">
                        <i class="fas fa-eye me-1"></i>View
                    </a>
                    <a href="{{ route('admin.categories.edit',$cat->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="fas fa-pen me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1"
                        onclick="confirmDelete('{{ route('admin.categories.destroy',$cat->id) }}', '{{ $cat->category_name }}')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x d-block mb-3 opacity-25"></i>
            <p class="mb-1 fw-semibold">No categories yet</p>
            <a href="{{ route('admin.categories.create') }}" class="text-primary">Create first category</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
</div>

@push('scripts')
<script>
// Check All checkbox
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});

// SweetAlert Delete Confirmation
function confirmDelete(url, name) {
    Swal.fire({
        title: 'Delete Category?',
        html: `Are you sure you want to delete <strong>"${name}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
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
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = '@csrf <input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Bulk Apply with confirmation
function bulkApply() {
    const form = document.getElementById('bulkForm');
    const action = form.querySelector('select[name="action"]').value;
    if (!action) {
        Swal.fire({ title: 'Select Action', text: 'Please select a bulk action first.', icon: 'info', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
        return;
    }
    const checked = form.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
        Swal.fire({ title: 'No Selection', text: 'Please select at least one category.', icon: 'info', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
        return;
    }
    const actionText = action === 'delete' ? 'Delete' : action === 'activate' ? 'Activate' : 'Deactivate';
    Swal.fire({
        title: `${actionText} Categories?`,
        text: `Apply "${actionText}" to ${checked.length} selected categories?`,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#3a57e8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText}`,
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: `btn btn-${action === 'delete' ? 'danger' : 'primary'}`, cancelButton: 'btn btn-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
}

function bulkApplyMobile() {
    const form = document.getElementById('bulkFormMobile');
    const action = form.querySelector('select[name="action"]').value;
    if (!action) {
        Swal.fire({ title: 'Select Action', text: 'Please select a bulk action first.', icon: 'info', confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
        return;
    }
    const actionText = action === 'delete' ? 'Delete' : action === 'activate' ? 'Activate' : 'Deactivate';
    Swal.fire({
        title: `${actionText} Categories?`,
        text: `Apply "${actionText}" to all matching categories?`,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#3a57e8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText}`,
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: `btn btn-${action === 'delete' ? 'danger' : 'primary'}`, cancelButton: 'btn btn-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
}
</script>
@endpush
</x-app-layout>
