
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ isset($category) ? 'Edit Category' : 'New Category' }}</h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ isset($category) ? route('admin.categories.update',$category->id) : route('admin.categories.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($category)) @method('PUT') @endif

        @php $cat = $category ?? null; $sub = null; $item = null; @endphp

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Basic Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Category Name *</label>
                                <input type="text" name="category_name" class="form-control" required value="{{ old('category_name',$cat?->category_name) }}" placeholder="e.g. Cakes, Sweets, Beverages">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order',$cat?->sort_order??0) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Short description shown in app">{{ old('description',$cat?->description) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Category Type</label>
                                <select name="category_type" class="form-select">
                                    <option value="">Normal</option>
                                    <option value="birthday" {{ old('category_type',$cat?->category_type)==='birthday'?'selected':'' }}>Birthday 🎂</option>
                                    <option value="seasonal" {{ old('category_type',$cat?->category_type)==='seasonal'?'selected':'' }}>Seasonal</option>
                                    <option value="premium"  {{ old('category_type',$cat?->category_type)==='premium'?'selected':'' }}>Premium</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="1" {{ old('status',$cat?->status??1)==='1'||old('status',$cat?->status??1)===1?'selected':'' }}>Active</option>
                                    <option value="0" {{ old('status',$cat?->status??1)==='0'||old('status',$cat?->status??1)===0?'selected':'' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured',$cat?->is_featured)?'checked':'' }}>
                                    <label class="form-check-label fw-semibold">Featured on Home</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
<div class="card shadow-sm mb-3">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">🇮🇳 Indian Tax (GST) Configuration</h6>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Quick Fill GST Slab</button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach($taxSlabs as $slab)
                <li><a class="dropdown-item small" href="#" onclick="fillTax({{ $slab['gst'] }},{{ $slab['cgst'] }},{{ $slab['sgst'] }},{{ $slab['igst'] }});return false;">{{ $slab['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info p-2 mb-3 small mb-0">
            <i class="fas fa-info-circle me-1"></i>
            <strong>CGST + SGST</strong> = Intra-state sales &nbsp;|&nbsp; <strong>IGST</strong> = Inter-state sales &nbsp;|&nbsp; <strong>Cess</strong> = Luxury/sin/tobacco goods only
        </div>
        <div class="row g-3 mt-0">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Total GST %</label>
                <div class="input-group"><input type="number" name="gst_percent" id="gst_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('gst_percent',$cat?->gst_percent??$sub?->gst_percent??'') }}" placeholder="e.g. 18"><span class="input-group-text">%</span></div>
                <small class="text-muted">Auto-fills CGST/SGST/IGST below</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">CGST %</label>
                <div class="input-group"><input type="number" name="cgst_percent" id="cgst_percent" class="form-control" step="0.01" min="0" max="50" value="{{ old('cgst_percent',$cat?->cgst_percent??$sub?->cgst_percent??'') }}" placeholder="e.g. 9"><span class="input-group-text">%</span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">SGST / UTGST %</label>
                <div class="input-group"><input type="number" name="sgst_percent" id="sgst_percent" class="form-control" step="0.01" min="0" max="50" value="{{ old('sgst_percent',$cat?->sgst_percent??$sub?->sgst_percent??'') }}" placeholder="e.g. 9"><span class="input-group-text">%</span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">IGST % <span class="text-muted small">(inter-state)</span></label>
                <div class="input-group"><input type="number" name="igst_percent" id="igst_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('igst_percent',$cat?->igst_percent??$sub?->igst_percent??'') }}" placeholder="e.g. 18"><span class="input-group-text">%</span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Cess %</label>
                <div class="input-group"><input type="number" name="cess_percent" id="cess_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('cess_percent',$cat?->cess_percent??$sub?->cess_percent??'') }}" placeholder="0"><span class="input-group-text">%</span></div>
                <small class="text-muted">Only for luxury/sin goods</small>
            </div>
            <div class="col-md-4 d-flex align-items-center mt-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_tax_inclusive" value="1" {{ old('is_tax_inclusive',$cat?->is_tax_inclusive??$sub?->is_tax_inclusive??false)?'checked':'' }}>
                    <label class="form-check-label fw-semibold">Tax Inclusive Pricing<br><small class="text-muted fw-normal">MRP already includes GST</small></label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">HSN Code</label>
                <input type="text" name="hsn_code" class="form-control" maxlength="20" value="{{ old('hsn_code',$cat?->hsn_code??$sub?->hsn_code??'') }}" placeholder="e.g. 1905">
                <small class="text-muted">Harmonised System of Nomenclature</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">SAC Code</label>
                <input type="text" name="sac_code" class="form-control" maxlength="20" value="{{ old('sac_code',$cat?->sac_code??$sub?->sac_code??'') }}" placeholder="e.g. 996333">
                <small class="text-muted">For service classifications</small>
            </div>
        </div>
    </div>
</div>
<script>
function fillTax(gst,cgst,sgst,igst){
    document.getElementById('gst_percent').value=gst;
    document.getElementById('cgst_percent').value=cgst;
    document.getElementById('sgst_percent').value=sgst;
    document.getElementById('igst_percent').value=igst;
}
document.addEventListener('DOMContentLoaded',function(){
    const gstEl = document.getElementById('gst_percent');
    if(gstEl){
        gstEl.addEventListener('change',function(){
            const v=parseFloat(this.value)||0;
            document.getElementById('cgst_percent').value=(v/2).toFixed(2);
            document.getElementById('sgst_percent').value=(v/2).toFixed(2);
            document.getElementById('igst_percent').value=v.toFixed(2);
        });
    }
});
</script>


                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Commission Settings</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Commission Type</label>
                                <select name="commission_type" class="form-select">
                                    <option value="percentage" {{ old('commission_type',$cat?->commission_type??'percentage')==='percentage'?'selected':'' }}>Percentage (%)</option>
                                    <option value="fixed"      {{ old('commission_type',$cat?->commission_type)==='fixed'?'selected':'' }}>Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Commission Value</label>
                                <div class="input-group">
                                    <input type="number" name="commission_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('commission_percent',$cat?->commission_percent) }}" placeholder="e.g. 5">
                                    <span class="input-group-text">% / ₹</span>
                                </div>
                                <small class="text-muted">Leave blank to use platform default</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">SEO (optional)</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ old('meta_title',$cat?->meta_title) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2" maxlength="500">{{ old('meta_description',$cat?->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Category Image</h6></div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if(isset($cat) && $cat->image)
                            <img id="preview" src="{{ asset($cat->image) }}" class="rounded-3 border mb-2" width="140" height="140" style="object-fit:cover">
                            @else
                            <div id="preview-placeholder" class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto mb-2" style="width:140px;height:140px">
                                <span style="font-size:56px">🎂</span>
                            </div>
                            <img id="preview" class="rounded-3 border mb-2 d-none" width="140" height="140" style="object-fit:cover">
                            @endif
                        </div>
                        <input type="file" name="image" id="imageInput" accept="image/*" class="form-control form-control-sm">
                        <small class="text-muted d-block mt-1">JPG, PNG, WEBP — max 3MB</small>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary">{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
document.getElementById('imageInput').addEventListener('change',function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('preview');
            const ph  = document.getElementById('preview-placeholder');
            img.src = e.target.result;
            img.classList.remove('d-none');
            if(ph) ph.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
</x-app-layout>
