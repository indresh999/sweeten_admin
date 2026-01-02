<x-app-layout>
    <div class="container py-4">
        <h3>Edit Item</h3>

        <form method="POST" action="{{ route('admin.items.update', $item->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="card">
                <div class="card-body">
                    <div class="row">

                        {{-- OWNER --}}
                        <div class="col-md-6 mb-3">
                            <label>Owner</label>
                            <select name="owner_id" class="form-select">
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
                            <label>Category</label>
                            <select name="category_id" id="categorySelect" class="form-select">
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
                            <label>Subcategory</label>
                            <select name="subcategory_id" id="subcategorySelect" class="form-select">
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
                            <label>Item Name</label>
                            <input name="item_name" value="{{ $item->item_name }}" class="form-control">
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="col-md-12 mb-3">
                            <textarea name="description" class="form-control">{{ $item->description }}</textarea>
                        </div>

                        {{-- EXISTING IMAGES --}}
                        <div class="col-md-12 mb-3">
                            @foreach ($item->image_urls as $img)
                                <img src="{{ $img }}" width="90" class="rounded border me-2 mb-2">
                            @endforeach
                        </div>

                        {{-- ADD IMAGES --}}
                        <div class="col-md-12 mb-3">
                            <input type="file" name="images[]" class="form-control" multiple>
                        </div>

                    </div>

                    <hr>
                    <h5>Variants</h5>

                    <div id="variantsWrapper">
                        @foreach ($item->variants as $i => $v)
                            <div class="row mb-2 variant-row">
                                <input type="hidden" name="variants[{{ $i }}][id]"
                                    value="{{ $v->id }}">
                                <div class="col-md-3">
                                    <input name="variants[{{ $i }}][label]" value="{{ $v->label }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][price]" value="{{ $v->price }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][offer_price]"
                                        value="{{ $v->offer_price }}" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][gst_percent]"
                                        value="{{ $v->gst_percent }}" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <input name="variants[{{ $i }}][hsn_code]" value="{{ $v->hsn_code }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="this.closest('.variant-row').remove()">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-secondary" onclick="addVariantRow()">+ Add
                        Variant</button>

                    <br><br>
                    <button class="btn btn-success">Update Item</button>

                </div>
            </div>
        </form>
    </div>

    <script>
        let variantIndex = {{ $item->variants->count() }};

        function addVariantRow() {
            document.getElementById('variantsWrapper')
                .insertAdjacentHTML('beforeend', `
                    <div class="row mb-2 variant-row">
                    <div class="col-md-3"><input name="variants[${variantIndex}][label]" class="form-control"></div>
                    <div class="col-md-2"><input name="variants[${variantIndex}][price]" class="form-control"></div>
                    <div class="col-md-2"><input name="variants[${variantIndex}][offer_price]" class="form-control"></div>
                    <div class="col-md-2"><input name="variants[${variantIndex}][gst_percent]" class="form-control"></div>
                    <div class="col-md-2"><input name="variants[${variantIndex}][hsn_code]" class="form-control"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.variant-row').remove()">✕</button></div>
                    </div>`);
            variantIndex++;
        }
    </script>
</x-app-layout>
