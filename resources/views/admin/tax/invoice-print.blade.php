
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ str_pad($order->id,6,'0',STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; margin: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .brand { font-size: 22px; font-weight: bold; color: #2D7A2D; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f4f8f4; padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .totals td { border-bottom: none; }
        .total-row { font-weight: bold; border-top: 2px solid #2D7A2D !important; }
        .footer { text-align: center; color: #888; margin-top: 30px; font-size: 11px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div><div class="brand">🍰 Sweetan</div><div style="color:#666;font-size:12px">Tax Invoice</div></div>
        <div style="text-align:right">
            <div><strong>Invoice #{{ str_pad($order->id,6,'0',STR_PAD_LEFT) }}</strong></div>
            <div style="color:#666">{{ $order->created_at->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;margin-bottom:20px">
        <div><strong>From:</strong><br>{{ $order->owner?->restaurant_name }}<br><small>{{ $order->owner?->restaurant_address }}</small><br>@if($order->owner?->gst_number)<small>GSTIN: {{ $order->owner->gst_number }}</small>@endif</div>
        <div style="text-align:right"><strong>Billed To:</strong><br>{{ $order->user?->full_name }}<br><small>{{ $order->address_line }}, {{ $order->city }}, {{ $order->state }} {{ $order->pincode }}</small><br><small>{{ $order->user?->phone_number }}</small></div>
    </div>

    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Rate (₹)</th><th>GST</th><th>Amount (₹)</th></tr></thead>
        <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->item['name'] ?? 'Item' }}<br><small style="color:#888">{{ $item->item['variant_label'] ?? '' }}</small></td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->offer_price ?? $item->price }}</td>
            <td>{{ $item->item['gst_percent'] ?? 0 }}% (₹{{ $item->gst_amount }})</td>
            <td>{{ $item->item_total }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals" style="width:300px;margin-left:auto">
        <tr><td>Subtotal</td><td style="text-align:right">₹{{ $order->total_amount }}</td></tr>
        <tr><td>GST</td><td style="text-align:right">₹{{ $order->gst_amount }}</td></tr>
        <tr><td>Delivery</td><td style="text-align:right">₹{{ $order->delivery_charge }}</td></tr>
        @if($order->discount_amount > 0)<tr><td style="color:green">Discount ({{ $order->coupon_code }})</td><td style="text-align:right;color:green">-₹{{ $order->discount_amount }}</td></tr>@endif
        @if($order->wallet_used > 0)<tr><td style="color:#007bff">Wallet Used</td><td style="text-align:right;color:#007bff">-₹{{ $order->wallet_used }}</td></tr>@endif
        <tr class="total-row"><td><strong>Total</strong></td><td style="text-align:right"><strong>₹{{ $order->final_amount }}</strong></td></tr>
        <tr><td style="color:#666;font-size:11px">Payment</td><td style="text-align:right;font-size:11px">{{ strtoupper($order->payment_method) }}</td></tr>
    </table>

    <div class="footer">Thank you for ordering from Sweetan! | support@sweetan.in<br><button onclick="window.print()">🖨 Print</button></div>
</body>
</html>
