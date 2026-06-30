
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ $category->category_name }}</h4>
        <span class="badge bg-{{ $category->status?'success':'secondary' }}">{{ $category->status?'Active':'Inactive' }}</span>
        @if($category->is_featured)<span class="badge bg-warning">⭐ Featured</span>@endif
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    @if($category->image)
                    <img src="{{ asset($category->image) }}" class="rounded-3 border mb-3" width="160" height="160" style="object-fit:cover">
                    @else
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto mb-3" style="width:160px;height:160px"><span style="font-size:72px">🎂</span></div>
                    @endif
                    <h5 class="fw-bold">{{ $category->category_name }}</h5>
                    <p class="text-muted small">{{ $category->description }}</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="{{ route('admin.categories.edit',$category->id) }}" class="btn btn-primary btn-sm">Edit Category</a>
                        <form method="POST" action="{{ route('admin.categories.toggle',$category->id) }}">@csrf
                            <button class="btn btn-sm btn-{{ $category->status?'warning':'success' }}">{{ $category->status?'Deactivate':'Activate' }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Stats</h6></div>
                <div class="card-body">
                    @foreach([['Subcategories',$stats['subcategories']],['Total Items',$stats['items']],['Active Items',$stats['active_items']]] as [$lbl,$val])
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">{{ $lbl }}</span><strong>{{ $val }}</strong></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Tax Details</h6></div>
                <div class="card-body">
                    @php $tax = $category->effective_tax; @endphp
                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        @foreach([['CGST',$tax['cgst'],'primary'],['SGST',$tax['sgst'],'success'],['IGST',$tax['igst'],'warning'],['Cess',$tax['cess'],'danger']] as [$lbl,$val,$c])
                        <div class="col"><div class="border rounded-2 p-3 text-center">
                            <h5 class="fw-bold text-{{ $c }} mb-0">{{ $val }}%</h5>
                            <small class="text-muted">{{ $lbl }}</small>
                        </div></div>
                        @endforeach
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6"><p class="mb-1 small"><strong>HSN Code:</strong> {{ $category->hsn_code ?: '—' }}</p></div>
                        <div class="col-md-6"><p class="mb-1 small"><strong>SAC Code:</strong> {{ $category->sac_code ?: '—' }}</p></div>
                        <div class="col-md-6"><p class="mb-1 small"><strong>Tax Inclusive:</strong> {{ $category->is_tax_inclusive?'Yes':'No' }}</p></div>
                        <div class="col-md-6"><p class="mb-1 small"><strong>Commission:</strong>
                            @if($category->commission_percent){{ $category->commission_type==='fixed'?'₹':'' }}{{ $category->commission_percent }}{{ $category->commission_type!=='fixed'?'%':'' }}
                            @else Default @endif
                        </p></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Subcategories</h6>
                    <a href="{{ route('admin.subcategories.create') }}?category_id={{ $category->id }}" class="btn btn-sm btn-outline-primary">+ Add Subcategory</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>HSN</th><th>Tax</th><th>Items</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($category->allSubcategories as $sub)
                        <tr>
                            <td>
                                @if($sub->image)<img src="{{ asset($sub->image) }}" width="32" height="32" class="rounded me-2" style="object-fit:cover">@endif
                                <a href="{{ route('admin.subcategories.edit',$sub->id) }}" class="text-dark">{{ $sub->name }}</a>
                                @if($sub->children->count())<span class="badge bg-light text-dark ms-1">{{ $sub->children->count() }} child</span>@endif
                            </td>
                            <td><code class="small">{{ $sub->hsn_code ?: '—' }}</code></td>
                            <td>
                                @php $st=$sub->effective_tax; @endphp
                                @if($st['total']>0)<span class="small">CGST {{ $st['cgst'] }}% + SGST {{ $st['sgst'] }}%</span>
                                @else<span class="text-muted small">Exempt</span>@endif
                            </td>
                            <td><span class="badge bg-info-subtle text-info">{{ $sub->items->count() }}</span></td>
                            <td><span class="badge bg-{{ $sub->status?'success':'secondary' }}">{{ $sub->status?'Active':'Off' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No subcategories yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
