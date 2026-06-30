
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ isset($sub) ? 'Edit Subcategory' : 'New Subcategory' }}</h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($sub) ? route('admin.subcategories.update',$sub->id) : route('admin.subcategories.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($sub)) @method('PUT') @endif

        @php $cat = null; @endphp

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Basic Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Category *</label>
                                <select name="category_id" class="form-select" id="categorySelect" required onchange="loadSubcats(this.value)">
                                    <option value="">Select category...</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id',$sub?->category_id??request('category_id'))==$c->id?'selected':'' }}>{{ $c->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Subcategory <span class="text-muted small">(optional)</span></label>
                                <select name="parent_id" class="form-select" id="parentSubSelect">
                                    <option value="">None (Root level)</option>
                                    @if(isset($subcategories))
                                    @foreach($subcategories as $s)
                                    <option value="{{ $s->id }}" {{ old('parent_id',$sub?->parent_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Will inherit tax/HSN from parent if not set</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Subcategory Name *</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name',$sub?->name) }}" placeholder="e.g. Chocolate Cakes, Dry Sweets...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order',$sub?->sort_order??0) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="1" {{ old('status',$sub?->status??1)?'selected':'' }}>Active</option>
                                    <option value="0" {{ old('status',$sub?->status??1)===0?'selected':'' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description',$sub?->description) }}</textarea>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured',$sub?->is_featured)?'checked':'' }}>
                                    <label class="form-check-label fw-semibold">Featured</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">🇮🇳 Tax Configuration</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="inheritFromCategory()">⬆ Inherit from Category</button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Quick GST Slab</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach($taxSlabs as $slab)
                                    <li><a class="dropdown-item small" href="#" onclick="fillTax({{ $slab['gst'] }},{{ $slab['cgst'] }},{{ $slab['sgst'] }},{{ $slab['igst'] }});return false;">{{ $slab['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info p-2 mb-3 small">
                            <i class="fas fa-info-circle me-1"></i>Leave blank to inherit from parent category. CGST+SGST = intra-state | IGST = inter-state.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Total GST %</label>
                                <div class="input-group"><input type="number" name="gst_percent" id="gst_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('gst_percent',$sub?->gst_percent??'') }}" placeholder="Auto-fill below"><span class="input-group-text">%</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">CGST %</label>
                                <div class="input-group"><input type="number" name="cgst_percent" id="cgst_percent" class="form-control" step="0.01" min="0" max="50" value="{{ old('cgst_percent',$sub?->cgst_percent??'') }}" placeholder="e.g. 9"><span class="input-group-text">%</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SGST / UTGST %</label>
                                <div class="input-group"><input type="number" name="sgst_percent" id="sgst_percent" class="form-control" step="0.01" min="0" max="50" value="{{ old('sgst_percent',$sub?->sgst_percent??'') }}" placeholder="e.g. 9"><span class="input-group-text">%</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">IGST %</label>
                                <div class="input-group"><input type="number" name="igst_percent" id="igst_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('igst_percent',$sub?->igst_percent??'') }}" placeholder="e.g. 18"><span class="input-group-text">%</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cess %</label>
                                <div class="input-group"><input type="number" name="cess_percent" id="cess_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('cess_percent',$sub?->cess_percent??'') }}" placeholder="0"><span class="input-group-text">%</span></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">HSN Code</label>
                                <input type="text" name="hsn_code" id="hsn_code" class="form-control" maxlength="20" value="{{ old('hsn_code',$sub?->hsn_code??'') }}" placeholder="e.g. 1905">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SAC Code</label>
                                <input type="text" name="sac_code" class="form-control" maxlength="20" value="{{ old('sac_code',$sub?->sac_code??'') }}" placeholder="e.g. 996333">
                            </div>
                            <div class="col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_tax_inclusive" value="1" {{ old('is_tax_inclusive',$sub?->is_tax_inclusive??false)?'checked':'' }}>
                                    <label class="form-check-label fw-semibold">Tax Inclusive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Commission</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Commission Type</label>
                                <select name="commission_type" class="form-select">
                                    <option value="percentage" {{ old('commission_type',$sub?->commission_type??'percentage')==='percentage'?'selected':'' }}>Percentage (%)</option>
                                    <option value="fixed"      {{ old('commission_type',$sub?->commission_type)==='fixed'?'selected':'' }}>Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Commission Value</label>
                                <div class="input-group">
                                    <input type="number" name="commission_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('commission_percent',$sub?->commission_percent??'') }}" placeholder="Blank = inherit from category">
                                    <span class="input-group-text">% / ₹</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0">Subcategory Image</h6></div>
                    <div class="card-body text-center">
                        @if(isset($sub) && $sub->image)
                        <img id="preview" src="{{ asset($sub->image) }}" class="rounded-3 border mb-2" width="140" height="140" style="object-fit:cover">
                        @else
                        <div id="preview-ph" class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto mb-2" style="width:140px;height:140px"><i class="fas fa-tags text-muted fs-2"></i></div>
                        <img id="preview" class="rounded-3 border mb-2 d-none" width="140" height="140" style="object-fit:cover">
                        @endif
                        <input type="file" name="image" id="imgInput" accept="image/*" class="form-control form-control-sm">
                        <small class="text-muted d-block mt-1">JPG, PNG, WEBP — max 3MB</small>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary">{{ isset($sub) ? 'Update Subcategory' : 'Create Subcategory' }}</button>
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
const categoryTaxData = {};
@foreach($categories as $c)
categoryTaxData[{{ $c->id }}] = {
    cgst: {{ $c->cgst_percent ?? 0 }},
    sgst: {{ $c->sgst_percent ?? 0 }},
    igst: {{ $c->igst_percent ?? 0 }},
    cess: {{ $c->cess_percent ?? 0 }},
    gst:  {{ $c->gst_percent ?? 0 }},
    hsn:  '{{ $c->hsn_code }}',
};
@endforeach

function fillTax(gst,cgst,sgst,igst){
    document.getElementById('gst_percent').value=gst;
    document.getElementById('cgst_percent').value=cgst;
    document.getElementById('sgst_percent').value=sgst;
    document.getElementById('igst_percent').value=igst;
}

document.getElementById('gst_percent')?.addEventListener('change',function(){
    const v=parseFloat(this.value)||0;
    document.getElementById('cgst_percent').value=(v/2).toFixed(2);
    document.getElementById('sgst_percent').value=(v/2).toFixed(2);
    document.getElementById('igst_percent').value=v.toFixed(2);
});

function inheritFromCategory(){
    const catId = document.getElementById('categorySelect').value;
    if(!catId){ alert('Select a parent category first.'); return; }
    const d = categoryTaxData[catId];
    if(d){
        document.getElementById('gst_percent').value=d.gst;
        document.getElementById('cgst_percent').value=d.cgst;
        document.getElementById('sgst_percent').value=d.sgst;
        document.getElementById('igst_percent').value=d.igst;
        document.getElementById('cess_percent').value=d.cess;
        document.getElementById('hsn_code').value=d.hsn;
    }
}

function loadSubcats(catId){
    const sel = document.getElementById('parentSubSelect');
    sel.innerHTML='<option value="">None (Root level)</option>';
    if(!catId) return;
    fetch('/admin/categories/'+catId+'/subcategories')
        .then(r=>r.json())
        .then(data=>{
            data.forEach(s=>{
                const opt=document.createElement('option');
                opt.value=s.id; opt.text=s.text; sel.appendChild(opt);
            });
        });
}

document.getElementById('imgInput')?.addEventListener('change',function(){
    const file=this.files[0];
    if(file){
        const reader=new FileReader();
        reader.onload=e=>{
            const img=document.getElementById('preview');
            const ph=document.getElementById('preview-ph');
            img.src=e.target.result; img.classList.remove('d-none');
            if(ph) ph.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
</x-app-layout>
