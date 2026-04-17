<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BrewHub</title>
</head>
<body style="margin:0;padding:0;background:#F3E9D7;font-family:Arial,sans-serif;color:#3A2E22;">
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
        <div style="width:100%;max-width:430px;background:#FFFFFF;border:1px solid #E5D9C9;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.08);overflow:hidden;">
            <div style="background:#2E5A3D;color:#FFFFFF;padding:18px 22px;font-size:22px;font-weight:700;letter-spacing:0.3px;">
                BrewHub
            </div>

            <div style="padding:22px;">
                <h1 style="margin:0 0 8px;font-size:28px;line-height:32px;color:#3A2E22;">Reset Password</h1>
                <p style="margin:0 0 18px;font-size:14px;line-height:20px;color:#6E6254;">
                    Enter your new password below for your account email.
                </p>

                @if ($errors->any())
                    <div style="margin-bottom:14px;border-radius:10px;background:#FDECEC;border:1px solid #E7B5B5;padding:10px 12px;color:#8E2F2F;font-size:13px;line-height:18px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset.submit') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}" />

                    <label for="email" style="display:block;margin-bottom:6px;font-size:12px;font-weight:700;color:#6E6254;">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $email) }}"
                        readonly
                        style="width:100%;box-sizing:border-box;border:1px solid #D8CCBE;border-radius:10px;padding:11px 12px;background:#F7F2EA;color:#3A2E22;font-size:14px;margin-bottom:12px;"
                    />

                    <label for="password" style="display:block;margin-bottom:6px;font-size:12px;font-weight:700;color:#6E6254;">New Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        minlength="8"
                        style="width:100%;box-sizing:border-box;border:1px solid #D8CCBE;border-radius:10px;padding:11px 12px;background:#FFFCF8;color:#3A2E22;font-size:14px;margin-bottom:12px;"
                    />

                    <label for="password_confirmation" style="display:block;margin-bottom:6px;font-size:12px;font-weight:700;color:#6E6254;">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        style="width:100%;box-sizing:border-box;border:1px solid #D8CCBE;border-radius:10px;padding:11px 12px;background:#FFFCF8;color:#3A2E22;font-size:14px;margin-bottom:16px;"
                    />

                    <button
                        type="submit"
                        style="width:100%;border:0;border-radius:12px;padding:12px 16px;background:#2E5A3D;color:#FFFFFF;font-size:15px;font-weight:700;cursor:pointer;"
                    >
                        Save New Password
                    </button>
                </form>

                <div style="margin-top:14px;text-align:center;">
                    <a href="{{ route('login') }}" style="color:#2E5A3D;text-decoration:none;font-size:13px;font-weight:600;">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
