<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payout Processed</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">💰</div>
        </div>
        <h2 style="color:#1B4332; text-align:center;">Payout Processed!</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>Your earnings have been transferred successfully!</p>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#166534;">Amount Transferred</p>
            <p style="margin:4px 0 0; font-size:32px; font-weight:bold; color:#16a34a;">₹{{ number_format($amount, 0) }}</p>
            <p style="margin:8px 0 0; font-size:12px; color:#666;">Via {{ $method }}</p>
        </div>
        <p style="font-size:13px; color:#666;">Please check your bank account/UPI for the credited amount.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
