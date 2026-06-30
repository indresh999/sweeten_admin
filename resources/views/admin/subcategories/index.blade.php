
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Subcategories</h4>
            <p class="text-muted mb-0 small">Manage subcategories with inherited or custom tax and commission settings.</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Subcategory</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row row-cols-3 g-3 mb-4">
        @foreach([['Total','total','primary','fa-list'],['Active','active','success','fa-check-circle'],['Inactive','inactive','secondary','fa-pause-circle']] as [$l,$k,$c,$i])
        <div class="col"><div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-44 rounded-3 bg-{{ $c }}-subtle text-{{ $c }} d-flex align-items-center justify-content-center flex-shrink-0"><i class="fas {{ $i }}"></i></div>
            <div><p class="mb-0 small text-muted">{{ $l }}</p><h5 class="mb-0 fw-bold">{{ $stats[$k] }}</h5></div>
        </div></div></div>
        @endforeach
    </div>

    <div class="card shadow-sm mb-3"><div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name, HSN..." value="{{ request('search') }}"></div></div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm" id="catFilter">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><select name="status" class="form-select form-select-sm"><option value="">All Status</option><option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option><option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option></select></div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Search</button>
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div></div>

    <div class="card shadow-sm"><div class="card-body p-0">
        <div class="d-flex align-items-center gap-2 p-3 border-bottom">
            <span class="small text-muted">{{ $subcategories->total() }} subcategories</span>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Subcategory</th>
                    <th>Category</th>
                    <th>Parent</th>
                    <th>HSN / SAC</th>
                    <th>Tax (CGST+SGST)</th>
                    <th>IGST</th>
                    <th>Commission</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($subcategories as $sub)
            @php $tax = $sub->effective_tax; @endphp
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($sub->image)
                        <img src="{{ asset($sub->image) }}" width="40" height="40" class="rounded-2 border" style="object-fit:cover">
                        @else
                        <div class="avatar avatar-40 bg-secondary-subtle rounded-2 d-flex align-items-center justify-content-center"><i class="fas fa-tag text-secondary small"></i></div>
                        @endif
                        <div>
                            <p class="mb-0 fw-semibold">{{ $sub->name }}</p>
                            @if($sub->is_featured)<span class="badge bg-warning-subtle text-warning" style="font-size:10px">⭐ Featured</span>@endif
                        </div>
                    </div>
                </td>
                <td><a href="{{ route('admin.categories.show',$sub->category_id) }}" class="text-decoration-none small">{{ $sub->category?->category_name }}</a></td>
                <td>{{ $sub->parent?->name ?? '<span class="text-muted small">Root</span>' }}</td>
                <td>
                    @if($sub->hsn_code)<code class="small">{{ $sub->hsn_code }}</code>@endif
                    @if($sub->sac_code)<br><span class="small text-muted">SAC: {{ $sub->sac_code }}</span>@endif
                    @if(!$sub->hsn_code && !$sub->sac_code)<span class="text-muted small">Inherited</span>@endif
                </td>
                <td>
                    @if($tax['total'] > 0)
                    <span class="badge bg-primary-subtle text-primary" style="font-size:10px">{{ $tax['cgst'] }}%</span>
                    <span class="badge bg-success-subtle text-success" style="font-size:10px">{{ $tax['sgst'] }}%</span>
                    @if($tax['cess']>0)<span class="badge bg-danger-subtle text-danger" style="font-size:10px">+{{ $tax['cess'] }}%</span>@endif
                    @else<span class="badge bg-success-subtle text-success">Exempt</span>@endif
                </td>
                <td>
                    @if($tax['igst']>0)
                    <span class="badge bg-warning-subtle text-warning" style="font-size:10px">{{ $tax['igst'] }}%</span>
                    @else<span class="text-muted small">—</span>@endif
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
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.subcategories.edit',$sub->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('admin.subcategories.destroy',$sub->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center py-5 text-muted">
                <i class="fas fa-tags fs-3 d-block mb-2 opacity-25"></i>No subcategories yet.
                <a href="{{ route('admin.subcategories.create') }}" class="d-block mt-2 text-primary">Create first subcategory →</a>
            </td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="px-3 py-2 border-top">{{ $subcategories->links('pagination::bootstrap-5') }}</div>
    </div></div>
</div>
</x-app-layout>
