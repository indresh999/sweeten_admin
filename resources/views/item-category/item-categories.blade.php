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
                <form method="GET" action="{{ route('admin.item-categories.index') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="category_type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="birthday" {{ request('category_type') == 'birthday' ? 'selected' : '' }}>
                                Birthday Categories 🎂
                            </option>
                            <option value="normal" {{ request('category_type') == 'normal' ? 'selected' : '' }}>
                                Normal Categories
                            </option>
                        </select>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Tax (%)</th>
                                <th>HSN Code</th>
                                <th>Description</th>
                                <th>Category Type</th>
                                <th>Status</th>
                                <th>Commission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($categories as $cat)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        @if ($cat->image)
                                            <img src="{{ $cat->image }}" alt="{{ $cat->category_name }}"
                                                style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>

                                    <td>{{ $cat->category_name }}</td>
                                    <td>{{ $cat->tax ?? '0' }}%</td>
                                    <td>{{ $cat->hsn ?? '-' }}</td>
                                    <td
                                        style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $cat->description }}
                                    </td>

                                    <td>
                                        @if ($cat->category_type === 'birthday')
                                            <span class="badge bg-info">Birthday 🎂</span>
                                        @else
                                            <span class="badge bg-secondary">Normal</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $cat->status ? 'success' : 'danger' }}">
                                            {{ $cat->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <td>
                                        @php
                                            $commission = \App\Services\CommissionService::getCommissionDetails($cat);
                                        @endphp

                                        <span
                                            class="badge 
        {{ $commission['source'] == 'Category'
            ? 'bg-success'
            : ($commission['source'] == 'Rule'
                ? 'bg-dark'
                : 'bg-secondary') }}"
                                            data-bs-toggle="tooltip" title="Source: {{ $commission['source'] }}">

                                            {{ $commission['type'] == 'percentage' ? $commission['value'] . '%' : '₹' . $commission['value'] }}

                                            ({{ $commission['source'] }})
                                        </span>
                                    </td>

                                    <td>


                                        <a href="{{ url('admin/category-commission/' . $cat->id) }}"
                                            class="btn btn-sm btn-primary">
                                            Set Commission
                                        </a>

                                        <a href="{{ route('admin.item-categories.edit', $cat->id) }}"
                                            class="btn btn-primary btn-sm">Edit</a>

                                        <form action="{{ route('admin.item-categories.destroy', $cat->id) }}"
                                            method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-primary btn-sm">
                                                {{ $cat->status ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $categories->links('pagination::bootstrap-5') }}

            </div>
        </div>

    </div>

</x-app-layout>
