<x-app-layout :assets="$assets ?? []">

<div class="container py-4">

    <h3 class="mb-3">
        Delivery Timeline — Order #{{ $assignment->order_id }}
    </h3>

    <div class="card shadow-sm p-4">

        <ul class="timeline">

            <li>
                <strong>Assigned</strong>
                <span class="text-muted">{{ $assignment->created_at }}</span>
            </li>

            @if($assignment->accepted_at)
            <li>
                <strong>Accepted</strong>
                <span class="text-muted">{{ $assignment->accepted_at }}</span>
            </li>
            @endif

            @if($assignment->picked_at)
            <li>
                <strong>Picked From Shop</strong>
                <span class="text-muted">{{ $assignment->picked_at }}</span>
            </li>
            @endif

            @if($assignment->delivered_at)
            <li>
                <strong>Delivered</strong>
                <span class="text-success">{{ $assignment->delivered_at }}</span>
            </li>
            @endif

            @if($assignment->status == 'cancelled')
            <li>
                <strong class="text-danger">Cancelled</strong>
                <span>{{ $assignment->rejected_at }}</span>
            </li>
            @endif

        </ul>
    </div>

</div>

<style>
.timeline {
    list-style: none;
    padding: 0;
    position: relative;
}
.timeline li {
    margin-bottom: 20px;
    padding-left: 20px;
    border-left: 3px solid #007bff;
}
.timeline li:last-child {
    border-left-color: #28a745;
}
</style>

</x-app-layout>