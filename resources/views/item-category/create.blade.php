<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <div class="card">
        <div class="card-header"><h4>Add Category</h4></div>

        <div class="card-body">

            <form method="POST" action="{{ route('admin.item-categories.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Category Name</label>
                    <input type="text" name="category_name" class="form-control" required>
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

                <button class="btn btn-success">Save</button>

            </form>

        </div>
    </div>

</div>

</x-app-layout>