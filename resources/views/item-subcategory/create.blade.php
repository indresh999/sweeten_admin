<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <h4>Add Subcategory</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.item-subcategories.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" id="imageInput" name="image" class="form-control">
                    </div>

                    <div class="mb-3">
                        <div
                            style="width:200px;height:200px;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;">
                            <img id="preview" style="max-width:100%;display:none;">
                        </div>
                    </div>

                    <button class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
</x-app-layout>
