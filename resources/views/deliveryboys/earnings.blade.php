<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3 class="mb-4">Earnings - {{ $boy->full_name }}</h3>

    <!-- STAT CARDS -->
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-3">
                <h5>Total Orders</h5>
                <h3>{{ $totalOrders }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-3">
                <h5>Total Earnings</h5>
                <h3>₹{{ number_format($totalEarnings, 2) }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center p-3">
                <h5>This Month</h5>
                <h3>₹{{ number_format($monthEarnings, 2) }}</h3>
            </div>
        </div>

    </div>

    <!-- EARNINGS TABLE -->
    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>Amount</th>
                        <th>Delivered At</th>
                        <th>Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $as)
                        <tr>
                            <td>#{{ $as->order->id }}</td>
                            <td>₹{{ $as->order->delivery_charge }}</td>
                            <td>{{ $as->delivered_at }}</td>
                            <td>
                                <a href="{{ route('admin.delivery.timeline', $as->order_id) }}"
                                   class="btn btn-sm btn-primary">
                                    View Timeline
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No completed orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>

</x-app-layout>