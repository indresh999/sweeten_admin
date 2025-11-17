<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3>Order Details - #{{ $assignment->order->id }}</h3>

    <div class="card mb-3">
        <div class="card-header bg-info text-white">Order Summary</div>
        <div class="card-body">
            <p><strong>Status:</strong> {{ ucfirst($assignment->status) }}</p>
            <p><strong>Total Amount:</strong> ₹{{ number_format($assignment->order->final_amount, 2) }}</p>
            <p><strong>Picked:</strong> {{ $assignment->picked_at }}</p>
            <p><strong>Delivered:</strong> {{ $assignment->delivered_at }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">User</div>
        <div class="card-body">
            <p>Name: {{ $assignment->order->user->full_name }}</p>
            <p>Phone: {{ $assignment->order->user->phone_number }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-warning">Shop</div>
        <div class="card-body">
            <p>{{ $assignment->order->owner->restaurant_name }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">Items</div>
        <div class="card-body">
            <table class="table">
                @foreach($assignment->order->items as $item)
                    <tr>
                        <td>{{ $item->item->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ $item->total_price }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

</div>

</x-app-layout>