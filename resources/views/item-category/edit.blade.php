<x-app-layout>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<div class="container py-4">

    <div class="card">
        <div class="card-header">
            <h4>Edit Category</h4>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.item-categories.update', $category->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Category Name</label>
                    <input type="text"
                           name="category_name"
                           class="form-control"
                           value="{{ $category->category_name }}"
                           required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description"
                              class="form-control">{{ $category->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $category->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$category->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Image -->
                <div class="mb-3">
                    <label>Category Image</label>
                    <input type="file" id="imageInput" name="image" class="form-control">
                </div>

                <!-- Preview -->
                <div class="mb-3">
                    <div style="width:200px;height:200px;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;">
                        <img id="preview"
                             src="{{ $category->image }}"
                             style="max-width:100%;{{ $category->image ? '' : 'display:none;' }}">
                    </div>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.item-categories.index') }}" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
let cropper;
const imageInput = document.getElementById('imageInput');
const preview = document.getElementById('preview');

imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        preview.src = reader.result;
        preview.style.display = 'block';

        if (cropper) cropper.destroy();

        cropper = new Cropper(preview, {
            aspectRatio: 1,
            viewMode: 2,
            autoCropArea: 1,
        });
    };
    reader.readAsDataURL(file);
});

document.querySelector('form').addEventListener('submit', function (e) {
    if (!cropper) return;

    e.preventDefault();

    cropper.getCroppedCanvas({
        width: 600,
        height: 600,
        imageSmoothingQuality: 'high'
    }).toBlob(blob => {
        const file = new File([blob], 'category.webp', { type: 'image/webp' });
        const dt = new DataTransfer();
        dt.items.add(file);
        imageInput.files = dt.files;
        this.submit();
    }, 'image/webp');
});
</script>
</x-app-layout>