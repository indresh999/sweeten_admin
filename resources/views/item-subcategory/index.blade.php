<x-app-layout :assets="$assets ?? []">
    <style>
        table thead tr th {
            text-transform: Capitalize;
            letter-spacing: 0.2px;
            background-color: #8fd893 !important;
        }
    </style>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.item-subcategories.create') }}" class="btn btn-primary mb-3">Add Sub Category</a>

                <form method="GET" action="{{ route('admin.item-subcategories.index') }}" class="row mb-3">

                    <div class="col-md-4">
                        <select name="category_id" class="form-select" onchange="this.form.submit()">

                            <option value="">All Categories</option>

                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->category_name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </form>

                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Tax (%)</th>
                            <th>HSN</th>
                            <th>Commision</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($subcategories as $sub)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($sub->image)
                                        <img src="{{ $sub->image }}" style="width:60px;height:60px;object-fit:cover;">
                                    @endif
                                </td>
                                <td>{{ $sub->name }}</td>
                                <td>{{ $sub->category->category_name }}</td>

                                <td>{{ $sub->tax ?? '0' }}%</td>
                                <td>{{ $sub->hsn ?? '-' }}</td>

                                <td>
                                    @php
                                        $commission = \App\Services\CommissionService::getCommissionDetails($sub);
                                    @endphp

                                    <span
                                        class="badge 
        {{ $commission['source'] == 'Item'
            ? 'bg-success'
            : ($commission['source'] == 'Subcategory'
                ? 'bg-warning'
                : ($commission['source'] == 'Category'
                    ? 'bg-primary'
                    : ($commission['source'] == 'Rule'
                        ? 'bg-dark'
                        : 'bg-secondary'))) }}"
                                        data-bs-toggle="tooltip" title="Source: {{ $commission['source'] }}">

                                        {{ $commission['type'] == 'percentage' ? $commission['value'] . '%' : '₹' . $commission['value'] }}

                                        ({{ $commission['source'] }})
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sub->status ? 'success' : 'danger' }}">
                                        {{ $sub->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ url('admin/subcategory-commission/' . $sub->id) }}"
                                        class="btn btn-sm btn-info">
                                        Set Commission
                                    </a>
                                    <a href="{{ route('admin.item-subcategories.edit', $sub->id) }}"
                                        class="btn btn-primary btn-sm">Edit</a>

                                    <form method="POST"
                                        action="{{ route('admin.item-subcategories.destroy', $sub->id) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this subcategory?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-primary btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{ $subcategories->links('pagination::bootstrap-5') }}

</x-app-layout>
