<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Delivered</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">🎉</div>
        </div>
        <h2 style="color:#1B4332; text-align:center;">Delivery Complete!</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>You've successfully delivered <strong>Order #{{ $orderId }}</strong>. Great job!</p>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#166534;">Earning Credited</p>
            <p style="margin:4px 0 0; font-size:32px; font-weight:bold; color:#16a34a;">₹{{ number_format($earning, 0) }}</p>
        </div>
        <p style="font-size:13px; color:#666;">Keep delivering to earn more! Check your earnings page for details.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
