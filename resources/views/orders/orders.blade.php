<x-app-layout :assets="$assets ?? []">

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="overflow-hidden card">
                <div class="p-0 card-body">
                    <div class="mt-4 table-responsive">
                        <table id="basic-table" class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User Name</th>
                                    <th>User Phone</th>
                                    <th>Shop Name</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>View Details</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>

                                        <td>{{ $order->user->full_name ?? 'N/A' }}</td>
                                        <td>{{ $order->user->phone_number ?? 'N/A' }}</td>

                                        <td>{{ $order->owner->restaurant_name ?? 'N/A' }}</td>

                                        <td>₹{{ number_format($order->final_amount, 2) }}</td>

                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                                        </td>

                                        <td>
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 px-3">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>