<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Account Pending Review</title>
</head>
<body style="margin:0;padding:0;background:#f7f2e8;font-family:Arial,sans-serif;color:#3A2E22;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f2e8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border-radius:12px;border:1px solid #e8ddd0;overflow:hidden;">
                    <tr>
                        <td style="background:#2E5A3D;color:#ffffff;padding:18px 24px;font-size:20px;font-weight:700;">
                            BrewHub
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;line-height:1.6;font-size:15px;">
                            <p style="margin:0 0 12px;">Hi {{ $user->name }},</p>
                            <p style="margin:0 0 12px;">Thank you for registering as a reseller on BrewHub.</p>
                            <p style="margin:0 0 12px;">Your account is currently pending review by our admin team. This usually takes 1-2 business days.</p>
                            <p style="margin:0 0 16px;">We will send another email once your reseller account is verified.</p>
                            <p style="margin:0 0 22px;">
                                <a href="{{ route('login') }}" style="display:inline-block;background:#2E5A3D;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600;">Open BrewHub</a>
                            </p>
                            <p style="margin:0;color:#7b6a55;font-size:13px;">If you did not request this account, please contact support.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
