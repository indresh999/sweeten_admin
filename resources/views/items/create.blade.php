<x-app-layout>
<div class="container py-4">
    <h3>Add Item</h3>

    <form method="POST"
          action="{{ route('admin.items.store') }}"
          enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-body">
                <div class="row">

                    {{-- OWNER --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Owner (Shop)</label>
                        <select name="owner_id" class="form-select" required>
                            <option value="">Select Owner</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->shop_id }}">
                                    {{ $o->restaurant_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id"
                                id="categorySelect"
                                class="form-select"
                                required>
                            <option value="">Select Category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SUBCATEGORY --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Subcategory</label>
                        <select name="subcategory_id"
                                id="subcategorySelect"
                                class="form-select"
                                required>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>

                    {{-- ITEM NAME --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Item Name</label>
                        <input type="text"
                               name="item_name"
                               class="form-control"
                               placeholder="Eg. Basmati Rice"
                               required>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold">Description</label>
                        <textarea name="description"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Short product description"></textarea>
                    </div>

                    {{-- IMAGES --}}
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold">Images</label>
                        <input type="file"
                               name="images[]"
                               class="form-control"
                               multiple
                               accept="image/*"
                               onchange="previewImages(event)">
                    </div>

                    <div class="col-md-12 mb-3 d-flex gap-2 flex-wrap"
                         id="previewBox"></div>

                    {{-- STATUS --}}
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>

                <hr>

                {{-- VARIANTS --}}
                <h5 class="mb-3">Variants</h5>

                {{-- HEADER --}}
                <div class="row fw-bold text-muted mb-1">
                    <div class="col-md-3">Label</div>
                    <div class="col-md-2">Price</div>
                    <div class="col-md-2">Offer</div>
                    <div class="col-md-2">GST %</div>
                    <div class="col-md-2">HSN</div>
                    <div class="col-md-1 text-center">Default</div>
                </div>

                <div id="variantsWrapper">

                    {{-- DEFAULT VARIANT --}}
                    <div class="row mb-2 variant-row align-items-center">
                        <div class="col-md-3">
                            <input name="variants[0][label]"
                                   class="form-control"
                                   placeholder="250 g / 1 Kg"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input name="variants[0][price]"
                                   type="number"
                                   step="0.01"
                                   class="form-control"
                                   placeholder="Price"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input name="variants[0][offer_price]"
                                   type="number"
                                   step="0.01"
                                   class="form-control"
                                   placeholder="Offer">
                        </div>
                        <div class="col-md-2">
                            <input name="variants[0][gst_percent]"
                                   type="number"
                                   step="0.01"
                                   class="form-control"
                                   placeholder="GST"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input name="variants[0][hsn_code]"
                                   class="form-control"
                                   placeholder="HSN">
                        </div>
                        <div class="col-md-1 text-center">
                            <input type="radio"
                                   name="default_variant"
                                   checked
                                   disabled>
                            <input type="hidden"
                                   name="variants[0][is_default]"
                                   value="1">
                        </div>
                    </div>

                </div>

                <button type="button"
                        class="btn btn-outline-primary mb-3"
                        onclick="addVariantRow()">
                    + Add Variant
                </button>

                <br>

                <button class="btn btn-success">
                    Save Item
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ================= SCRIPTS ================= --}}
<script>
/* SUBCATEGORY LOAD */
document.getElementById('categorySelect')
.addEventListener('change', function () {
    const id = this.value;
    const sub = document.getElementById('subcategorySelect');
    sub.innerHTML = '<option>Loading...</option>';

    fetch(`/admin/get-subcategories/${id}`)
        .then(r => r.json())
        .then(d => {
            sub.innerHTML = '<option value="">Select Subcategory</option>';
            d.forEach(s => {
                sub.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });
});

/* IMAGE PREVIEW */
function previewImages(e) {
    const box = document.getElementById('previewBox');
    box.innerHTML = '';
    [...e.target.files].forEach(f => {
        const r = new FileReader();
        r.onload = ev => {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.className = 'rounded border';
            box.appendChild(img);
        };
        r.readAsDataURL(f);
    });
}

/* ADD VARIANT */
let variantIndex = 1;
function addVariantRow() {
    document.getElementById('variantsWrapper')
    .insertAdjacentHTML('beforeend', `
    <div class="row mb-2 variant-row align-items-center">
        <div class="col-md-3">
            <input name="variants[${variantIndex}][label]"
                   class="form-control"
                   placeholder="Label"
                   required>
        </div>
        <div class="col-md-2">
            <input name="variants[${variantIndex}][price]"
                   type="number"
                   step="0.01"
                   class="form-control"
                   required>
        </div>
        <div class="col-md-2">
            <input name="variants[${variantIndex}][offer_price]"
                   type="number"
                   step="0.01"
                   class="form-control">
        </div>
        <div class="col-md-2">
            <input name="variants[${variantIndex}][gst_percent]"
                   type="number"
                   step="0.01"
                   class="form-control"
                   required>
        </div>
        <div class="col-md-2">
            <input name="variants[${variantIndex}][hsn_code]"
                   class="form-control">
        </div>
        <div class="col-md-1 text-center">
            <button type="button"
                    class="btn btn-sm btn-danger"
                    onclick="this.closest('.variant-row').remove()">✕</button>
        </div>
    </div>`);
    variantIndex++;
}
</script>
</x-app-layout>