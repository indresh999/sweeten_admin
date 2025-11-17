<x-app-layout :assets="$assets ?? []">
<div class="container py-4">
    <h3>Add Item</h3>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Owner (Shop)</label>
                        <select name="owner_id" class="form-select" required>
                            <option value="">Select owner</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->shop_id }}" {{ old('owner_id') == $o->shop_id ? 'selected' : '' }}>
                                    {{ $o->restaurant_name }} ({{ $o->full_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Item Name</label>
                        <input type="text" name="item_name" class="form-control" value="{{ old('item_name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Offer Price</label>
                        <input type="number" step="0.01" name="offer_price" class="form-control" value="{{ old('offer_price') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>GST Percent</label>
                        <input type="number" step="0.01" name="gst_percent" class="form-control" value="{{ old('gst_percent') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Min Quantity</label>
                        <input type="number" name="min_quantity" class="form-control" value="{{ old('min_quantity') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Weight / Piece</label>
                        <input type="text" name="weight_or_piece" class="form-control" value="{{ old('weight_or_piece') }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Images (you can select multiple)</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-success">Save Item</button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>