<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3 class="mb-3">Orders of {{ $boy->full_name }}</h3>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Orders</option>
                    <option value="running" {{ $status=='running' ? 'selected' : '' }}>Running</option>
                    <option value="completed" {{ $status=='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>User</th>
                        <th>Shop</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Picked</th>
                        <th>Delivered</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($assignments as $as)
                        <tr>
                            <td>#{{ $as->order->id }}</td>
                            <td>{{ $as->order->user->full_name }}</td>
                            <td>{{ $as->order->owner->restaurant_name }}</td>
                            <td>₹{{ number_format($as->order->final_amount, 2) }}</td>

                            <td>
                                <span class="badge bg-{{ 
                                    $as->status == 'completed' ? 'success' :
                                    ($as->status == 'running' ? 'info' : 'danger')
                                }}">
                                    {{ ucfirst($as->status) }}
                                </span>
                            </td>

                            <td>{{ $as->picked_at ? $as->picked_at->format('d M h:i A') : '-' }}</td>
                            <td>{{ $as->delivered_at ? $as->delivered_at->format('d M h:i A') : '-' }}</td>

                            <td>
                                <a href="{{ route('admin.delivery.order.details', $as->order_id) }}"
                                    class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $assignments->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>

</x-app-layout>