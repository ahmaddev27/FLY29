@php
    $variants = [
        'info'    => '#0066CC',
        'success' => '#10B981',
        'warning' => '#F59E0B',
        'danger'  => '#EF4444',
    ];
    $bar = $variants[$announcement->variant] ?? $variants['info'];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }}</title>
</head>
<body style="margin:0;padding:0;background:#F5F5F5;font-family:'Tahoma','Segoe UI',Arial,sans-serif;color:#222;direction:rtl;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#F5F5F5;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;background:#FFFFFF;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.06);">

                    <tr>
                        <td style="background:#0066CC;padding:20px 32px;text-align:center;">
                            <h1 style="margin:0;color:#FFFFFF;font-size:20px;font-weight:700;">29FLY · برنامج الولاء</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="height:4px;background:{{ $bar }};"></td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px;">
                            <h2 style="margin:0 0 16px;font-size:20px;color:#0066CC;">{{ $announcement->title }}</h2>
                            <div style="font-size:15px;line-height:1.7;color:#333;white-space:pre-wrap;">{{ $announcement->body }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#F5F5F5;padding:16px 32px;text-align:center;border-top:1px solid #E5E7EB;">
                            <p style="margin:0;font-size:12px;color:#888;line-height:1.6;">
                                هذه رسالة آلية من برنامج ولاء 29FLY.<br>
                                للدعم: <a href="mailto:support@29fly.com" style="color:#0066CC;text-decoration:none;">support@29fly.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
