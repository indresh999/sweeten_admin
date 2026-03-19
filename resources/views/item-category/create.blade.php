<x-app-layout :assets="$assets ?? []">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <div class="container py-4">

        <div class="card">
            <div class="card-header">
                <h4>Add Category</h4>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('admin.item-categories.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>


                     <!-- HSN Code -->
                    <div class="mb-3">
                        <label>HSN Code</label>
                        <input type="text" name="hsn" class="form-control"
                            placeholder="Enter HSN Code (e.g. 1905)">
                    </div>

                    <!-- Tax Percentage -->
                    <div class="mb-3">
                        <label>Tax (%)</label>
   
                        <select name="tax" class="form-select">
                            <option value="">Select Tax</option>
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18">18%</option>
                            <option value="28">28%</option>
                        </select>
                   

                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Category Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_birthday" value="1"
                                id="isBirthday">
                            <label class="form-check-label" for="isBirthday">
                                Birthday Category 🎂
                            </label>
                        </div>
                    </div>

                    <!-- Featured -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Featured</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                id="isFeatured">
                            <label class="form-check-label" for="isFeatured">
                                Make this category Featured ⭐
                            </label>
                        </div>
                    </div>

                   

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label>Category Image</label>
                        <input type="file" id="imageInput" name="image" class="form-control" accept="image/*">
                    </div>

                    <!-- Preview Box -->
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
                    responsive: true,
                });
            };
            reader.readAsDataURL(file);
        });

        // Convert cropped image to Blob before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!cropper) return;

            e.preventDefault();

            cropper.getCroppedCanvas({
                width: 700,
                height: 700,
                imageSmoothingQuality: 'high',
            }).toBlob(blob => {
                const fileInput = document.getElementById('imageInput');
                const file = new File([blob], 'category.webp', {
                    type: 'image/webp'
                });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                this.submit();
            }, 'image/webp');
        });
    </script>
</x-app-layout>
