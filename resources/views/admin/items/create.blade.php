
<x-app-layout :assets="$assets ?? []">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control{border-radius:10px;padding:8px 12px;font-size:14px;border-color:#C8E4D2}
    .ts-wrapper .ts-control:focus{border-color:#2D6A4F;box-shadow:0 0 0 3px rgba(45,106,79,0.12)}
    .ts-wrapper .ts-dropdown{border-radius:10px;border-color:#C8E4D2;font-size:14px}
    .ts-wrapper .ts-dropdown .active{background:#2D6A4F;color:#fff}
    .ts-wrapper .ts-dropdown .option{padding:8px 12px}
    .variant-card{background:#f8fdf9;border:1px solid #DCEFE3;border-radius:12px;padding:16px;margin-bottom:12px;transition:all .2s}
    .variant-card:hover{border-color:#95D5B2;box-shadow:0 2px 8px rgba(27,67,50,0.08)}
    .variant-card .variant-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    .variant-card .variant-num{background:#2D6A4F;color:#fff;border-radius:8px;padding:2px 10px;font-size:12px;font-weight:600}
    .price-preview{background:linear-gradient(135deg,#EBFAF0,#fff);border:1px solid #DCEFE3;border-radius:10px;padding:12px 16px;margin-top:12px}
    .price-preview .preview-price{font-size:22px;font-weight:800;color:#1B4332}
    .image-upload-zone{border:2px dashed #C8E4D2;border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;background:#f8fdf9}
    .image-upload-zone:hover{border-color:#2D6A4F;background:#EBFAF0}
    .image-upload-zone.dragover{border-color:#2D6A4F;background:#EBFAF0}
    .image-thumb{position:relative;display:inline-block}
    .image-thumb img{width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #DCEFE3}
    .image-thumb .remove-img{position:absolute;top:-6px;right:-6px;width:20px;height:20px;background:#E53935;color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center}
    .field-group{margin-bottom:16px}
    .field-group label{font-weight:600;font-size:13px;color:#3A6B50;margin-bottom:6px;display:block}
    .field-group .hint{font-size:11px;color:#7AAD90;margin-top:4px}
    .field-group input:focus,.field-group select:focus,.field-group textarea:focus{border-color:#2D6A4F;box-shadow:0 0 0 3px rgba(45,106,79,0.1)}
    .btn-create{background:linear-gradient(135deg,#40916C,#2D6A4F);border:none;color:#fff;font-weight:700;padding:12px 24px;border-radius:12px;font-size:15px;transition:all .2s}
    .btn-create:hover{background:linear-gradient(135deg,#2D6A4F,#1B4332);color:#fff;transform:translateY(-1px);box-shadow:0 4px 12px rgba(27,67,50,0.25)}
    .form-card{background:#fff;border:1px solid #DCEFE3;border-radius:14px;padding:20px;margin-bottom:16px}
    .form-card h6{font-weight:700;color:#1B4332;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #DCEFE3}
</style>

<div class="content-inner container-fluid pb-0">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:10px"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">Add New Item</h4>
    </div>

    @if($errors->any())
    <div class="alert alert-danger d-flex align-items-center gap-2" style="border-radius:12px">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.items.store') }}" enctype="multipart/form-data" id="itemForm">
        @csrf
        <div class="row g-3">
            <div class="col-md-8">
                {{-- Basic Info --}}
                <div class="form-card">
                    <h6><i class="fas fa-info-circle me-2 text-primary"></i>Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-group">
                                <label>Vendor *</label>
                                <select name="shop_id" id="vendorSelect" required>
                                    <option value="">Search vendor...</option>
                                    @foreach($vendors as $v)
                                    <option value="{{ $v->shop_id }}" {{ old('shop_id')==$v->shop_id?'selected':'' }}>{{ $v->restaurant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label>Category *</label>
                                <select name="category_id" id="categorySelect" required>
                                    <option value="">Search category...</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id')==$c->id?'selected':'' }}>{{ $c->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label>Subcategory</label>
                                <select name="subcategory_id" id="subcategorySelect">
                                    <option value="">Select subcategory...</option>
                                </select>
                                <div class="hint">Select a category first to load subcategories</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-group">
                                <label>Item Name *</label>
                                <input type="text" name="item_name" class="form-control" value="{{ old('item_name') }}" placeholder="e.g. Motichoor Laddu Box" required maxlength="150">
                                <div class="hint">Max 150 characters</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="field-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" maxlength="2000" placeholder="Describe your product...">{{ old('description') }}</textarea>
                                <div class="hint">Optional. Max 2000 characters</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-group">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status','active')=='active'?'selected':'' }}>Active</option>
                                    <option value="inactive" {{ old('status')=='inactive'?'selected':'' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_veg" value="1" {{ old('is_veg','1')=='1'?'checked':'' }} id="vegCheck">
                                <label class="form-check-label fw-semibold" for="vegCheck">Vegetarian</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured')?'checked':'' }} id="featCheck">
                                <label class="form-check-label fw-semibold" for="featCheck">Featured / Bestseller</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Variants --}}
                <div class="form-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Variants & Pricing</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addVariant" style="border-radius:8px">
                            <i class="fas fa-plus me-1"></i>Add Variant
                        </button>
                    </div>
                    <div id="variantContainer">
                        <div class="variant-card" data-idx="0">
                            <div class="variant-header">
                                <span class="variant-num">Default Variant</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="field-group">
                                        <label>Label *</label>
                                        <input type="text" name="variants[0][label]" class="form-control" placeholder="e.g. 500g, 1 box" required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="field-group">
                                        <label>MRP (₹) *</label>
                                        <input type="number" name="variants[0][price]" class="form-control price-input" step="0.01" min="0.01" required placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="field-group">
                                        <label>Sale Price (₹)</label>
                                        <input type="number" name="variants[0][offer_price]" class="form-control offer-input" step="0.01" min="0" placeholder="Optional">
                                        <div class="hint">Leave blank for no discount</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="field-group">
                                        <label>GST % *</label>
                                        <input type="number" name="variants[0][gst_percent]" class="form-control" step="0.01" min="0" max="100" value="5" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="field-group">
                                        <label>HSN Code</label>
                                        <input type="text" name="variants[0][hsn_code]" class="form-control" maxlength="20" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant mb-1" disabled title="Cannot remove only variant">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="price-preview" id="pricePreview" style="display:none">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted small">Preview:</span>
                            <span class="preview-price" id="previewPrice">₹0</span>
                            <span class="text-muted small text-decoration-line-through" id="previewOriginal" style="display:none">₹0</span>
                            <span class="badge bg-danger" id="previewDiscount" style="display:none">0% off</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Images --}}
                <div class="form-card">
                    <h6><i class="fas fa-image me-2 text-primary"></i>Product Images</h6>
                    <div class="image-upload-zone" id="dropZone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-1 fw-semibold" style="font-size:13px;color:#3A6B50">Click or drag images here</p>
                        <p class="mb-0 text-muted" style="font-size:11px">JPEG, PNG, WebP · Max 3MB each</p>
                        <input type="file" name="images[]" id="imageInput" accept="image/jpeg,image/png,image/webp" multiple style="display:none">
                    </div>
                    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                    <div class="hint mt-2" id="imageCount">No images selected</div>
                </div>

                {{-- Submit --}}
                <div class="form-card">
                    <button type="submit" class="btn btn-create w-100" id="submitBtn">
                        <i class="fas fa-plus-circle me-2"></i>Create Item
                    </button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:12px">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    let subcatTom = null;

    function loadSubcategories(catId, preselect){
        const sel = document.getElementById('subcategorySelect');
        sel.innerHTML = '<option value="">Loading...</option>';
        if(subcatTom){subcatTom.destroy();subcatTom=null;}
        if(!catId){
            sel.innerHTML = '<option value="">Select subcategory...</option>';
            subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Select subcategory...',maxOptions:null});
            return;
        }
        fetch('/admin/subcategories-by-cat/'+catId)
            .then(r=>r.json())
            .then(data=>{
                sel.innerHTML = '<option value="">Select subcategory...</option>';
                data.forEach(s=>{
                    const o=document.createElement('option');
                    o.value=s.id;
                    o.textContent=s.name;
                    if(preselect && String(s.id)===String(preselect)) o.selected=true;
                    sel.appendChild(o);
                });
                subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Search subcategory...',maxOptions:null});
            })
            .catch(()=>{
                sel.innerHTML = '<option value="">Error loading subcategories</option>';
                subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Select subcategory...',maxOptions:null});
            });
    }

    new TomSelect('#vendorSelect',{placeholder:'Search vendor...',maxOptions:null});
    new TomSelect('#categorySelect',{placeholder:'Search category...',maxOptions:null,
        onChange:function(val){ loadSubcategories(val); }
    });
    loadSubcategories(
        document.getElementById('categorySelect').value,
        '{{ old("subcategory_id") }}'
    );

    // ── Variant management ────────────────────────────────────────
    let varCount=1;
    document.getElementById('addVariant').addEventListener('click',function(){
        const tpl=document.createElement('div');
        tpl.className='variant-card';
        tpl.dataset.idx=varCount;
        tpl.innerHTML=`<div class="variant-header"><span class="variant-num">Variant #${varCount+1}</span><button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="fas fa-trash me-1"></i>Remove</button></div>
        <div class="row g-3">
            <div class="col-md-3"><div class="field-group"><label>Label *</label><input type="text" name="variants[${varCount}][label]" class="form-control" placeholder="e.g. 1kg, family pack" required maxlength="100"></div></div>
            <div class="col-md-2"><div class="field-group"><label>MRP (₹) *</label><input type="number" name="variants[${varCount}][price]" class="form-control price-input" step="0.01" min="0.01" required placeholder="0.00"></div></div>
            <div class="col-md-2"><div class="field-group"><label>Sale Price (₹)</label><input type="number" name="variants[${varCount}][offer_price]" class="form-control offer-input" step="0.01" min="0" placeholder="Optional"></div></div>
            <div class="col-md-2"><div class="field-group"><label>GST % *</label><input type="number" name="variants[${varCount}][gst_percent]" class="form-control" step="0.01" min="0" max="100" value="5" required></div></div>
            <div class="col-md-2"><div class="field-group"><label>HSN Code</label><input type="text" name="variants[${varCount}][hsn_code]" class="form-control" maxlength="20" placeholder="Optional"></div></div>
            <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger remove-variant mb-1"><i class="fas fa-trash"></i></button></div>
        </div>`;
        document.getElementById('variantContainer').appendChild(tpl);
        varCount++;
        updateRemoveButtons();
    });
    document.getElementById('variantContainer').addEventListener('click',function(e){
        const btn=e.target.closest('.remove-variant');
        if(!btn||btn.disabled)return;
        const card=btn.closest('.variant-card');
        if(card){card.remove();reindexVariants();updateRemoveButtons();}
    });
    function reindexVariants(){
        const cards=document.querySelectorAll('#variantContainer .variant-card');
        cards.forEach((card,i)=>{
            card.dataset.idx=i;
            card.querySelector('.variant-num').textContent=i===0?'Default Variant':`Variant #${i+1}`;
            card.querySelectorAll('input,select').forEach(el=>{
                if(el.name)el.name=el.name.replace(/variants\[\d+\]/,`variants[${i}]`);
            });
        });
        varCount=cards.length;
    }
    function updateRemoveButtons(){
        const cards=document.querySelectorAll('#variantContainer .variant-card');
        document.querySelectorAll('.remove-variant').forEach(btn=>{
            btn.disabled=cards.length<=1;
            if(cards.length<=1)btn.title='Cannot remove only variant';
            else btn.title='Remove variant';
        });
    }

    // ── Price preview ─────────────────────────────────────────────
    function updatePreview(){
        const card=document.querySelector('#variantContainer .variant-card');
        if(!card)return;
        const price=parseFloat(card.querySelector('.price-input')?.value)||0;
        const offer=parseFloat(card.querySelector('.offer-input')?.value)||0;
        const preview=document.getElementById('pricePreview');
        const pPrice=document.getElementById('previewPrice');
        const pOrig=document.getElementById('previewOriginal');
        const pDisc=document.getElementById('previewDiscount');
        if(price>0){
            preview.style.display='block';
            if(offer>0&&offer<price){
                pPrice.textContent='₹'+offer.toFixed(0);
                pOrig.style.display='inline';
                pOrig.textContent='₹'+price.toFixed(0);
                pDisc.style.display='inline';
                pDisc.textContent=Math.round((1-offer/price)*100)+'% off';
            }else{
                pPrice.textContent='₹'+price.toFixed(0);
                pOrig.style.display='none';
                pDisc.style.display='none';
            }
        }else{preview.style.display='none';}
    }
    document.getElementById('variantContainer').addEventListener('input',function(e){
        if(e.target.classList.contains('price-input')||e.target.classList.contains('offer-input'))updatePreview();
    });

    // ── Image upload zone ─────────────────────────────────────────
    const dropZone=document.getElementById('dropZone');
    const imageInput=document.getElementById('imageInput');
    const preview=document.getElementById('imagePreview');
    const countEl=document.getElementById('imageCount');
    let selectedFiles=[];

    dropZone.addEventListener('click',()=>imageInput.click());
    dropZone.addEventListener('dragover',e=>{e.preventDefault();dropZone.classList.add('dragover');});
    dropZone.addEventListener('dragleave',()=>dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop',e=>{
        e.preventDefault();dropZone.classList.remove('dragover');
        addFiles(e.dataTransfer.files);
    });
    imageInput.addEventListener('change',()=>{addFiles(imageInput.files);imageInput.value='';});

    function addFiles(files){
        for(const f of files){
            if(!f.type.match(/image\/(jpeg|png|webp)/)){alert(f.name+' is not a supported image format');continue;}
            if(f.size>3*1024*1024){alert(f.name+' exceeds 3MB limit');continue;}
            selectedFiles.push(f);
        }
        renderPreviews();
    }
    function renderPreviews(){
        preview.innerHTML='';
        selectedFiles.forEach((f,i)=>{
            const div=document.createElement('div');div.className='image-thumb';
            const img=document.createElement('img');
            const reader=new FileReader();
            reader.onload=e=>img.src=e.target.result;
            reader.readAsDataURL(f);
            const btn=document.createElement('button');btn.type='button';btn.className='remove-img';btn.innerHTML='×';
            btn.onclick=()=>{selectedFiles.splice(i,1);renderPreviews();};
            div.appendChild(img);div.appendChild(btn);preview.appendChild(div);
        });
        countEl.textContent=selectedFiles.length?selectedFiles.length+' image(s) selected':'No images selected';
        // Update file input
        const dt=new DataTransfer();
        selectedFiles.forEach(f=>dt.items.add(f));
        imageInput.files=dt.files;
    }
});
</script>
</x-app-layout>
