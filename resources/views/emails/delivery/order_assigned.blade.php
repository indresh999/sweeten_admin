<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order Assigned</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">📦</div>
        </div>
        <h2 style="color:#1B4332; text-align:center;">New Order Assigned</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>You have a new delivery assignment!</p>
        <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:16px; margin:20px 0;">
            <table style="width:100%; font-size:14px;">
                <tr>
                    <td style="color:#666; padding:4px 0;">Order ID:</td>
                    <td style="font-weight:bold; color:#1B4332;">#{{ $orderId }}</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">Pickup From:</td>
                    <td style="font-weight:bold; color:#1B4332;">{{ $shopName }}</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">Deliver To:</td>
                    <td style="font-weight:bold; color:#1B4332;">{{ $deliveryAddress }}</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">Order Amount:</td>
                    <td style="font-weight:bold; color:#16a34a;">₹{{ number_format($orderAmount, 0) }}</td>
                </tr>
            </table>
        </div>
        <p style="font-size:13px; color:#666;">Open the app to accept or reject this order.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
