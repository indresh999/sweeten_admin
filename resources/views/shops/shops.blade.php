<x-app-layout :assets="$assets ?? []">
   
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="overflow-hidden card">
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
                                              
                                              <a href="{{ route('shops.show', ['id' => $shop->shop_id]) }}" class="btn btn-sm btn-outline-primary">View</a>
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
        </div>
</x-app-layout>
