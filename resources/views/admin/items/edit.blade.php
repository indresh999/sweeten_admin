<x-app-layout :assets="$assets ?? []">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control{border-radius:10px;padding:8px 12px;font-size:14px;border-color:#C8E4D2;background:#fff}
    .ts-wrapper .ts-control:focus{border-color:#2D6A4F;box-shadow:0 0 0 3px rgba(45,106,79,0.12)}
    .ts-wrapper .ts-dropdown{border-radius:10px;border-color:#C8E4D2;font-size:14px}
    .ts-wrapper .ts-dropdown .active{background:#2D6A4F;color:#fff}
    .ts-wrapper .ts-dropdown .option{padding:8px 12px}

    .edit-header{background:#fff;border:1px solid #E8F5E9;border-radius:16px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:16px}
    .edit-header .item-badge{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
    .edit-header .veg-dot{display:inline-block;width:18px;height:18px;border-radius:4px;border:2.5px solid;position:relative;vertical-align:middle}
    .edit-header .veg-dot.veg{border-color:#2D7A2D}.edit-header .veg-dot.veg::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:9px;height:9px;border-radius:50%;background:#2D7A2D}
    .edit-header .veg-dot.nonveg{border-color:#D32F2F}.edit-header .veg-dot.nonveg::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:9px;height:9px;border-radius:50%;background:#D32F2F}

    .section-card{background:#fff;border:1px solid #E8F5E9;border-radius:16px;padding:20px;margin-bottom:16px}
    .section-card .section-title{font-size:14px;font-weight:700;color:#1B4332;margin-bottom:16px;display:flex;align-items:center;gap:8px;padding-bottom:12px;border-bottom:1px solid #E8F5E9}
    .section-card .section-title i{color:#2D6A4F;font-size:15px}

    .fg{margin-bottom:14px}
    .fg label{font-weight:600;font-size:12px;color:#3A6B50;margin-bottom:5px;display:block;letter-spacing:0.3px}
    .fg .form-control,.fg .form-select{border-radius:10px;border-color:#DCEFE3;font-size:13px;padding:9px 12px;transition:all .15s}
    .fg .form-control:focus,.fg .form-select:focus{border-color:#2D6A4F;box-shadow:0 0 0 3px rgba(45,106,79,0.1)}
    .fg .form-text{font-size:10px;color:#7AAD90;margin-top:3px}

    .variant-box{background:#F8FBF9;border:1px solid #DCEFE3;border-radius:12px;padding:14px;margin-bottom:10px;position:relative;transition:all .2s}
    .variant-box:hover{border-color:#95D5B2}
    .variant-box .vhead{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
    .variant-box .vnum{background:#2D6A4F;color:#fff;border-radius:6px;padding:2px 10px;font-size:11px;font-weight:600}
    .variant-box .del-variant{background:none;border:1px solid #FFCDD2;color:#C62828;border-radius:8px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer;transition:all .15s}
    .variant-box .del-variant:hover{background:#FFEBEE;border-color:#C62828}

    .price-preview-bar{background:linear-gradient(135deg,#F1F8E9,#E8F5E9);border:1px solid #C5E1A5;border-radius:10px;padding:10px 14px;margin-top:12px;display:none}
    .price-preview-bar .pp{font-size:20px;font-weight:800;color:#1B4332}
    .price-preview-bar .op{font-size:12px;color:#7AAD90;text-decoration:line-through;margin-left:8px}
    .price-preview-bar .disc{background:#C62828;color:#fff;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700;margin-left:8px}

    .img-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px}
    .img-cell{position:relative;border-radius:12px;overflow:hidden;aspect-ratio:1;border:2px solid #E8F5E9;cursor:pointer;transition:all .2s}
    .img-cell:hover{border-color:#2D6A4F;transform:scale(1.03)}
    .img-cell img{width:100%;height:100%;object-fit:cover;display:block}
    .img-cell .img-remove{position:absolute;top:4px;right:4px;width:22px;height:22px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s}
    .img-cell:hover .img-remove{opacity:1}
    .img-cell .img-badge{position:absolute;bottom:4px;left:4px;background:rgba(45,106,79,0.85);color:#fff;font-size:9px;font-weight:600;padding:1px 6px;border-radius:4px}
    .img-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#F1F8E9,#E8F5E9);display:flex;align-items:center;justify-content:center}
    .img-placeholder i{color:#A5D6A7;font-size:24px}

    .upload-zone{border:2px dashed #C8E4D2;border-radius:14px;padding:28px 16px;text-align:center;cursor:pointer;transition:all .2s;background:#FAFFFE;margin-top:10px}
    .upload-zone:hover{border-color:#2D6A4F;background:#F1F8E9}
    .upload-zone.dragover{border-color:#2D6A4F;background:#E8F5E9;transform:scale(1.01)}
    .upload-zone i{font-size:28px;color:#95D5B2;margin-bottom:6px}
    .upload-zone p{margin:0;font-size:12px;color:#3A6B50;font-weight:500}
    .upload-zone small{font-size:10px;color:#7AAD90}

    .submit-bar{background:#fff;border:1px solid #E8F5E9;border-radius:16px;padding:16px 20px;display:flex;gap:10px}
    .btn-save{background:linear-gradient(135deg,#40916C,#2D6A4F);border:none;color:#fff;font-weight:700;padding:12px 32px;border-radius:12px;font-size:14px;transition:all .2s;flex:1}
    .btn-save:hover{background:linear-gradient(135deg,#2D6A4F,#1B4332);transform:translateY(-1px);box-shadow:0 4px 16px rgba(27,67,50,0.25)}
    .btn-cancel{border:1.5px solid #C8E4D2;background:#fff;color:#3A6B50;font-weight:600;padding:12px 24px;border-radius:12px;font-size:14px;text-decoration:none;transition:all .15s}
    .btn-cancel:hover{background:#F1F8E9;border-color:#2D6A4F;color:#1B4332}

    .lb-overlay{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.88);justify-content:center;align-items:center;animation:fadeIn .2s}
    .lb-overlay img{max-width:92vw;max-height:88vh;border-radius:10px;box-shadow:0 12px 48px rgba(0,0,0,0.5)}
    .lb-close{position:absolute;top:16px;right:20px;width:40px;height:40px;background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:50%;font-size:18px;cursor:pointer;transition:background .2s}
    .lb-close:hover{background:rgba(255,255,255,0.3)}
    .lb-nav{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:50%;font-size:18px;cursor:pointer;transition:background .2s}
    .lb-nav:hover{background:rgba(255,255,255,0.3)}
    .lb-nav.lb-prev{left:16px}.lb-nav.lb-next{right:16px}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
</style>

<div class="content-inner container-fluid pb-0">
    {{-- Back + Title --}}
    <div class="d-flex align-items-center mb-3 gap-3">
        <a href="{{ route('admin.items.index') }}" style="width:36px;height:36px;border-radius:10px;border:1.5px solid #DCEFE3;display:flex;align-items:center;justify-content:center;color:#3A6B50;text-decoration:none;transition:all .15s" onmouseover="this.style.borderColor='#2D6A4F';this.style.color='#1B4332'" onmouseout="this.style.borderColor='#DCEFE3';this.style.color='#3A6B50'"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h5 class="fw-bold mb-0" style="color:#1B4332;font-size:18px">Edit Item</h5>
            <small style="color:#7AAD90;font-size:12px">ID #{{ $item->id }} · {{ $item->item_name }}</small>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#FFF3E0;border:1px solid #FFCC80;border-radius:12px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-exclamation-triangle" style="color:#E65100;font-size:16px;margin-top:1px"></i>
        <div style="font-size:13px;color:#BF360C">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
    @endif

    {{-- Item Header --}}
    @php $imgs = is_array($item->images) ? $item->images : json_decode($item->images ?? '[]', true); @endphp
    @php $firstImg = !empty($imgs) ? (str_starts_with($imgs[0], 'http') ? $imgs[0] : asset('storage/' . $imgs[0])) : null; @endphp
    <div class="edit-header">
        @if($firstImg)
            <div class="item-badge" style="background:#F1F8E9;overflow:hidden">
                <img src="{{ $firstImg }}" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'fas fa-image\' style=\'color:#A5D6A7;font-size:24px\'></i>'">
            </div>
        @else
            <div class="item-badge" style="background:linear-gradient(135deg,#F1F8E9,#E8F5E9)">
                @if($item->is_veg)<i class="fas fa-leaf" style="color:#2D7A2D"></i>@else<i class="fas fa-drumstick-bite" style="color:#C62828"></i>@endif
            </div>
        @endif
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="veg-dot {{ $item->is_veg ? 'veg' : 'nonveg' }}"></span>
                <strong style="font-size:16px;color:#1B4332">{{ $item->item_name }}</strong>
                @if($item->is_featured)<span style="background:#FFF8E1;border:1px solid #FFE082;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700;color:#F57F17">★ Featured</span>@endif
            </div>
            <div style="font-size:12px;color:#7AAD90">
                {{ $item->owner?->restaurant_name ?? 'Unknown vendor' }} · {{ $item->category?->category_name ?? 'Uncategorized' }}
                @if($item->subcategory) · {{ $item->subcategory->name }}@endif
                · {{ $item->variants->count() }} variant(s)
                · <span style="color:{{ $item->status==='active'?'#2E7D32':'#757575' }};font-weight:600">{{ ucfirst($item->status) }}</span>
            </div>
        </div>
        @if(!empty($imgs))
        <button type="button" onclick="openLightbox('{{ addslashes($firstImg) }}')" style="background:#F1F8E9;border:1px solid #C5E1A5;border-radius:10px;padding:8px 14px;font-size:12px;font-weight:600;color:#2E7D32;cursor:pointer">
            <i class="fas fa-expand me-1"></i>View {{ count($imgs) }} image(s)
        </button>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.items.update',$item->id) }}" enctype="multipart/form-data" id="itemForm">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-8">
                {{-- Basic Info --}}
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-tag"></i> Basic Information</div>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="fg"><label>Vendor *</label><select name="shop_id" id="vendorSelect" required>@foreach($vendors as $v)<option value="{{ $v->shop_id }}" {{ old('shop_id',$item->shop_id)==$v->shop_id?'selected':'' }}>{{ $v->restaurant_name }}</option>@endforeach</select></div></div>
                        <div class="col-md-6"><div class="fg"><label>Category *</label><select name="category_id" id="categorySelect" required>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id',$item->category_id)==$c->id?'selected':'' }}>{{ $c->category_name }}</option>@endforeach</select></div></div>
                        <div class="col-md-6"><div class="fg"><label>Subcategory</label><select name="subcategory_id" id="subcategorySelect"><option value="">Select subcategory...</option>@if(isset($subcats))@foreach($subcats as $s)<option value="{{ $s->id }}" {{ old('subcategory_id',$item->subcategory_id??'')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach@endif</select></div></div>
                        <div class="col-md-6"><div class="fg"><label>Item Name *</label><input type="text" name="item_name" class="form-control" value="{{ old('item_name',$item->item_name) }}" required maxlength="150"></div></div>
                        <div class="col-12"><div class="fg"><label>Description</label><textarea name="description" class="form-control" rows="2" maxlength="2000" placeholder="Optional product description...">{{ old('description',$item->description??'') }}</textarea></div></div>
                        <div class="col-md-4"><div class="fg"><label>Status</label><select name="status" class="form-select"><option value="active" {{ old('status',$item->status)=='active'?'selected':'' }}>Active</option><option value="inactive" {{ old('status',$item->status)=='inactive'?'selected':'' }}>Inactive</option></select></div></div>
                        <div class="col-md-4"><div class="form-check form-switch" style="margin-top:28px"><input class="form-check-input" type="checkbox" name="is_veg" value="1" {{ old('is_veg',$item->is_veg)?'checked':'' }} id="vegCheck"><label class="form-check-label fw-semibold" for="vegCheck" style="font-size:13px">Vegetarian</label></div></div>
                        <div class="col-md-4"><div class="form-check form-switch" style="margin-top:28px"><input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured',$item->is_featured)?'checked':'' }} id="featCheck"><label class="form-check-label fw-semibold" for="featCheck" style="font-size:13px">Featured</label></div></div>
                    </div>
                </div>

                {{-- Variants --}}
                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #E8F5E9">
                        <div class="section-title mb-0" style="border:0;padding:0;margin:0"><i class="fas fa-coins"></i> Variants & Pricing</div>
                        <button type="button" class="btn btn-sm" id="addVariant" style="background:#E8F5E9;color:#2E7D32;border-radius:8px;font-weight:600"><i class="fas fa-plus me-1"></i>Add</button>
                    </div>
                    <div id="variantContainer">
                        @foreach($item->variants as $i => $v)
                        <div class="variant-box" data-idx="{{ $i }}">
                            <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $v->id }}">
                            <div class="vhead">
                                <span class="vnum">{{ $i===0?'DEFAULT':'#'.($i+1) }}</span>
                                <button type="button" class="del-variant remove-variant" {{ $item->variants->count()<=1?'disabled':'' }}><i class="fas fa-trash me-1"></i>Remove</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3"><div class="fg"><label>Label *</label><input type="text" name="variants[{{ $i }}][label]" class="form-control" value="{{ $v->label }}" required maxlength="100" placeholder="e.g. 500g"></div></div>
                                <div class="col-md-2"><div class="fg"><label>MRP ₹ *</label><input type="number" name="variants[{{ $i }}][price]" class="form-control price-input" step="0.01" min="0.01" value="{{ $v->price }}" required></div></div>
                                <div class="col-md-2"><div class="fg"><label>Sale ₹</label><input type="number" name="variants[{{ $i }}][offer_price]" class="form-control offer-input" step="0.01" min="0" value="{{ $v->offer_price }}" placeholder="Optional"></div></div>
                                <div class="col-md-2"><div class="fg"><label>GST % *</label><input type="number" name="variants[{{ $i }}][gst_percent]" class="form-control" step="0.01" min="0" max="100" value="{{ $v->gst_percent }}" required></div></div>
                                <div class="col-md-2"><div class="fg"><label>HSN</label><input type="text" name="variants[{{ $i }}][hsn_code]" class="form-control" value="{{ $v->hsn_code }}" maxlength="20" placeholder="—"></div></div>
                                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn del-variant remove-variant" style="font-size:14px;padding:6px" {{ $item->variants->count()<=1?'disabled':'' }}><i class="fas fa-trash"></i></button></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="price-preview-bar" id="pricePreview">
                        <span style="font-size:11px;color:#558B2F;font-weight:600">Price Preview</span>
                        <span class="pp" id="previewPrice">₹0</span>
                        <span class="op" id="previewOriginal" style="display:none">₹0</span>
                        <span class="disc" id="previewDiscount" style="display:none">0% off</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Images --}}
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-camera"></i> Product Images</div>
                    @if(!empty($imgs))
                    <div class="img-grid" id="existingImages">
                        @foreach($imgs as $idx => $img)
                        @php $imgUrl = str_starts_with($img, 'http') ? $img : asset('storage/' . $img); @endphp
                        <div class="img-cell" onclick="openLightbox('{{ addslashes($imgUrl) }}')">
                            <img src="{{ $imgUrl }}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=img-placeholder><i class=fas fa-image></i></div>'">
                            <span class="img-badge">{{ $idx===0?'Cover':'#'.($idx+1) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div id="existingImages"></div>
                    @endif
                    <div class="upload-zone" id="dropZone">
                        <i class="fas fa-cloud-upload-alt d-block"></i>
                        <p>Tap to add images</p>
                        <small>JPEG, PNG, WebP · Max 3MB each</small>
                        <input type="file" name="images[]" id="imageInput" accept="image/jpeg,image/png,image/webp" multiple style="display:none">
                    </div>
                    <div class="img-grid mt-2" id="newImagePreview"></div>
                    <div id="imageCount" style="font-size:11px;color:#7AAD90;margin-top:6px"></div>
                </div>

                {{-- Submit --}}
                <div class="submit-bar">
                    <button type="submit" class="btn-save"><i class="fas fa-check-circle me-1"></i> Save Changes</button>
                    <a href="{{ route('admin.items.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Lightbox --}}
<div class="lb-overlay" id="lightbox">
    <button class="lb-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <button class="lb-nav lb-prev" onclick="event.stopPropagation();navLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
    <img id="lbImg" src="" onclick="event.stopPropagation()">
    <button class="lb-nav lb-next" onclick="event.stopPropagation();navLightbox(1)"><i class="fas fa-chevron-right"></i></button>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function(){
    const allImages = {!! json_encode(array_map(function($img) {
        return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
    }, $imgs ?? [])) !!};
    let lbIdx = 0;

    window.openLightbox = function(src){
        lbIdx = allImages.indexOf(src);
        if(lbIdx < 0) lbIdx = 0;
        showLbImage();
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };
    window.closeLightbox = function(){
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    };
    window.navLightbox = function(dir){
        lbIdx = (lbIdx + dir + allImages.length) % allImages.length;
        showLbImage();
    };
    function showLbImage(){
        document.getElementById('lbImg').src = allImages[lbIdx];
        const navBtns = document.querySelectorAll('.lb-nav');
        navBtns.forEach(b => b.style.display = allImages.length <= 1 ? 'none' : 'flex');
    }
    document.getElementById('lightbox').addEventListener('click', closeLightbox);
    document.addEventListener('keydown', function(e){
        if(document.getElementById('lightbox').style.display !== 'flex') return;
        if(e.key === 'Escape') closeLightbox();
        if(e.key === 'ArrowLeft') navLightbox(-1);
        if(e.key === 'ArrowRight') navLightbox(1);
    });

    // ── TomSelect ───────────────────────────────────────
    let subcatTom = null;
    function loadSubcategories(catId, preselect){
        const sel = document.getElementById('subcategorySelect');
        if(subcatTom){subcatTom.destroy();subcatTom=null;}
        if(!catId){
            sel.innerHTML = '<option value="">Select subcategory...</option>';
            subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Search subcategory...',maxOptions:null});
            return;
        }
        sel.innerHTML = '<option value="">Loading...</option>';
        fetch('/admin/subcategories-by-cat/'+catId)
            .then(r=>r.json())
            .then(data=>{
                sel.innerHTML = '<option value="">Select subcategory...</option>';
                data.forEach(s=>{
                    const o=document.createElement('option');
                    o.value=s.id; o.textContent=s.name;
                    if(preselect && String(s.id)===String(preselect)) o.selected=true;
                    sel.appendChild(o);
                });
                subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Search subcategory...',maxOptions:null});
            })
            .catch(()=>{
                sel.innerHTML = '<option value="">Error loading</option>';
                subcatTom = new TomSelect('#subcategorySelect',{placeholder:'Select subcategory...',maxOptions:null});
            });
    }
    new TomSelect('#vendorSelect',{placeholder:'Search vendor...',maxOptions:null});
    new TomSelect('#categorySelect',{placeholder:'Search category...',maxOptions:null,onChange:function(v){loadSubcategories(v);}});
    loadSubcategories(document.getElementById('categorySelect').value,'{{ old("subcategory_id",$item->subcategory_id??"") }}');

    // ── Variants ────────────────────────────────────────
    let varCount = {{ $item->variants->count() }};
    document.getElementById('addVariant').addEventListener('click',function(){
        const d = document.createElement('div');
        d.className='variant-box'; d.dataset.idx=varCount;
        d.innerHTML=`<div class="vhead"><span class="vnum">#${varCount+1}</span><button type="button" class="del-variant remove-variant"><i class="fas fa-trash me-1"></i>Remove</button></div><div class="row g-2"><div class="col-md-3"><div class="fg"><label>Label *</label><input type="text" name="variants[${varCount}][label]" class="form-control" placeholder="e.g. 1kg" required maxlength="100"></div></div><div class="col-md-2"><div class="fg"><label>MRP ₹ *</label><input type="number" name="variants[${varCount}][price]" class="form-control price-input" step="0.01" min="0.01" required placeholder="0.00"></div></div><div class="col-md-2"><div class="fg"><label>Sale ₹</label><input type="number" name="variants[${varCount}][offer_price]" class="form-control offer-input" step="0.01" min="0" placeholder="Optional"></div></div><div class="col-md-2"><div class="fg"><label>GST % *</label><input type="number" name="variants[${varCount}][gst_percent]" class="form-control" step="0.01" min="0" max="100" value="5" required></div></div><div class="col-md-2"><div class="fg"><label>HSN</label><input type="text" name="variants[${varCount}][hsn_code]" class="form-control" maxlength="20" placeholder="—"></div></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn del-variant remove-variant" style="font-size:14px;padding:6px"><i class="fas fa-trash"></i></button></div></div>`;
        document.getElementById('variantContainer').appendChild(d);
        varCount++; updateRemoveButtons();
    });
    document.getElementById('variantContainer').addEventListener('click',function(e){
        const btn=e.target.closest('.remove-variant');
        if(!btn||btn.disabled) return;
        const card=btn.closest('.variant-box');
        if(card){card.remove();reindexVariants();updateRemoveButtons();}
    });
    function reindexVariants(){
        document.querySelectorAll('#variantContainer .variant-box').forEach((c,i)=>{
            c.dataset.idx=i;
            c.querySelector('.vnum').textContent=i===0?'DEFAULT':'#'+(i+1);
            c.querySelectorAll('input').forEach(el=>{if(el.name)el.name=el.name.replace(/variants\[\d+\]/,'variants['+i+']');});
        });
        varCount=document.querySelectorAll('#variantContainer .variant-box').length;
    }
    function updateRemoveButtons(){
        const n=document.querySelectorAll('#variantContainer .variant-box').length;
        document.querySelectorAll('.remove-variant').forEach(b=>{b.disabled=n<=1;b.title=n<=1?'Cannot remove':'Remove';});
    }

    // ── Price preview ───────────────────────────────────
    function updatePreview(){
        const c=document.querySelector('#variantContainer .variant-box');
        if(!c)return;
        const p=parseFloat(c.querySelector('.price-input')?.value)||0;
        const o=parseFloat(c.querySelector('.offer-input')?.value)||0;
        const bar=document.getElementById('pricePreview');
        if(p>0){
            bar.style.display='flex';bar.style.alignItems='center';
            document.getElementById('previewPrice').textContent='₹'+p.toFixed(0);
            if(o>0&&o<p){
                document.getElementById('previewOriginal').style.display='inline';
                document.getElementById('previewOriginal').textContent='₹'+p.toFixed(0);
                document.getElementById('previewDiscount').style.display='inline';
                document.getElementById('previewDiscount').textContent=Math.round((1-o/p)*100)+'% off';
                document.getElementById('previewPrice').textContent='₹'+o.toFixed(0);
            } else {
                document.getElementById('previewOriginal').style.display='none';
                document.getElementById('previewDiscount').style.display='none';
            }
        } else bar.style.display='none';
    }
    document.getElementById('variantContainer').addEventListener('input',function(e){
        if(e.target.classList.contains('price-input')||e.target.classList.contains('offer-input'))updatePreview();
    });
    updatePreview();

    // ── Image upload ────────────────────────────────────
    const drop=document.getElementById('dropZone');
    const inp=document.getElementById('imageInput');
    const prev=document.getElementById('newImagePreview');
    const cnt=document.getElementById('imageCount');
    let files=[];

    drop.addEventListener('click',()=>inp.click());
    drop.addEventListener('dragover',e=>{e.preventDefault();drop.classList.add('dragover');});
    drop.addEventListener('dragleave',()=>drop.classList.remove('dragover'));
    drop.addEventListener('drop',e=>{e.preventDefault();drop.classList.remove('dragover');addFiles(e.dataTransfer.files);});
    inp.addEventListener('change',()=>{addFiles(inp.files);inp.value='';});

    function addFiles(fl){
        for(const f of fl){
            if(!f.type.match(/image\/(jpeg|png|webp)/)){alert(f.name+': unsupported format');continue;}
            if(f.size>3*1024*1024){alert(f.name+': exceeds 3MB');continue;}
            files.push(f);
        }
        renderNew();
    }
    function renderNew(){
        prev.innerHTML='';
        files.forEach((f,i)=>{
            const d=document.createElement('div');d.className='img-cell';
            const img=document.createElement('img');
            const r=new FileReader();
            r.onload=e=>img.src=e.target.result;
            r.readAsDataURL(f);
            const b=document.createElement('button');b.type='button';b.className='img-remove';b.innerHTML='×';
            b.onclick=()=>{files.splice(i,1);renderNew();};
            d.appendChild(img);d.appendChild(b);prev.appendChild(d);
        });
        cnt.textContent=files.length?files.length+' new image(s)':'';
        const dt=new DataTransfer();
        files.forEach(f=>dt.items.add(f));
        inp.files=dt.files;
    }
})();
</script>
</x-app-layout>
