<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Password</title>
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
                            <p style="margin:0 0 12px;">A new temporary password was generated for your account.</p>
                            <p style="margin:0 0 8px;"><strong>Temporary Password:</strong></p>
                            <p style="margin:0 0 16px;padding:10px 12px;background:#f7f2e8;border:1px solid #e8ddd0;border-radius:8px;font-size:16px;letter-spacing:0.5px;">
                                {{ $temporaryPassword }}
                            </p>
                            <p style="margin:0 0 12px;">Please log in using this password and change it right away.</p>
                            <p style="margin:0;color:#7b6a55;font-size:13px;">If you did not request this reset, contact BrewHub support immediately.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
