<?php



namespace App\Controllers;

use App\Config\Database;
use App\Services\AuthService;
use PDO;

abstract class BaseController
{
    protected PDO $db;
    protected AuthService $auth;
    protected ?array $currentUser = null;
    protected ?array $currentAccount = null;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->auth = new AuthService($this->db);
        $this->currentUser = $this->auth->getCurrentUser();

        if ($this->currentUser) {
            $this->loadCurrentAccount();
        }
    }

    protected function loadCurrentAccount(): void
    {
        if (!$this->currentUser) {
            return;
        }

        // Get user's primary account (or first account they belong to)
        $stmt = $this->db->prepare("
            SELECT a.*, am.role as member_role
            FROM accounts a
            JOIN account_members am ON a.id = am.account_id
            WHERE am.user_id = ? AND am.is_active = 1
            ORDER BY am.role = 'owner' DESC, am.created_at ASC
            LIMIT 1
        ");
        $stmt->execute([$this->currentUser['id']]);
        $this->currentAccount = $stmt->fetch() ?: null;
    }

    protected function requireAuth(): void
    {
        if (!$this->currentUser) {
            $this->redirect('/login');
        }
    }

    protected function requireAccount(): void
    {
        $this->requireAuth();
        if (!$this->currentAccount) {
            $this->redirect('/account/setup');
        }
    }

    protected function view(string $template, array $data = []): string
    {
        $data['currentUser'] = $this->currentUser;
        $data['currentAccount'] = $this->currentAccount;
        $data['appName'] = $_ENV['APP_NAME'] ?? 'ProjectShare';
        $data['appUrl'] = $_ENV['APP_URL'] ?? 'http://localhost:8000';

        extract($data);

        ob_start();
        include __DIR__ . "/../Views/{$template}.php";
        return ob_get_clean();
    }

    protected function json(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function getPostData(): array
    {
        return $_POST;
    }

    protected function getJsonData(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
