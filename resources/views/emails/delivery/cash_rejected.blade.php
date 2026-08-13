<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">❌</div>
        </div>
        <h2 style="color:#DC2626; text-align:center;">Payment Rejected</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>Your cash payment submission has been <strong style="color:#DC2626;">rejected</strong> by the admin.</p>
        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#991b1b;">Amount</p>
            <p style="margin:4px 0 0; font-size:28px; font-weight:bold; color:#DC2626;">₹{{ number_format($amount, 0) }}</p>
        </div>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:16px; margin:20px 0;">
            <p style="margin:0; font-size:13px; color:#92400e;"><strong>Reason:</strong> {{ $reason }}</p>
        </div>
        <p style="font-size:13px; color:#666;">Please re-submit the correct payment. You will not be able to accept new orders until your submission is approved.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
