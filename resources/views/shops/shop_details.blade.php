<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">

        <!-- PAGE TITLE -->
        <h2 class="mb-4 fw-bold">Shop Details</h2>

        <div class="row">
            <!-- LEFT: SHOP DETAILS -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">

                        <!-- MAIN SHOP IMAGE -->
                        <img src="{{ $shop->images->first()->image_path ?? 'https://via.placeholder.com/200x200?text=No+Image' }}"
                            class="img-fluid rounded mb-3" style="max-height:180px; object-fit:cover;">

                        <h4 class="fw-bold">{{ $shop->restaurant_name }}</h4>
                        <p class="text-muted">{{ $shop->restaurant_address }}</p>

                        <p><strong>Phone:</strong> {{ $shop->phone_number ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $shop->email ?? 'N/A' }}</p>

                        <p class="mt-3">
                            <span
                                class="badge bg-{{ $shop->status === 'active' ? 'success' : 'warning' }} text-uppercase px-3 py-2">
                                {{ ucfirst($shop->status) }}
                            </span>
                        </p>

                        <a href="#" class="btn btn-primary w-100 mt-3">
                            Edit Shop
                        </a>
                    </div>
                </div>

                <!-- SHOP IMAGES LIST -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <strong>Shop Images</strong>
                    </div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        @forelse($shop->images as $img)
                            <img src="{{ asset($img->image_path) }}" style="width:90px; height:90px; object-fit:cover;"
                                class="rounded border">
                        @empty
                            <p class="text-muted">No Images Found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- RIGHT: ORDERS + ITEMS -->
            <div class="col-lg-8">

                <!-- ORDERS SECTION -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between">
                        <h5 class="mb-0">Orders</h5>
                        <span class="badge bg-light text-dark">{{ count($orders) }} Total</span>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Placed</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->user->full_name }}</td>
                                        <td>₹{{ number_format($order->final_amount, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'info') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('d M, h:i A') }}</td>

                                        <td>
                                            <a href="{{ route('admin.delivery.order.details', $order->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-3">No orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ITEMS SECTION -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between">
                        <h5 class="mb-0">Items</h5>
                        <span class="badge bg-light text-dark">{{ count($items) }} Total</span>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($items as $item)
                                    @php
                                        $img = $item->images ? json_decode($item->images, true)[0] : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <img src="{{ $img ? asset('uploads/items/' . $img) : 'https://via.placeholder.com/60' }}"
                                                style="width:60px; height:60px; object-fit:cover;"
                                                class="rounded border">
                                        </td>
                                        <td>{{ $item->item_name }}</td>

                                        <td>
                                            {{ $item->category->category_name ?? 'N/A' }}
                                        </td>

                                        <td>₹{{ number_format($item->price, 2) }}</td>

                                        <td>
                                            <span
                                                class="badge bg-{{ $item->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <a href="{{ route('admin.items.edit', $item->id) }}"
                                                class="btn btn-sm btn-outline-warning">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-3">No items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
