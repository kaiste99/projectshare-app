<?php



namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'host' => $_ENV['MAIL_HOST'] ?? '',
            'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@projectshare.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'ProjectShare',
        ];
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            if (!empty($this->config['host'])) {
                $mail->isSMTP();
                $mail->Host = $this->config['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->config['username'];
                $mail->Password = $this->config['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->config['port'];
            }

            // Recipients
            $mail->setFrom($this->config['from_address'], $this->config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mail error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function sendVerificationCode(string $email, string $code): bool
    {
        $subject = "Your ProjectShare Access Code";

        $html = $this->getEmailTemplate('verification_code', [
            'code' => $code,
            'email' => $email,
        ]);

        return $this->send($email, $subject, $html);
    }

    public function sendTeamInvitation(string $email, string $inviterName, string $accountName, string $inviteUrl): bool
    {
        $subject = "You've been invited to join {$accountName} on ProjectShare";

        $html = $this->getEmailTemplate('team_invitation', [
            'inviter_name' => $inviterName,
            'account_name' => $accountName,
            'invite_url' => $inviteUrl,
        ]);

        return $this->send($email, $subject, $html);
    }

    public function sendStakeholderInvitation(string $email, string $stakeholderName, string $projectName, string $companyName, string $shareUrl): bool
    {
        $subject = "Project Update: {$projectName}";

        $html = $this->getEmailTemplate('stakeholder_invitation', [
            'stakeholder_name' => $stakeholderName,
            'project_name' => $projectName,
            'company_name' => $companyName,
            'share_url' => $shareUrl,
        ]);

        return $this->send($email, $subject, $html);
    }

    public function sendPlanUpdateNotification(string $email, string $name, string $projectName, string $changeSummary, string $shareUrl): bool
    {
        $subject = "Important Update: {$projectName} - New Plan Version";

        $html = $this->getEmailTemplate('plan_update', [
            'name' => $name,
            'project_name' => $projectName,
            'change_summary' => $changeSummary,
            'share_url' => $shareUrl,
        ]);

        return $this->send($email, $subject, $html);
    }

    public function sendImpactNotification(string $email, string $name, string $projectName, array $impacts, string $shareUrl): bool
    {
        $subject = "Action Required: {$projectName} - Service Interruption Notice";

        $html = $this->getEmailTemplate('impact_notification', [
            'name' => $name,
            'project_name' => $projectName,
            'impacts' => $impacts,
            'share_url' => $shareUrl,
        ]);

        return $this->send($email, $subject, $html);
    }

    public function sendFeedbackRequest(string $email, string $name, string $projectName, string $companyName, string $feedbackUrl): bool
    {
        $subject = "How did we do? Your feedback on {$projectName}";

        $html = $this->getEmailTemplate('feedback_request', [
            'name' => $name,
            'project_name' => $projectName,
            'company_name' => $companyName,
            'feedback_url' => $feedbackUrl,
        ]);

        return $this->send($email, $subject, $html);
    }

    private function getEmailTemplate(string $template, array $data): string
    {
        extract($data);

        ob_start();
        include __DIR__ . "/../Views/emails/{$template}.php";
        return ob_get_clean();
    }
}
