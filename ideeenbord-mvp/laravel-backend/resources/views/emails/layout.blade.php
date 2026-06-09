<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Ideeënbord' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color:#1a1a1a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 6px 24px rgba(31,41,55,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1f2937; padding:28px 32px;">
                            <span style="font-size:22px; font-weight:bold; color:#ffffff; letter-spacing:0.3px;">
                                Ideeën<span style="color:#f78a1d;">bord</span>
                                <span style="color:#ffbb00;">&#128161;</span>
                            </span>
                        </td>
                    </tr>
                    <!-- Accent bar -->
                    <tr><td style="height:4px; background:linear-gradient(90deg,#f78a1d,#ffbb00);"></td></tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1f2937; padding:24px 32px; text-align:center;">
                            <p style="margin:0 0 6px; color:#d1d5db; font-size:13px;">
                                Ideeënbord — waar jouw stem telt bij merken.
                            </p>
                            <p style="margin:0; color:#9ca3af; font-size:12px;">
                                <a href="https://ideeenbord.nl" style="color:#f78a1d; text-decoration:none;">ideeenbord.nl</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="color:#9ca3af; font-size:11px; margin:16px 0 0;">
                    Je ontvangt deze e-mail omdat je een account hebt op Ideeënbord.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
