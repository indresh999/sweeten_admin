<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="row row-cols-1">
                <div class="overflow-hidden d-slider1 ">
                    <ul class="p-0 m-0 mb-2 swiper-wrapper list-inline">
                        <li class="swiper-slide card card-slide" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body">
                                <div class="progress-widget">
                                    <div id="circle-progress-01"
                                        class="text-center circle-progress-01 circle-progress circle-progress-primary"
                                        data-min-value="0" data-max-value="100" data-value="90" data-type="percent">
                                        <svg class="card-slie-arrow icon-24 " width="24" viewBox="0 0 24 24">
                                            <path fill="currentColor"
                                                d="M5,17.59L15.59,7H9V5H19V15H17V8.41L6.41,19L5,17.59Z" />
                                        </svg>
                                    </div>
                                    <div class="progress-detail">
                                        <p class="mb-2">Total Stores</p>
                                        <h4 class="counter">{{ $totalStores }}</h4>
                                    </div>
                                </div>
                            </div>
                        </li>


                    </ul>
                    <div class="swiper-button swiper-button-next"></div>
                    <div class="swiper-button swiper-button-prev"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="overflow-hidden card" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-0 card-body">
                        <div class="mt-4 table-responsive">
                            <table id="basic-table" class="table mb-0 table-striped" role="grid">
                                <thead>
                                    <tr>
                                        <th>Store name</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Pincode</th>
                                        <th>Photos</th>
                                        <th>View Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shops as $shop)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    {{-- First image (if available), fallback image otherwise --}}
                                                    @php
                                                        $firstImage = $shop->images->first();
                                                    @endphp
                                                    <img class="rounded bg-primary-subtle img-fluid avatar-40 me-3"
                                                        src="{{ $firstImage ? asset($firstImage->image_path) : asset('https://via.placeholder.com/40x40?text=No+Img') }}"
                                                        alt="profile">
                                                    <h6>{{ $shop->restaurant_name ?? 'Unnamed Shop' }}</h6>
                                                </div>
                                            </td>

                                            <td>
                                                @if ($shop->phone_number)
                                                    <a
                                                        href="tel:{{ $shop->phone_number }}">{{ $shop->phone_number }}</a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>

                                            <td>{{ $shop->restaurant_address ?? '—' }}</td>

                                            <td>{{ $shop->zip_code ?? '—' }}</td>

                                            <td>
                                                <div class="d-flex flex-wrap">
                                                    @forelse($shop->images->take(4) as $image)
                                                        <div class="me-2 mb-2">
                                                            <img src="{{ asset($image->image_path) }}" alt="Shop image"
                                                                width="60" height="60" class="rounded border">
                                                        </div>
                                                    @empty
                                                        <span class="text-muted">No Images</span>
                                                    @endforelse
                                                </div>
                                            </td>

                                            <td>

                                                <a href="{{ route('shops.show', ['id' => $shop->shop_id]) }}"
                                                    class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No shops found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        <div class="mt-3 px-3">
                            {{ $shops->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====================== ORDERS SECTION ======================= -->
            <div class="col-md-12 mt-4">

                <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                    </div>

                    {{-- FILTER FORM --}}
                    <div class="card-body border-bottom pb-3">
                        <form method="GET" action="{{ route('dashboard') }}" class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ $from }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ $to }}">
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary w-100">Filter</button>
                            </div>

                        </form>
                    </div>

                    {{-- ORDERS TABLE --}}
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Store</th>
                                    <th>Total (₹)</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>

                                        <td>{{ $order->user->full_name ?? 'N/A' }}</td>

                                        <td>{{ $order->owner->restaurant_name ?? 'N/A' }}</td>

                                        <td>
                                            <strong>₹{{ number_format($order->final_amount, 2) }}</strong>
                                        </td>

                                        <td>
                                            <span
                                                class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>

                                        <td>{{ $order->created_at->format('d M, Y h:i A') }}</td>

                                        <td>
                                           <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted p-3">
                                            No orders found for selected dates.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3 px-3">
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
