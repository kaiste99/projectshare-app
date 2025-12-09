<?php



namespace App\Services;

use PDO;

class AuthService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getCurrentUser(): ?array
    {
        // Check session first
        if (isset($_SESSION['user_id'])) {
            return $this->getUserById((int)$_SESSION['user_id']);
        }

        // Check remember token cookie
        if (isset($_COOKIE['remember_token'])) {
            $user = $this->validateRememberToken($_COOKIE['remember_token']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                return $user;
            }
        }

        return null;
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    public function createUser(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, company_name, auth_provider, oauth_id, email_verified_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $passwordHash = !empty($data['password']) ? password_hash($data['password'], PASSWORD_ARGON2ID) : null;
        $emailVerified = ($data['auth_provider'] ?? 'email') !== 'email' ? date('Y-m-d H:i:s') : null;

        $stmt->execute([
            strtolower($data['email']),
            $passwordHash,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['company_name'] ?? null,
            $data['auth_provider'] ?? 'email',
            $data['oauth_id'] ?? null,
            $emailVerified,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login_time'] = time();

        // Update last login
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    public function logout(): void
    {
        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $this->clearRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        // Clear session
        $_SESSION = [];
        session_destroy();
    }

    public function generateEmailCode(string $email): string
    {
        // Generate 6-digit code
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code with 15 minute expiry
        $stmt = $this->db->prepare("
            INSERT INTO email_verification_codes (email, code, expires_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ");
        $stmt->execute([strtolower($email), $code]);

        return $code;
    }

    public function verifyEmailCode(string $email, string $code): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM email_verification_codes
            WHERE email = ? AND code = ? AND expires_at > NOW() AND used_at IS NULL
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([strtolower($email), $code]);
        $record = $stmt->fetch();

        if ($record) {
            // Mark code as used
            $updateStmt = $this->db->prepare("UPDATE email_verification_codes SET used_at = NOW() WHERE id = ?");
            $updateStmt->execute([$record['id']]);
            return true;
        }

        return false;
    }

    public function setRememberToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare("
            INSERT INTO user_sessions (user_id, token_hash, ip_address, user_agent, expires_at)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ");
        $stmt->execute([
            $userId,
            $tokenHash,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        setcookie('remember_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $token;
    }

    private function validateRememberToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN user_sessions s ON u.id = s.user_id
            WHERE s.token_hash = ? AND s.expires_at > NOW() AND u.is_active = 1
        ");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch() ?: null;
    }

    private function clearRememberToken(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE token_hash = ?");
        $stmt->execute([$tokenHash]);
    }

    public function findOrCreateOAuthUser(string $provider, array $userData): array
    {
        // Check if user exists with this OAuth
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE auth_provider = ? AND oauth_id = ?
        ");
        $stmt->execute([$provider, $userData['id']]);
        $user = $stmt->fetch();

        if ($user) {
            return $user;
        }

        // Check if email exists
        $existingUser = $this->getUserByEmail($userData['email']);
        if ($existingUser) {
            // Link OAuth to existing account
            $stmt = $this->db->prepare("
                UPDATE users SET auth_provider = ?, oauth_id = ?, email_verified_at = COALESCE(email_verified_at, NOW())
                WHERE id = ?
            ");
            $stmt->execute([$provider, $userData['id'], $existingUser['id']]);
            return $this->getUserById($existingUser['id']);
        }

        // Create new user
        $userId = $this->createUser([
            'email' => $userData['email'],
            'first_name' => $userData['first_name'] ?? null,
            'last_name' => $userData['last_name'] ?? null,
            'auth_provider' => $provider,
            'oauth_id' => $userData['id'],
        ]);

        return $this->getUserById($userId);
    }
}
