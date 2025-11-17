<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">

        <a href="{{ route('admin.item-categories.create') }}" class="btn btn-primary mb-3">Add Category</a>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td>{{ $cat->id }}</td>
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
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-warning btn-sm">
                                            {{ $cat->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $categories->links('pagination::bootstrap-5') }}

            </div>
        </div>

    </div>

</x-app-layout>
