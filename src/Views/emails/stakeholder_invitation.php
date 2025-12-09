<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Access</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 32px; text-align: center;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 600; color: #ffffff;">You've Been Added to a Project</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px; color: #1e293b;">
                                Hello <?= htmlspecialchars($stakeholder_name) ?>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6;">
                                <strong style="color: #1e293b;"><?= htmlspecialchars($company_name) ?></strong> has given you access to view project information for:
                            </p>

                            <!-- Project Box -->
                            <div style="background-color: #f1f5f9; border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
                                <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #1e293b;">
                                    <?= htmlspecialchars($project_name) ?>
                                </h2>
                            </div>

                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6;">
                                Click the button below to view project plans, schedules, and important documents. No account creation required.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($share_url) ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 8px;">
                                            View Project
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 14px; color: #94a3b8; text-align: center;">
                                This is a private link. Please don't share it with others.
                            </p>
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
