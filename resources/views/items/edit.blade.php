<x-app-layout :assets="$assets ?? []">
<div class="container py-4">
    <h3>Edit Item - #{{ $item->id }}</h3>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Owner (Shop)</label>
                        <select name="owner_id" class="form-select" required>
                            <option value="">Select owner</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->shop_id }}" {{ old('owner_id',$item->owner_id) == $o->shop_id ? 'selected' : '' }}>
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
                                <option value="{{ $c->id }}" {{ old('category_id',$item->category_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Item Name</label>
                        <input type="text" name="item_name" class="form-control" value="{{ old('item_name',$item->item_name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price',$item->price) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Offer Price</label>
                        <input type="number" step="0.01" name="offer_price" class="form-control" value="{{ old('offer_price',$item->offer_price) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>GST Percent</label>
                        <input type="number" step="0.01" name="gst_percent" class="form-control" value="{{ old('gst_percent',$item->gst_percent) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Min Quantity</label>
                        <input type="number" name="min_quantity" class="form-control" value="{{ old('min_quantity',$item->min_quantity) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Weight / Piece</label>
                        <input type="text" name="weight_or_piece" class="form-control" value="{{ old('weight_or_piece',$item->weight_or_piece) }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description',$item->description) }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Existing Images</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($images as $img)
                                <div class="position-relative" style="width:120px;">
                                    <img src="{{ asset('uploads/items/'.$img) }}" class="img-fluid border" style="height:90px;">
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_images[]" value="{{ $img }}" id="rm_{{ $img }}">
                                        <label for="rm_{{ $img }}">Remove</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Add Images (multiple)</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $item->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $item->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <button class="btn btn-success">Update Item</button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>