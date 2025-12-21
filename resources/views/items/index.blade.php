<x-app-layout :assets="$assets ?? []">
        <style>
        table thead tr th {
            text-transform: Capitalize;
            letter-spacing: 0.2px;
            background-color: #8fd893 !important;
        }
    </style>
<div class="container py-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>Items</h3>
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary">Add Item</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">

            <form method="GET"
      action="{{ route('admin.items.index') }}"
      class="row mb-3">

    <div class="col-md-4">
        <select name="shop_id"
                class="form-select"
                onchange="this.form.submit()">

            <option value="">All Shops</option>

            @foreach ($owners as $owner)
                <option value="{{ $owner->shop_id }}"
                    {{ request('shop_id') == $owner->shop_id ? 'selected' : '' }}>
                    {{ $owner->restaurant_name }}
                </option>
            @endforeach

        </select>
    </div>

    @if(request('shop_id'))
        <div class="col-md-2">
            <a href="{{ route('admin.items.index') }}"
               class="btn btn-secondary">
                Reset
            </a>
        </div>
    @endif

</form>

            <table class="table table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Subcategory</th> <!-- NEW -->
                        <th>Owner</th>
                        <th>Price</th>
                        <th>GST %</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody class="text-center">
                    @forelse($items as $item)

                        @php
                            // Extract first image from JSON
                            $firstImage = null;
                            if ($item->images) {
                                $imgArr = json_decode($item->images, true);
                                $firstImage = $imgArr[0] ?? null;
                            }
                        @endphp

                        <tr>
                            <td>{{ $item->id }}</td>

                            <td>
                                @if($firstImage)
                                    <img src="{{ asset('uploads/items/'.$firstImage) }}" class="img-thumbnail" style="height:60px;width:60px;object-fit:cover;">
                                @else
                                    <span class="text-muted small">No Image</span>
                                @endif
                            </td>

                            <td>{{ $item->item_name }}</td>

                            <td>{{ $item->category->category_name ?? '-' }}</td>

                            <td>{{ $item->subcategory->name ?? '-' }}</td> <!-- NEW -->

                            <td>{{ $item->owner->restaurant_name ?? '-' }}</td>

                            <td>₹{{ number_format($item->price, 2) }}</td>

                            <td>{{ $item->gst_percent ?? '-' }}</td>

                            <td>
                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="text-center">

                                <a href="{{ route('admin.items.show', $item->id) }}"
                                   class="btn btn-sm btn-info mb-1 w-100">
                                    View
                                </a>

                                <a href="{{ route('admin.items.edit', $item->id) }}"
                                   class="btn btn-sm btn-warning mb-1 w-100">
                                    Edit
                                </a>

                                <form action="{{ route('admin.items.destroy', $item->id) }}"
                                      method="POST"
                                      class="d-inline w-100">
                                    @csrf @method('DELETE')

                                    <button class="btn btn-sm btn-secondary w-100">
                                        {{ $item->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">
                                No items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
</x-app-layout>