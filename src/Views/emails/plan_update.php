<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Update</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Alert Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 32px; text-align: center;">
                            <div style="width: 56px; height: 56px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 16px; line-height: 56px;">
                                <span style="font-size: 24px;">📋</span>
                            </div>
                            <h1 style="margin: 0; font-size: 22px; font-weight: 600; color: #ffffff;">Project Plan Updated</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px; color: #1e293b;">
                                Hello <?= htmlspecialchars($name) ?>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6;">
                                There's an important update to the project plan for <strong style="color: #1e293b;"><?= htmlspecialchars($project_name) ?></strong>.
                            </p>

                            <!-- Change Summary -->
                            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <h3 style="margin: 0 0 8px; font-size: 14px; font-weight: 600; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px;">
                                    What Changed
                                </h3>
                                <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($change_summary)) ?>
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($share_url) ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 8px;">
                                            View Updated Plan
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 14px; color: #94a3b8; text-align: center;">
                                Please review the changes and contact us if you have any questions.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                                You're receiving this because you're a stakeholder on this project.<br>
                                This is an automated message from ProjectShare.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
