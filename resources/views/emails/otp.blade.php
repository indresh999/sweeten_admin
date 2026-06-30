<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>
<body style="font-family: Arial; background:#f4f4f4; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:8px;">
        
        <h2 style="color:#333;">OTP Verification</h2>

        <p>Hello,</p>

        <p>Your One Time Password (OTP) for login is:</p>

        <h1 style="letter-spacing:5px; color:#593884;">{{ $otp }}</h1>

        <p>This OTP will expire in 10 minutes.</p>

        <p>If you did not request this OTP, please ignore this email.</p>

        <br>

        <p>Thanks,<br>Your App Team</p>

    </div>
</body>
</html>