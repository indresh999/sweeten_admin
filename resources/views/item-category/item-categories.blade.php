<x-app-layout :assets="$assets ?? []">
    <style>
        table thead tr th {
            text-transform: Capitalize;
            letter-spacing: 0.2px;
            background-color: #8fd893 !important;
        }
    </style>
    <div class="container py-4">

        <a href="{{ route('admin.item-categories.create') }}" class="btn btn-primary mb-3">Add Category</a>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td>{{ $cat->id }}</td>

                                <!-- Image -->
                                <td>
                                    @if ($cat->image)
                                        <img src="{{ $cat->image }}" alt="{{ $cat->category_name }}"
                                            style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>

                                <td>{{ $cat->category_name }}</td>
                                <td>{{ $cat->description }}</td>

                                <td>
                                    <span class="badge bg-{{ $cat->status ? 'success' : 'danger' }}">
                                        {{ $cat->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.item-categories.edit', $cat->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('admin.item-categories.destroy', $cat->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to {{ $cat->status ? 'deactivate' : 'activate' }} this category?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-secondary btn-sm">
                                            {{ $cat->status ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </tbody>
                </table>

                {{ $categories->links('pagination::bootstrap-5') }}

            </div>
        </div>

    </div>

</x-app-layout>
