<style>
.timeline { list-style:none; padding:0; }
.timeline-item {
    display:flex;
    gap:15px;
    padding:12px 0;
    border-left:3px solid #ddd;
    margin-left:20px;
    position:relative;
}
.timeline-item.active { border-color:#28a745; }
.timeline-icon {
    width:34px;height:34px;
    border-radius:50%;
    background:#fff;
    border:2px solid #28a745;
    display:flex;align-items:center;justify-content:center;
    position:absolute;left:-18px;top:12px;
}
.timeline-content { margin-left:30px; }
</style>
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
        <strong>Order Tracking</strong>
    </div>

    <div class="card-body">
        <ul class="timeline">

            @foreach ($order->timeline as $t)
                <li class="timeline-item {{ $loop->last ? 'active' : '' }}">

                    <div class="timeline-icon">
                        @switch($t->status)
                            @case('order_placed')
                                🛒
                            @break

                            @case('delivery_assigned')
                                👤
                            @break

                            @case('delivery_accepted')
                                ✅
                            @break

                            @case('picked_up')
                                📦
                            @break

                            @case('out_for_delivery')
                                🚚
                            @break

                            @case('delivered')
                                🎉
                            @break

                            @case('cancelled')
                                ❌
                            @break

                            @default
                                🔄
                        @endswitch
                    </div>

                    <div class="timeline-content">
                        <h6 class="mb-1 text-capitalize">
                            {{ str_replace('_', ' ', $t->status) }}
                        </h6>

                        <p class="mb-1 text-muted">
                            {{ $t->message }}
                        </p>

                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}
                        </small>
                    </div>

                </li>
            @endforeach

        </ul>
    </div>
</div>
