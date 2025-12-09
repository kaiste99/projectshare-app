<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Access Code</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 480px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 32px 24px; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #0f172a;">ProjectShare</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 0 32px 32px;">
                            <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 600; color: #1e293b; text-align: center;">Your Access Code</h2>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6; text-align: center;">
                                Enter this code to sign in to your account:
                            </p>

                            <!-- Code Box -->
                            <div style="background-color: #f1f5f9; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 24px;">
                                <span style="font-size: 36px; font-weight: 700; letter-spacing: 8px; color: #2563eb; font-family: monospace;">
                                    <?= htmlspecialchars($code) ?>
                                </span>
                            </div>

                            <p style="margin: 0 0 8px; font-size: 14px; color: #94a3b8; text-align: center;">
                                This code expires in 15 minutes.
                            </p>
                            <p style="margin: 0; font-size: 14px; color: #94a3b8; text-align: center;">
                                If you didn't request this code, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                                &copy; <?= date('Y') ?> ProjectShare. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
