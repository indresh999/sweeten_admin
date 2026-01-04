<x-app-layout>
    <div class="container py-4">
        <h3>Edit Item</h3>

        <form method="POST" action="{{ route('admin.items.update', $item->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-body">
                    <div class="row">

                        {{-- OWNER --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Owner (Shop)</label>
                            <select name="owner_id" class="form-select" required>
                                @foreach ($owners as $o)
                                    <option value="{{ $o->shop_id }}"
                                        {{ $item->shop_id == $o->shop_id ? 'selected' : '' }}>
                                        {{ $o->restaurant_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- CATEGORY --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Category</label>
                            <select name="category_id" id="categorySelect" class="form-select" required>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $item->category_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SUBCATEGORY --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Subcategory</label>
                            <select name="subcategory_id" id="subcategorySelect" class="form-select" required>
                                @foreach ($subcategories as $s)
                                    <option value="{{ $s->id }}"
                                        {{ $item->subcategory_id == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ITEM NAME --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Item Name</label>
                            <input name="item_name" value="{{ $item->item_name }}" class="form-control" required>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ $item->description }}</textarea>
                        </div>

                        {{-- EXISTING IMAGES --}}
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Existing Images</label><br>
                            @foreach ($item->image_urls as $img)
                                <img src="{{ $img }}" width="100" class="rounded border me-2 mb-2">
                            @endforeach
                        </div>

                        {{-- ADD NEW IMAGES --}}
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Add Images</label>
                            <input type="file" name="images[]" class="form-control" multiple>
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
                        @foreach ($item->variants as $i => $v)
                            <div class="row mb-2 variant-row align-items-center">

                                {{-- VARIANT ID --}}
                                <input type="hidden" name="variants[{{ $i }}][id]"
                                    value="{{ $v->id }}">

                                <div class="col-md-3">
                                    <input name="variants[{{ $i }}][label]" value="{{ $v->label }}"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][price]" value="{{ $v->price }}"
                                        type="number" step="0.01" class="form-control" required>
                                </div>

                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][offer_price]"
                                        value="{{ $v->offer_price }}" type="number" step="0.01"
                                        class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][gst_percent]"
                                        value="{{ $v->gst_percent }}" type="number" step="0.01"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][hsn_code]" value="{{ $v->hsn_code }}"
                                        class="form-control">
                                </div>

                                <div class="col-md-1 text-center">
                                    @if ($v->is_default)
                                        <span class="badge bg-success">Default</span>
                                        <input type="hidden" name="variants[{{ $i }}][is_default]"
                                            value="1">
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="this.closest('.variant-row').remove()">✕</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-primary mt-2" onclick="addVariantRow()">
                        + Add Variant
                    </button>

                    <br><br>

                    <button class="btn btn-success">
                        Update Item
                    </button>

                </div>
            </div>
        </form>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        let variantIndex = {{ $item->variants->count() }};

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
