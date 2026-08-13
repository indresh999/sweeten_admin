<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">💰</div>
        </div>
        <h2 style="color:#1B4332; text-align:center;">Payment Submitted</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>You have submitted a cash payment for verification.</p>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#92400e;">Amount Submitted</p>
            <p style="margin:4px 0 0; font-size:32px; font-weight:bold; color:#d97706;">₹{{ number_format($amount, 0) }}</p>
            <p style="margin:8px 0 0; font-size:12px; color:#666;">Date: {{ $date }}</p>
        </div>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px; margin:20px 0;">
            <p style="margin:0; font-size:13px; color:#1e40af;">⏳ Your submission is under review. You will receive an email once the admin verifies your payment.</p>
        </div>
        <p style="font-size:13px; color:#666;">Until approved, you will not be able to accept new orders.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
