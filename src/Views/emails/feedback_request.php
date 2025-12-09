<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Feedback Matters</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 32px; text-align: center;">
                            <div style="width: 56px; height: 56px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 16px; line-height: 56px;">
                                <span style="font-size: 24px;">💬</span>
                            </div>
                            <h1 style="margin: 0; font-size: 22px; font-weight: 600; color: #ffffff;">How Did We Do?</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px; color: #1e293b;">
                                Hello <?= htmlspecialchars($name) ?>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6;">
                                The <strong style="color: #1e293b;"><?= htmlspecialchars($project_name) ?></strong> project by <?= htmlspecialchars($company_name) ?> has been completed. We'd love to hear about your experience!
                            </p>

                            <!-- Why Feedback Matters -->
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <h3 style="margin: 0 0 12px; font-size: 14px; font-weight: 600; color: #166534;">
                                    Your feedback helps us:
                                </h3>
                                <ul style="margin: 0; padding: 0 0 0 20px; color: #15803d; font-size: 14px; line-height: 1.8;">
                                    <li>Improve our services</li>
                                    <li>Train our team</li>
                                    <li>Serve you better in the future</li>
                                </ul>
                            </div>

                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6; text-align: center;">
                                It only takes 2-3 minutes
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($feedback_url) ?>" style="display: inline-block; background-color: #10b981; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 8px;">
                                            Share Your Feedback
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                                Sent by <?= htmlspecialchars($company_name) ?> via ProjectShare
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
