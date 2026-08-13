<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Store Application Update</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">❌</div>
        </div>
        <h2 style="color:#DC2626; text-align:center;">Store Application Not Approved</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>Thank you for applying to list your store <strong>{{ $storeName }}</strong> on Sweetan.</p>
        <p>Unfortunately, your application has been <strong style="color:#DC2626;">not approved</strong> at this time.</p>
        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:20px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:13px; color:#991b1b;">Store</p>
            <p style="margin:4px 0 0; font-size:18px; font-weight:bold; color:#DC2626;">{{ $storeName }}</p>
        </div>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:16px; margin:20px 0;">
            <p style="margin:0; font-size:13px; color:#92400e;"><strong>Reason:</strong> {{ $reason }}</p>
        </div>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px; margin:20px 0;">
            <p style="margin:0; font-size:13px; color:#1e40af;"><strong>What to do next?</strong></p>
            <p style="margin:8px 0 0; font-size:13px; color:#1e40af;">You can update your store details and resubmit your application, or contact our support team for assistance.</p>
        </div>
        <p style="font-size:13px; color:#666;">We encourage you to reapply once the issues are addressed.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Team</strong></p>
    </div>
</body>
</html>
