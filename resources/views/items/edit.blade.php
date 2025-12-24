<x-app-layout :assets="$assets ?? []">
<div class="container py-4">
    <h3>Edit Item - #{{ $item->id }}</h3>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.items.update', $item->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row">

                    {{-- OWNER --}}
                    <div class="col-md-6 mb-3">
                        <label>Owner (Shop)</label>
                        <select name="owner_id" class="form-select" required>
                            @foreach($owners as $o)
                                <option value="{{ $o->shop_id }}"
                                    {{ old('owner_id', $item->shop_id) == $o->shop_id ? 'selected' : '' }}>
                                    {{ $o->restaurant_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('category_id', $item->category_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SUBCATEGORY --}}
                    <div class="col-md-6 mb-3">
                        <label>Subcategory</label>
                        <select name="subcategory_id" id="subcategorySelect" class="form-select" required>
                            @foreach($subcategories as $sub)
                                <option value="{{ $sub->id }}"
                                    {{ old('subcategory_id', $item->subcategory_id) == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ITEM NAME --}}
                    <div class="col-md-6 mb-3">
                        <label>Item Name</label>
                        <input type="text"
                               name="item_name"
                               class="form-control"
                               value="{{ old('item_name', $item->item_name) }}"
                               required>
                    </div>

                    {{-- PRICE --}}
                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01"
                               name="price"
                               class="form-control"
                               value="{{ old('price', $item->price) }}"
                               required>
                    </div>

                    {{-- OFFER PRICE --}}
                    <div class="col-md-6 mb-3">
                        <label>Offer Price</label>
                        <input type="number" step="0.01"
                               name="offer_price"
                               class="form-control"
                               value="{{ old('offer_price', $item->offer_price) }}">
                    </div>

                    {{-- GST --}}
                    <div class="col-md-6 mb-3">
                        <label>GST %</label>
                        <input type="number" step="0.01"
                               name="gst_percent"
                               class="form-control"
                               value="{{ old('gst_percent', $item->gst_percent) }}">
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description"
                                  class="form-control">{{ old('description', $item->description) }}</textarea>
                    </div>

                    {{-- EXISTING IMAGES --}}
                    <div class="col-md-12 mb-3">
                        <label>Existing Images</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($item->images ?? [] as $img)
                                <div class="text-center">
                                    <img src="{{ asset($img) }}"
                                         class="border rounded mb-1"
                                         style="height:90px;width:120px;object-fit:cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ADD NEW IMAGES --}}
                    <div class="col-md-12 mb-3">
                        <label>Add Images</label>
                        <input type="file"
                               name="images[]"
                               class="form-control"
                               multiple
                               accept="image/*">
                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ $item->status ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$item->status ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <button class="btn btn-success">Update Item</button>
            </form>
        </div>
    </div>
</div>

{{-- AJAX SUBCATEGORY LOAD --}}
<script>
document.getElementById('categorySelect').addEventListener('change', function () {
    const categoryId = this.value;
    const subSelect = document.getElementById('subcategorySelect');

    subSelect.innerHTML = '<option>Loading...</option>';

    fetch(`/admin/get-subcategories/${categoryId}`)
        .then(res => res.json())
        .then(data => {
            subSelect.innerHTML = '';
            data.forEach(sub => {
                subSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
            });
        });
});
</script>
</x-app-layout>