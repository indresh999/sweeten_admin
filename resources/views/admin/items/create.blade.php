
<x-app-layout :assets="$assets ?? []">
<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">{{ isset($item) ? 'Edit Item' : 'Add Item' }}</h4>
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ isset($item) ? route('admin.items.update',$item->id) : route('admin.items.store') }}" enctype="multipart/form-data">
        @csrf @if(isset($item)) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8">
                {{-- Basic Info --}}
                <div class="card shadow-sm mb-3"><div class="card-body">
                    <h6 class="fw-bold mb-3">Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor *</label>
                            <select name="shop_id" class="form-select" required>
                                <option value="">Select vendor...</option>
                                @foreach($vendors as $v)<option value="{{ $v->shop_id }}" {{ old('shop_id',$item->shop_id??'')==$v->shop_id?'selected':'' }}>{{ $v->restaurant_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" id="categorySelect" required>
                                <option value="">Select category...</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id',$item->category_id??'')==$c->id?'selected':'' }}>{{ $c->category_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subcategory</label>
                            <select name="subcategory_id" class="form-select" id="subcategorySelect">
                                <option value="">Select subcategory...</option>
                                @if(isset($subcats)) @foreach($subcats as $s)<option value="{{ $s->id }}" {{ old('subcategory_id',$item->subcategory_id??'')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Item Name *</label>
                            <input type="text" name="item_name" class="form-control" value="{{ old('item_name',$item->item_name??'') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description',$item->description??'') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status',$item->status??'active')=='active'?'selected':'' }}>Active</option>
                                <option value="inactive" {{ old('status',$item->status??'')=='inactive'?'selected':'' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_veg" value="1" {{ old('is_veg',$item->is_veg??true)?'checked':'' }}>
                                <label class="form-check-label fw-semibold">Vegetarian</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured',$item->is_featured??false)?'checked':'' }}>
                                <label class="form-check-label fw-semibold">Featured</label>
                            </div>
                        </div>
                    </div>
                </div></div>

                {{-- Variants --}}
                <div class="card shadow-sm mb-3"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Variants & Pricing</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addVariant"><i class="fas fa-plus me-1"></i>Add Variant</button>
                    </div>
                    <div id="variantContainer">
                        @if(isset($item) && $item->variants->count() > 0)
                            @foreach($item->variants as $i => $v)
                            <div class="variant-row border rounded p-3 mb-2">
                                <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $v->id }}">
                                <div class="row g-2">
                                    <div class="col-md-3"><label class="form-label small">Label *</label><input type="text" name="variants[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $v->label }}" required></div>
                                    <div class="col-md-2"><label class="form-label small">MRP (₹) *</label><input type="number" name="variants[{{ $i }}][price]" class="form-control form-control-sm" step="0.01" value="{{ $v->price }}" required></div>
                                    <div class="col-md-2"><label class="form-label small">Offer Price</label><input type="number" name="variants[{{ $i }}][offer_price]" class="form-control form-control-sm" step="0.01" value="{{ $v->offer_price }}"></div>
                                    <div class="col-md-2"><label class="form-label small">GST % *</label><input type="number" name="variants[{{ $i }}][gst_percent]" class="form-control form-control-sm" step="0.01" value="{{ $v->gst_percent }}" required></div>
                                    <div class="col-md-2"><label class="form-label small">HSN Code</label><input type="text" name="variants[{{ $i }}][hsn_code]" class="form-control form-control-sm" value="{{ $v->hsn_code }}"></div>
                                    <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-variant mb-0"><i class="fas fa-trash"></i></button></div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="variant-row border rounded p-3 mb-2">
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label small">Label *</label><input type="text" name="variants[0][label]" class="form-control form-control-sm" placeholder="e.g. 500g" required></div>
                                <div class="col-md-2"><label class="form-label small">MRP (₹) *</label><input type="number" name="variants[0][price]" class="form-control form-control-sm" step="0.01" required></div>
                                <div class="col-md-2"><label class="form-label small">Offer Price</label><input type="number" name="variants[0][offer_price]" class="form-control form-control-sm" step="0.01"></div>
                                <div class="col-md-2"><label class="form-label small">GST % *</label><input type="number" name="variants[0][gst_percent]" class="form-control form-control-sm" step="0.01" value="5" required></div>
                                <div class="col-md-2"><label class="form-label small">HSN Code</label><input type="text" name="variants[0][hsn_code]" class="form-control form-control-sm"></div>
                                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-variant" disabled><i class="fas fa-trash"></i></button></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div></div>
            </div>

            <div class="col-md-4">
                {{-- Images --}}
                <div class="card shadow-sm mb-3"><div class="card-body">
                    <h6 class="fw-bold mb-3">Product Images</h6>
                    <input type="file" name="images[]" class="form-control mb-2" accept="image/*" multiple>
                    @if(isset($item))
                    @php $imgs = is_array($item->images) ? $item->images : json_decode($item->images??'[]',true); @endphp
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($imgs as $img)<img src="{{ asset('storage/'.$img) }}" class="rounded border" width="70" height="70" style="object-fit:cover">@endforeach
                    </div>
                    @if(!empty($imgs))<small class="text-muted">Upload new images to replace existing ones</small>@endif
                    @endif
                </div></div>
                <div class="d-grid">
                    <button class="btn btn-primary">{{ isset($item) ? 'Update Item' : 'Create Item' }}</button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary mt-2">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
let varCount = {{ isset($item) ? $item->variants->count() : 1 }};
document.getElementById('addVariant').addEventListener('click', function(){
    const tpl = `<div class="variant-row border rounded p-3 mb-2"><div class="row g-2">
        <div class="col-md-3"><label class="form-label small">Label *</label><input type="text" name="variants[${varCount}][label]" class="form-control form-control-sm" required placeholder="e.g. 1kg"></div>
        <div class="col-md-2"><label class="form-label small">MRP (₹) *</label><input type="number" name="variants[${varCount}][price]" class="form-control form-control-sm" step="0.01" required></div>
        <div class="col-md-2"><label class="form-label small">Offer Price</label><input type="number" name="variants[${varCount}][offer_price]" class="form-control form-control-sm" step="0.01"></div>
        <div class="col-md-2"><label class="form-label small">GST % *</label><input type="number" name="variants[${varCount}][gst_percent]" class="form-control form-control-sm" step="0.01" value="5" required></div>
        <div class="col-md-2"><label class="form-label small">HSN Code</label><input type="text" name="variants[${varCount}][hsn_code]" class="form-control form-control-sm"></div>
        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="fas fa-trash"></i></button></div>
    </div></div>`;
    document.getElementById('variantContainer').insertAdjacentHTML('beforeend', tpl);
    varCount++;
});
document.getElementById('variantContainer').addEventListener('click', function(e){
    if(e.target.closest('.remove-variant')) {
        const rows = document.querySelectorAll('.variant-row');
        if(rows.length > 1) e.target.closest('.variant-row').remove();
    }
});
document.getElementById('categorySelect').addEventListener('change', function(){
    const id = this.value;
    if(!id) return;
    fetch(`/admin/subcategories-by-cat/${id}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('subcategorySelect');
            sel.innerHTML = '<option value="">Select subcategory...</option>';
            data.forEach(s => sel.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.name}</option>`));
        });
});
</script>
</x-app-layout>
