<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #3A2E22;
            background-color: #F5F0E8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(58, 46, 34, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #4A6741 0%, #2E5A3D 100%);
            padding: 30px 20px;
            text-align: center;
            color: #FFFFFF;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .body {
            padding: 30px 20px;
            line-height: 1.6;
        }
        .body p {
            margin: 0 0 15px 0;
            font-size: 14px;
        }
        .status-box {
            background-color: #FEF3E6;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .status-box strong {
            color: #D97706;
        }
        .footer {
            background-color: #F9F6F1;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9E8C78;
            border-top: 1px solid #E5DDD0;
        }
        .footer a {
            color: #4A6741;
            text-decoration: none;
        }
        .cta-text {
            color: #6B5B4A;
            font-size: 13px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to BrewHub!</h1>
        </div>
        
        <div class="body">
            <p>Hi {{ $user->name }},</p>
            
            <p>Thank you for registering as a reseller on BrewHub. We're excited to have you join our growing community of coffee enthusiasts and sellers.</p>
            
            <div class="status-box">
                <strong>Account Status:</strong> Pending Review<br>
                Your reseller account is currently under review by our admin team. This typically takes 1-2 business days.
            </div>
            
            <p>During this time, you can:</p>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>Log in to your account</li>
                <li>View your dashboard</li>
                <li>Update your profile information</li>
                <li>Browse the marketplace</li>
            </ul>
            
            <p>You'll receive another email notification as soon as your account is verified. Once verified, you'll be able to list products and complete transactions on the platform.</p>
            
            <p class="cta-text">
                <strong>Questions?</strong> If you need any assistance, feel free to reach out to our support team.
            </p>
            
            <p>Best regards,<br>
            The BrewHub Team</p>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">This is an automated message. Please do not reply to this email.</p>
            <p style="margin: 5px 0 0 0;"><a href="{{ config('app.url') }}">Visit BrewHub</a></p>
        </div>
    </div>
</body>
</html>
