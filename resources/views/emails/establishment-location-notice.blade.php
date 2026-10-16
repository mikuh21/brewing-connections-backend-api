<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrewHub Establishment Location Notice</title>
</head>
<body style="margin:0;padding:0;background:#f7f2e8;font-family:Arial,sans-serif;color:#3A2E22;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f2e8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border-radius:12px;border:1px solid #e8ddd0;overflow:hidden;">
                    <tr>
                        <td style="background:#2E5A3D;color:#ffffff;padding:18px 24px;font-size:20px;font-weight:700;">BrewHub</td>
                    </tr>
                    <tr>
                        <td style="padding:24px;line-height:1.65;font-size:15px;">
                            <p style="margin:0 0 12px;">Hi {{ $ownerName }},</p>
                            <p style="margin:0 0 12px;">We are reaching out about the map pin for your establishment listed on BrewHub.</p>
                            <div style="margin:18px 0;padding:16px;background:#f7f2e8;border:1px solid #e8ddd0;border-radius:8px;">
                                <p style="margin:0 0 6px;font-size:12px;color:#7b6a55;text-transform:uppercase;letter-spacing:.5px;">Establishment</p>
                                <p style="margin:0 0 10px;font-size:18px;font-weight:700;">{{ $establishment->name }}</p>
                                <p style="margin:0;color:#7b6a55;">{{ $establishment->address ?: 'No address currently listed' }}</p>
                            </div>
                            <p style="margin:0 0 12px;">BrewHub users have submitted <strong>{{ $incorrectCount }} reports</strong> indicating that the pinned location may be incorrect.</p>
                            <p style="margin:0 0 12px;">Please review your establishment's map pin and address in your BrewHub account. If the pin is inaccurate, update it so visitors can find your establishment more easily.</p>
                            <p style="margin:0 0 22px;">This notice is a request to verify the information; BrewHub has not automatically changed your establishment's coordinates.</p>
                            <p style="margin:0 0 22px;">
                                <a href="{{ route('login') }}" style="display:inline-block;background:#2E5A3D;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:700;">Log In to BrewHub</a>
                            </p>
                            <p style="margin:0;color:#7b6a55;font-size:13px;">If you believe this notice was sent in error or need assistance, please contact BrewHub support.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
