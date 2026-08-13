<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">✅</div>
        </div>
        <h2 style="color:#1B4332; text-align:center;">Payment Approved!</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>Great news! Your cash payment has been <strong style="color:#16a34a;">verified and approved</strong> by the admin.</p>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#166534;">Amount Verified</p>
            <p style="margin:4px 0 0; font-size:32px; font-weight:bold; color:#16a34a;">₹{{ number_format($amount, 0) }}</p>
            <p style="margin:8px 0 0; font-size:12px; color:#666;">Submitted on: {{ $date }}</p>
        </div>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:14px; color:#166534;">You can now accept new orders! 🎉</p>
        </div>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
