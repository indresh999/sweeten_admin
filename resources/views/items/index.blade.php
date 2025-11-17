<x-app-layout :assets="$assets ?? []">
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
            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>Price</th>
                        <th>GST %</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php $firstImage = $item->images ? json_decode($item->images, true)[0] ?? null : null; @endphp
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td class="text-center" style="width:120px;">
                                @if($firstImage)
                                    <img src="{{ asset('uploads/items/'.$firstImage) }}" style="height:60px;">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->category->category_name ?? '-' }}</td>
                            <td>{{ $item->owner->restaurant_name ?? '-' }}</td>
                            <td>₹{{ number_format($item->price,2) }}</td>
                            <td>{{ $item->gst_percent ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.items.show', $item->id) }}" class="btn btn-sm btn-info mb-1">View</a>
                                <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>

                                <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-secondary mb-1">
                                        {{ $item->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No items found.</td></tr>
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