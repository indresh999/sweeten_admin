<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Document Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:48px;">✅</div>
        </div>
        <h2 style="color:#16a34a; text-align:center;">Document Approved!</h2>
        <p>Hello <strong>{{ $fullName }}</strong>,</p>
        <p>Your document <strong style="color:#16a34a;">{{ $docLabel }}</strong> has been <strong style="color:#16a34a;">approved</strong> by our verification team.</p>
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px; margin:20px 0; text-align:center;">
            <p style="margin:0; font-size:14px; color:#166534;"><strong>{{ $docLabel }}</strong></p>
            <p style="margin:4px 0 0; font-size:13px; color:#16a34a;">✓ Verified</p>
        </div>
        <p style="font-size:13px; color:#666;">Please continue uploading your remaining documents if any are still pending. All documents must be approved before your account can be activated.</p>
        <br>
        <p style="font-size:13px; color:#999;">Thanks,<br><strong>Sweetan Delivery Team</strong></p>
    </div>
</body>
</html>
