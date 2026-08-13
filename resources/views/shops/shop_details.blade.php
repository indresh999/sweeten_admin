<x-app-layout :assets="$assets ?? []">

    <div class="container py-4">

       

        <div class="row">
            <!-- LEFT: SHOP DETAILS -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">

                        <!-- MAIN SHOP IMAGE -->
                        <img src="{{ $shop->images->first()?->url ?? 'https://via.placeholder.com/200x200?text=No+Image' }}"
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

                    </div>
                </div>

                <!-- PAYMENT DETAILS -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <strong>Payment Details</strong>
                    </div>
                    <div class="card-body">
                        @if($shop->payment_type)
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">Method</td><td><span class="badge bg-{{ $shop->payment_type==='upi'?'info':'primary' }}">{{ strtoupper($shop->payment_type) }}</span></td></tr>
                            @if($shop->payment_type === 'upi')
                            <tr><td class="text-muted">UPI ID</td><td>{{ $shop->upi_id ?? '—' }}</td></tr>
                            @else
                            <tr><td class="text-muted">Bank Name</td><td>{{ $shop->bank_name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Account Holder</td><td>{{ $shop->bank_account_name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Account No.</td><td>{{ $shop->bank_account_number ? '****' . substr($shop->bank_account_number, -4) : '—' }}</td></tr>
                            <tr><td class="text-muted">IFSC</td><td>{{ $shop->bank_ifsc ?? '—' }}</td></tr>
                            @endif
                        </table>
                        @else
                        <p class="text-muted mb-0 small">No payment details added yet</p>
                        @endif
                    </div>
                </div>

                <!-- SHOP IMAGES LIST -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <strong>Shop Images</strong>
                    </div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        @forelse($shop->images as $img)
                            <img src="{{ $img->url }}" style="width:90px; height:90px; object-fit:cover;"
                                class="rounded border" title="{{ $img->tag ?? 'gallery' }}">
                        @empty
                            <p class="text-muted">No Images Found</p>
                        @endforelse
                    </div>
                </div>

                <!-- SHOP DOCUMENTS -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Shop Documents</strong>
                        <span class="badge bg-secondary">{{ $shop->documents->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($shop->documents->sortBy('created_at') as $doc)
                            <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                                {{-- icon by mime --}}
                                @php $isPdf = str_contains($doc->mime_type ?? '', 'pdf'); @endphp
                                <div class="text-center" style="width:48px;flex-shrink:0">
                                    @if($isPdf)
                                        <span style="font-size:28px">📄</span>
                                    @else
                                        <img src="{{ $doc->url }}" style="width:48px;height:48px;object-fit:cover;" class="rounded border">
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold small text-truncate">{{ $doc->label }}</div>
                                    <div class="text-muted" style="font-size:11px">
                                        {{ $doc->original_name ?? '—' }}
                                        @if($doc->file_size)
                                            · {{ round($doc->file_size / 1024) }} KB
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                    <a href="{{ $doc->url }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                    {{-- Quick approve / reject --}}
                                    <form method="POST" action="{{ route('admin.shop.document.status', $doc->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm py-0" style="width:auto;font-size:12px"
                                            onchange="this.form.submit()">
                                            <option value="pending"  {{ $doc->status === 'pending'  ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $doc->status === 'approved' ? 'selected' : '' }}>Approve</option>
                                            <option value="rejected" {{ $doc->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted p-3 mb-0">No documents uploaded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- RIGHT: ORDERS + ITEMS -->
            <div class="col-lg-8">

                <!-- ORDERS SECTION -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white d-flex justify-content-between">
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
                    <div class="card-header text-white d-flex justify-content-between">
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
                                    @php $firstImgUrl = $item->image_urls[0] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <img src="{{ $firstImgUrl ?? 'https://via.placeholder.com/60' }}"
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
