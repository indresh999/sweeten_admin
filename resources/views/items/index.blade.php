<x-app-layout :assets="$assets ?? []">
    <style>
        table thead tr th {
            text-transform: Capitalize;
            letter-spacing: 0.2px;
            background-color: #8fd893 !important;
        }

        @media (max-width: 768px) {

            table th,
            table td {
                font-size: 13px;
                padding: 6px;
            }
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
    <div class="container py-4">

        <div class="d-flex justify-content-between mb-3">
            <h3>Items</h3>
            <a href="{{ route('admin.items.create') }}" class="btn btn-primary">Add Item</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">

                <form method="GET" action="{{ route('admin.items.index') }}" class="row mb-3">

                    <div class="col-md-4">
                        <select name="shop_id" class="form-select" onchange="this.form.submit()">

                            <option value="">All Shops</option>

                            @foreach ($owners as $owner)
                                <option value="{{ $owner->shop_id }}"
                                    {{ request('shop_id') == $owner->shop_id ? 'selected' : '' }}>
                                    {{ $owner->restaurant_name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    @if (request('shop_id'))
                        <div class="col-md-2">
                            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    @endif

                </form>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-nowrap">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Owner</th>

                                <th>Base Price</th> {{-- NEW --}}
                                <th>Offer Price</th> {{-- NEW --}}
                                <th>Final Price</th> {{-- NEW --}}

                                <th>GST %</th>
                                <th>Commission</th>

                                <th>Stock</th> {{-- NEW --}}
                                <th>SKU</th> {{-- NEW --}}

                                <th>Status</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="text-center">

                            
                            @forelse($items as $item)

                            @php
    $price = $item->offer_price ?? $item->price;

    $commission = \App\Services\CommissionService::getCommissionDetails($item, $price);

    $commissionAmount = \App\Services\CommissionService::calculateCommission($price, $commission);

    $finalPrice = $price + $commissionAmount;
@endphp

                                @php
                                    $firstImage = $item->image_urls[0] ?? null;
                                @endphp

                                <tr>
                                    <td>{{ $item->id }}</td>

                                    <td>
                                        @if ($firstImage)
                                            <img src="{{ $firstImage }}" class="img-thumbnail"
                                                style="height:60px;width:60px;object-fit:cover;">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>

                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->category->category_name ?? '-' }}</td>
                                    <td>{{ $item->subcategory->name ?? '-' }}</td>
                                    <td>{{ $item->owner->restaurant_name ?? '-' }}</td>

                                    {{-- BASE PRICE --}}
                                    <td>₹{{ number_format($item->price, 2) }}</td>

                                    {{-- OFFER PRICE --}}
                                    <td>
                                        @if ($item->offer_price)
                                            ₹{{ number_format($item->offer_price, 2) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- FINAL PRICE --}}
                                    <td>
                                        <span class="fw-bold text-success">
                                            ₹{{ number_format($finalPrice, 2) }}
                                        </span>
                                    </td>

                                    {{-- GST --}}
                                    <td>{{ $item->gst_percent ?? '-' }}%</td>

                                    {{-- COMMISSION --}}
                                    <td>
                                        <span class="badge bg-success"
                                            title="Amount: ₹{{ round($commissionAmount, 2) }}">
                                            {{ $commission['type'] == 'percentage' ? $commission['value'] . '%' : '₹' . $commission['value'] }}
                                        </span>
                                    </td>

                                    {{-- STOCK --}}
                                    <td>
                                        @if ($item->track_inventory)
                                            {{ $item->stock_quantity ?? 0 }}
                                        @else
                                            <span class="text-muted">∞</span>
                                        @endif
                                    </td>

                                    {{-- SKU --}}
                                    <td>{{ $item->sku ?? '-' }}</td>

                                    {{-- STATUS --}}
                                    <td>
                                        <span class="badge bg-{{ $item->status ? 'success' : 'danger' }}">
                                            {{ $item->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td>
                                        <a href="{{ url('admin/item-commission/' . $item->id) }}"
                                            class="btn btn-sm btn-warning">
                                            ⚙
                                        </a>

                                        <a href="{{ route('admin.items.show', $item->id) }}"
                                            class="btn btn-sm btn-info mb-1">View</a>

                                        <a href="{{ route('admin.items.edit', $item->id) }}"
                                            class="btn btn-sm btn-warning mb-1">Edit</a>

                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-secondary">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-muted py-3">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
