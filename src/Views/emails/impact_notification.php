<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Interruption Notice</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Alert Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 32px; text-align: center;">
                            <div style="width: 56px; height: 56px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 16px; line-height: 56px;">
                                <span style="font-size: 24px;">⚠️</span>
                            </div>
                            <h1 style="margin: 0; font-size: 22px; font-weight: 600; color: #ffffff;">Service Interruption Notice</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px; color: #1e293b;">
                                Hello <?= htmlspecialchars($name) ?>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #64748b; line-height: 1.6;">
                                Important information about upcoming work on <strong style="color: #1e293b;"><?= htmlspecialchars($project_name) ?></strong> that may affect you:
                            </p>

                            <!-- Impacts -->
                            <?php foreach ($impacts as $impact): ?>
                            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 20px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                    <?php
                                    $icons = [
                                        'electricity_interruption' => '⚡',
                                        'water_interruption' => '💧',
                                        'heating_interruption' => '🌡️',
                                        'noise' => '🔊',
                                        'access_restriction' => '🚧',
                                    ];
                                    $icon = $icons[$impact['impact_type']] ?? 'ℹ️';
                                    ?>
                                    <span style="font-size: 24px; margin-right: 12px;"><?= $icon ?></span>
                                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #991b1b;">
                                        <?= htmlspecialchars($impact['title']) ?>
                                    </h3>
                                </div>

                                <?php if (!empty($impact['description'])): ?>
                                <p style="margin: 0 0 12px; font-size: 14px; color: #7f1d1d; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($impact['description'])) ?>
                                </p>
                                <?php endif; ?>

                                <?php if (!empty($impact['impact_start_datetime'])): ?>
                                <div style="background-color: #ffffff; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                    <p style="margin: 0; font-size: 14px; color: #991b1b;">
                                        <strong>When:</strong>
                                        <?= date('l, M j, Y - H:i', strtotime($impact['impact_start_datetime'])) ?>
                                        <?php if (!empty($impact['impact_end_datetime'])): ?>
                                            to <?= date('H:i', strtotime($impact['impact_end_datetime'])) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($impact['preparation_instructions'])): ?>
                                <div style="background-color: #ffffff; border-radius: 8px; padding: 12px;">
                                    <p style="margin: 0 0 4px; font-size: 12px; font-weight: 600; color: #991b1b; text-transform: uppercase;">
                                        What to do before:
                                    </p>
                                    <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.5;">
                                        <?= nl2br(htmlspecialchars($impact['preparation_instructions'])) ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= htmlspecialchars($share_url) ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 8px;">
                                            View Full Details
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
