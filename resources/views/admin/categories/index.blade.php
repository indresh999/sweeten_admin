
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Product Categories</h4>
            <p class="text-muted mb-0 small">Manage categories with CGST / SGST / IGST / Cess / HSN codes and commission rules.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Category</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @foreach([['Total','total','primary','fa-list'],['Active','active','success','fa-check-circle'],['Inactive','inactive','secondary','fa-pause-circle'],['Featured','featured','warning','fa-star']] as [$label,$key,$color,$icon])
        <div class="col"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-48 rounded-3 bg-{{ $color }}-subtle text-{{ $color }} d-flex align-items-center justify-content-center flex-shrink-0"><i class="fas {{ $icon }}"></i></div>
            <div><p class="mb-0 small text-muted">{{ $label }}</p><h5 class="mb-0 fw-bold">{{ $stats[$key] }}</h5></div>
        </div></div></div>
        @endforeach
    </div>

    <div class="card shadow-sm mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name, HSN code..." value="{{ request('search') }}"></div></div>
            <div class="col-auto"><select name="status" class="form-select form-select-sm"><option value="">All Status</option><option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option><option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option></select></div>
            <div class="col-auto"><select name="type" class="form-select form-select-sm"><option value="">All Types</option><option value="normal" {{ request('type')==='normal'?'selected':'' }}>Normal</option><option value="birthday" {{ request('type')==='birthday'?'selected':'' }}>Birthday 🎂</option></select></div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Search</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div></div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <form id="bulkForm" method="POST" action="{{ route('admin.categories.bulk') }}">@csrf
                <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                    <select name="action" class="form-select form-select-sm w-auto">
                        <option value="">Bulk Action</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Apply to selected?')">Apply</button>
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
                                <th>Tax Structure</th>
                                <th>Commission</th>
                                <th>Sub / Items</th>
                                <th>Sort</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $cat)
                        @php $tax = $cat->effective_tax; @endphp
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $cat->id }}" class="form-check-input row-check"></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($cat->image)
                                    <img src="{{ asset($cat->image) }}" width="44" height="44" class="rounded-2 border" style="object-fit:cover">
                                    @else
                                    <div class="avatar avatar-44 bg-primary-subtle rounded-2 d-flex align-items-center justify-content-center"><span class="fs-5">🎂</span></div>
                                    @endif
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $cat->category_name }}</p>
                                        <small class="text-muted">{{ $cat->all_subcategories_count }} subcategories</small>
                                        @if($cat->is_featured)<span class="badge bg-warning-subtle text-warning ms-1">⭐</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($cat->category_type === 'birthday')
                                <span class="badge bg-info-subtle text-info">🎂 Birthday</span>
                                @else<span class="badge bg-secondary-subtle text-secondary">Normal</span>@endif
                            </td>
                            <td>
                                @if($cat->hsn_code)<code class="small">{{ $cat->hsn_code }}</code>@endif
                                @if($cat->sac_code)<br><span class="small text-muted">SAC: {{ $cat->sac_code }}</span>@endif
                                @if(!$cat->hsn_code && !$cat->sac_code)<span class="text-muted small">—</span>@endif
                            </td>
                            <td>
                                @if($tax['total'] > 0 || $tax['igst'] > 0)
                                <div class="vstack gap-1">
                                    <div class="hstack gap-1">
                                        <span class="badge bg-primary-subtle text-primary" style="font-size:10px">CGST {{ $tax['cgst'] }}%</span>
                                        <span class="badge bg-success-subtle text-success" style="font-size:10px">SGST {{ $tax['sgst'] }}%</span>
                                    </div>
                                    <div class="hstack gap-1">
                                        <span class="badge bg-warning-subtle text-warning" style="font-size:10px">IGST {{ $tax['igst'] }}%</span>
                                        @if($tax['cess'] > 0)<span class="badge bg-danger-subtle text-danger" style="font-size:10px">Cess {{ $tax['cess'] }}%</span>@endif
                                    </div>
                                    @if($cat->is_tax_inclusive)<span class="badge bg-secondary-subtle text-secondary" style="font-size:10px">Tax Inclusive</span>@endif
                                </div>
                                @else
                                <span class="badge bg-success-subtle text-success">GST Exempt</span>
                                @endif
                            </td>
                            <td>
                                @if($cat->commission_percent)
                                <span class="badge bg-dark-subtle text-dark">{{ $cat->commission_type==='fixed'?'₹':'' }}{{ $cat->commission_percent }}{{ $cat->commission_type!=='fixed'?'%':'' }}</span>
                                @else<span class="text-muted small">Default</span>@endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info me-1">{{ $cat->all_subcategories_count }} sub</span>
                                <span class="badge bg-primary-subtle text-primary">{{ $cat->items_count }} items</span>
                            </td>
                            <td><span class="small text-muted">{{ $cat->sort_order ?? 0 }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.toggle',$cat->id) }}" class="d-inline">@csrf
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ $cat->status ? 'checked' : '' }} onchange="this.form.submit()" style="cursor:pointer" title="{{ $cat->status?'Active':'Inactive' }}">
                                    </div>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.categories.show',$cat->id) }}" class="btn btn-sm btn-outline-info py-0 px-2"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.categories.edit',$cat->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('admin.categories.destroy',$cat->id) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-3 d-block mb-2 opacity-25"></i>No categories yet.
                            <a href="{{ route('admin.categories.create') }}" class="d-block mt-2 text-primary">Create first category →</a>
                        </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="px-3 py-2 border-top">{{ $categories->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change',function(){
    document.querySelectorAll('.row-check').forEach(cb=>cb.checked=this.checked);
});
</script>
@endpush
</x-app-layout>
