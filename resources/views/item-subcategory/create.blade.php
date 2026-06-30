<x-app-layout>
@php $sub = null; @endphp

    <!-- ================= CSS ================= -->

    <!-- Cropper -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"/>

   


    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <h4>Add Subcategory</h4>
            </div>

            <div class="card-body">
                <form id="subcategoryForm"
                      method="POST"
                      action="{{ route('admin.item-subcategories.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id"
                                id="categorySelect"
                                class="form-select"
                                required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Parent Subcategory -->
                    <div class="mb-3">
                        <label class="form-label">Parent Subcategory (Optional)</label>
                        <select name="parent_id"
                                id="parentSelect"
                                class="form-select">
                            <option value="">None</option>
                        </select>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               required>
                    </div>

                    <!-- HSN -->
                    <div class="mb-3">
                        <label class="form-label">HSN Code</label>
                        <input type="text"
                               name="hsn"
                               id="hsnInput"
                               class="form-control">
                    </div>

                    <!-- Tax -->
                    <div class="mb-3">
                        <label class="form-label">Tax (%)</label>
                        <select name="tax"
                                id="taxSelect"
                                class="form-select">
                            <option value="">Select Tax</option>
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18">18%</option>
                            <option value="28">28%</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"></textarea>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status"
                                class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file"
                               id="imageInput"
                               name="image"
                               class="form-control">
                    </div>

                    <!-- Preview -->
                    <div class="mb-3">
                        <div style="width:200px;height:200px;border:1px dashed #ccc;
                                    display:flex;align-items:center;justify-content:center;">
                            <img id="preview"
                                 style="max-width:100%;display:none;">
                        </div>
                    </div>

                    <button class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>



    <!-- ================= JS ================= -->

    <!-- jQuery (Required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



    <!-- Cropper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>


    <script>
        $(document).ready(function () {

    

            /* ===== Category Change Logic ===== */
            $('#categorySelect').on('change', function () {

                let categoryId = $(this).val();

                // Clear parent dropdown
                $('#parentSelect').html('<option value="">Loading...</option>');

                if (!categoryId) {
                    $('#parentSelect').html('<option value="">None</option>');
                    return;
                }

                // Auto fill HSN & Tax
                fetch('/admin/categories/' + categoryId + '/tax-hsn')

                    .then(response => response.json())
                    .then(data => {
                        $('#hsnInput').val(data.hsn ?? '');
                        $('#taxSelect').val(data.tax ?? '');
                    });
                // Load Parent Subcategories
               fetch('/admin/categories/' + categoryId + '/subcategory-parents')
                    .then(response => response.json())
                    .then(data => {

                        let options = '<option value="">None</option>';

                        data.forEach(function (sub) {
                            options += `<option value="${sub.id}">${sub.name}</option>`;
                        });

                        $('#parentSelect').html(options);
                    });

            });


            /* ===== Cropper ===== */
            let cropper;
            const imageInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const form = document.getElementById('subcategoryForm');

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

            form.addEventListener('submit', function (e) {

                if (!cropper) return;

                e.preventDefault();

                cropper.getCroppedCanvas({
                    width: 700,
                    height: 700,
                }).toBlob(blob => {

                    const file = new File([blob], 'subcategory.webp', {
                        type: 'image/webp'
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    imageInput.files = dataTransfer.files;

                    form.submit();
                }, 'image/webp');
            });

        });
    </script>

</x-app-layout>