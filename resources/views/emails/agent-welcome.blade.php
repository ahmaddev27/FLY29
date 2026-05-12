<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في 29FLY</title>
</head>
<body style="margin:0;padding:0;background:#F5F5F5;font-family:'Tahoma','Segoe UI',Arial,sans-serif;color:#222;direction:rtl;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#F5F5F5;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;background:#FFFFFF;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.06);">

                    <!-- Brand bar -->
                    <tr>
                        <td style="background:#0066CC;padding:24px 32px;text-align:center;">
                            <h1 style="margin:0;color:#FFFFFF;font-size:22px;font-weight:700;letter-spacing:0.5px;">
                                29FLY · برنامج الولاء
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px;font-size:20px;color:#0066CC;">
                                أهلاً وسهلاً، {{ $fullName }} 👋
                            </h2>

                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#333;">
                                تم إنشاء حساب وكيل لشركتكم <strong>«{{ $businessName }}»</strong> في برنامج ولاء 29FLY.
                                هذا الحساب يربطكم بمكافآت تلقائية على كل عملية بيع تتم عبر <a href="https://fly29.net" style="color:#0066CC;text-decoration:none;">fly29.net</a>.
                            </p>

                            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#333;">
                                للبدء، فضلاً اضغط على الزر بالأسفل لتعيين كلمة المرور الخاصة بكم:
                            </p>

                            <!-- CTA -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td style="border-radius:8px;background:#10B981;">
                                        <a href="{{ $setupUrl }}"
                                           style="display:inline-block;padding:14px 32px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;border-radius:8px;">
                                            تعيين كلمة المرور
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px;font-size:13px;line-height:1.6;color:#666;text-align:center;">
                                إذا لم يعمل الزر، انسخ الرابط التالي والصقه في المتصفح:<br>
                                <span dir="ltr" style="color:#0066CC;word-break:break-all;">{{ $setupUrl }}</span>
                            </p>

                            <!-- What's included -->
                            <div style="background:#F5F5F5;border-radius:8px;padding:20px;margin:16px 0;">
                                <h3 style="margin:0 0 12px;font-size:15px;color:#0066CC;">ما الذي ينتظركم؟</h3>
                                <ul style="margin:0;padding-right:20px;font-size:14px;line-height:1.8;color:#333;">
                                    <li>محفظتان مستقلتان: كاش (قابل للتحويل) + باكجات سياحية.</li>
                                    <li>تصنيف تلقائي (برونزي → فضي → ذهبي → ماسي) بناءً على المبيعات.</li>
                                    <li>لوحة تحكم تعرض رصيدكم وآخر المعاملات.</li>
                                    <li>تنبيه فوري عند كل عملية بيع وكل ترقية تصنيف.</li>
                                </ul>
                            </div>

                            <p style="margin:24px 0 0;font-size:13px;line-height:1.7;color:#666;">
                                <strong>تنبيه:</strong> هذا الرابط صالح لمدة 60 دقيقة. إذا انتهت صلاحيته، تواصلوا مع مدير حسابكم
                                وسنرسل لكم رابطاً جديداً.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#F5F5F5;padding:20px 32px;text-align:center;border-top:1px solid #E5E7EB;">
                            <p style="margin:0;font-size:12px;color:#888;line-height:1.6;">
                                هذه رسالة آلية، فضلاً لا تردّوا عليها مباشرة.<br>
                                للدعم: <a href="mailto:support@29fly.com" style="color:#0066CC;text-decoration:none;">support@29fly.com</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;font-size:11px;color:#999;">
                    © {{ date('Y') }} 29FLY · جميع الحقوق محفوظة
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
