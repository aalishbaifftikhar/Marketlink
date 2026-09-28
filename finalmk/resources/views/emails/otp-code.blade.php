<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET ? 'Reset your password' : 'Verify your email' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f4;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background-color:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e7e5e4;">
                    <tr>
                        <td style="background-color:#307233;padding:28px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <span style="display:inline-block;width:38px;height:38px;border-radius:11px;background-color:rgba(255,255,255,0.16);text-align:center;line-height:38px;font-size:19px;color:#ffffff;">&#127811;</span>
                                    </td>
                                    <td style="vertical-align:middle;padding-left:12px;">
                                        <span style="font-size:19px;font-weight:700;color:#ffffff;letter-spacing:0.2px;">{{ settings('site_name', 'MarketLink') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 8px;font-size:22px;line-height:1.3;color:#0d1f0f;">
                                @if ($purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET)
                                    Reset your password
                                @else
                                    Verify your email
                                @endif
                            </h1>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#57534e;">
                                @if ($recipientName !== '')
                                    Hi {{ $recipientName }},
                                @else
                                    Hi there,
                                @endif
                                @if ($purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET)
                                    use the code below to choose a new password. It expires in {{ \App\Models\Otp::TTL_MINUTES }} minutes.
                                @else
                                    use the code below to finish setting up your account. It expires in {{ \App\Models\Otp::TTL_MINUTES }} minutes.
                                @endif
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="background-color:#f2f9f1;border:1px dashed #96cd93;border-radius:16px;padding:22px 12px;">
                                        <span style="display:block;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#307233;font-weight:700;margin-bottom:8px;">Your code</span>
                                        <span style="display:block;font-size:38px;letter-spacing:10px;font-weight:700;color:#0d1f0f;font-family:'Courier New',Courier,monospace;">{{ $code }}</span>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:24px 0 0;font-size:14px;line-height:1.6;color:#78716c;">
                                Enter this code on the verification page. If you didn't request it, you can safely ignore this email — your account stays secure.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px 26px;border-top:1px solid #e7e5e4;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#a8a29e;">
                                &copy; {{ date('Y') }} {{ settings('site_name', 'MarketLink') }} — fresh local produce, pre-ordered from the people who grow it.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
