<x-app-layout>
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
                            <option value="">Select Owner</option>
                            @foreach($owners as $o)
                                <option value="{{ $o->shop_id }}">
                                    {{ $o->restaurant_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Sub Category</label>
                        <select name="subcategory_id" id="subcategorySelect" class="form-select" required>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Item Name</label>
                        <input type="text" name="item_name" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Offer Price</label>
                        <input type="number" step="0.01" name="offer_price" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*"
                               onchange="previewImages(event)">
                    </div>

                    <div class="col-md-12 mb-3 d-flex gap-2 flex-wrap" id="previewBox"></div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <button class="btn btn-success">Save Item</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('categorySelect').addEventListener('change', function () {
    const categoryId = this.value;
    const subSelect = document.getElementById('subcategorySelect');

    subSelect.innerHTML = '<option>Loading...</option>';

    if (!categoryId) {
        subSelect.innerHTML = '<option value="">Select Subcategory</option>';
        return;
    }

    fetch(`/admin/get-subcategories/${categoryId}`)
        .then(res => res.json())
        .then(data => {
            subSelect.innerHTML = '<option value="">Select Subcategory</option>';
            data.forEach(sub => {
                subSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
            });
        });
});

function previewImages(event) {
    const box = document.getElementById('previewBox');
    box.innerHTML = '';

    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.border = '1px solid #ccc';
            box.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
</x-app-layout>