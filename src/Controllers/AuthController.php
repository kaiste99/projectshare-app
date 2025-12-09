<?php



namespace App\Controllers;

use App\Services\MailService;

class AuthController extends BaseController
{
    public function showLogin(): string
    {
        if ($this->currentUser) {
            $this->redirect('/dashboard');
        }

        return $this->view('pages/auth/login', [
            'title' => 'Sign In',
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function showRegister(): string
    {
        if ($this->currentUser) {
            $this->redirect('/dashboard');
        }

        return $this->view('pages/auth/register', [
            'title' => 'Create Account',
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function showVerifyEmail(): string
    {
        $email = $_GET['email'] ?? $_SESSION['verify_email'] ?? '';

        if (empty($email)) {
            $this->redirect('/login');
        }

        return $this->view('pages/auth/verify-email', [
            'title' => 'Enter Access Code',
            'email' => $email,
            'csrf' => $this->csrfToken(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function sendCode(): string
    {
        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $data = $this->getPostData();
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            return $this->json(['error' => 'Please enter a valid email address'], 400);
        }

        // Generate and send code
        $code = $this->auth->generateEmailCode($email);

        $mailService = new MailService();
        $sent = $mailService->sendVerificationCode($email, $code);

        if (!$sent && ($_ENV['APP_DEBUG'] ?? false)) {
            // In debug mode, return the code for testing
            return $this->json(['success' => true, 'debug_code' => $code]);
        }

        $_SESSION['verify_email'] = $email;

        return $this->json(['success' => true]);
    }

    public function verifyCode(): string
    {
        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid request'], 403);
        }

        $data = $this->getPostData();
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $code = preg_replace('/[^0-9]/', '', $data['code'] ?? '');

        if (!$email || strlen($code) !== 6) {
            return $this->json(['error' => 'Invalid email or code'], 400);
        }

        if (!$this->auth->verifyEmailCode($email, $code)) {
            return $this->json(['error' => 'Invalid or expired code. Please request a new one.'], 400);
        }

        // Check if user exists
        $user = $this->auth->getUserByEmail($email);

        if (!$user) {
            // Create new user
            $userId = $this->auth->createUser([
                'email' => $email,
                'auth_provider' => 'email',
            ]);
            $user = $this->auth->getUserById($userId);

            // Mark email as verified
            $stmt = $this->db->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        }

        // Log in the user
        $this->auth->login($user);

        // Set remember token if requested
        if (!empty($data['remember'])) {
            $this->auth->setRememberToken($user['id']);
        }

        unset($_SESSION['verify_email']);

        // Redirect to onboarding if new user without account
        $hasAccount = $this->db->prepare("SELECT 1 FROM account_members WHERE user_id = ? LIMIT 1");
        $hasAccount->execute([$user['id']]);

        $redirectUrl = $hasAccount->fetch() ? '/dashboard' : '/account/setup';

        return $this->json(['success' => true, 'redirect' => $redirectUrl]);
    }

    public function login(): string
    {
        // This is now handled via email code verification
        $this->redirect('/login');
        return '';
    }

    public function register(): string
    {
        // Registration is now also via email code
        $this->redirect('/register');
        return '';
    }

    public function logout(): string
    {
        $this->auth->logout();
        $this->redirect('/');
        return '';
    }

    // OAuth: Google
    public function googleRedirect(): void
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

        if (empty($clientId)) {
            $this->flash('error', 'Google login is not configured');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        $this->redirect("https://accounts.google.com/o/oauth2/v2/auth?{$params}");
    }

    public function googleCallback(): string
    {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
            $this->flash('error', 'Invalid authentication response');
            $this->redirect('/login');
        }

        unset($_SESSION['oauth_state']);

        try {
            // Exchange code for token
            $tokenResponse = $this->exchangeGoogleCode($code);
            $accessToken = $tokenResponse['access_token'] ?? '';

            if (empty($accessToken)) {
                throw new \Exception('Failed to get access token');
            }

            // Get user info
            $userInfo = $this->getGoogleUserInfo($accessToken);

            // Find or create user
            $user = $this->auth->findOrCreateOAuthUser('google', [
                'id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'first_name' => $userInfo['given_name'] ?? null,
                'last_name' => $userInfo['family_name'] ?? null,
            ]);

            $this->auth->login($user);
            $this->auth->setRememberToken($user['id']);

            // Check if user has account
            $hasAccount = $this->db->prepare("SELECT 1 FROM account_members WHERE user_id = ? LIMIT 1");
            $hasAccount->execute([$user['id']]);

            $this->redirect($hasAccount->fetch() ? '/dashboard' : '/account/setup');

        } catch (\Exception $e) {
            error_log("Google OAuth error: " . $e->getMessage());
            $this->flash('error', 'Authentication failed. Please try again.');
            $this->redirect('/login');
        }

        return '';
    }

    private function exchangeGoogleCode(string $code): array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'code' => $code,
                'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
                'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
                'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
                'grant_type' => 'authorization_code',
            ]),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    private function getGoogleUserInfo(string $accessToken): array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    // OAuth: Microsoft
    public function microsoftRedirect(): void
    {
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? '';
        $redirectUri = $_ENV['MICROSOFT_REDIRECT_URI'] ?? '';

        if (empty($clientId)) {
            $this->flash('error', 'Microsoft login is not configured');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile User.Read',
            'state' => $state,
            'response_mode' => 'query',
        ]);

        $this->redirect("https://login.microsoftonline.com/common/oauth2/v2.0/authorize?{$params}");
    }

    public function microsoftCallback(): string
    {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
            $this->flash('error', 'Invalid authentication response');
            $this->redirect('/login');
        }

        unset($_SESSION['oauth_state']);

        try {
            // Exchange code for token
            $ch = curl_init('https://login.microsoftonline.com/common/oauth2/v2.0/token');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'code' => $code,
                    'client_id' => $_ENV['MICROSOFT_CLIENT_ID'],
                    'client_secret' => $_ENV['MICROSOFT_CLIENT_SECRET'],
                    'redirect_uri' => $_ENV['MICROSOFT_REDIRECT_URI'],
                    'grant_type' => 'authorization_code',
                ]),
            ]);
            $tokenResponse = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $accessToken = $tokenResponse['access_token'] ?? '';

            if (empty($accessToken)) {
                throw new \Exception('Failed to get access token');
            }

            // Get user info
            $ch = curl_init('https://graph.microsoft.com/v1.0/me');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
            ]);
            $userInfo = json_decode(curl_exec($ch), true);
            curl_close($ch);

            // Find or create user
            $user = $this->auth->findOrCreateOAuthUser('microsoft', [
                'id' => $userInfo['id'],
                'email' => $userInfo['mail'] ?? $userInfo['userPrincipalName'],
                'first_name' => $userInfo['givenName'] ?? null,
                'last_name' => $userInfo['surname'] ?? null,
            ]);

            $this->auth->login($user);
            $this->auth->setRememberToken($user['id']);

            $hasAccount = $this->db->prepare("SELECT 1 FROM account_members WHERE user_id = ? LIMIT 1");
            $hasAccount->execute([$user['id']]);

            $this->redirect($hasAccount->fetch() ? '/dashboard' : '/account/setup');

        } catch (\Exception $e) {
            error_log("Microsoft OAuth error: " . $e->getMessage());
            $this->flash('error', 'Authentication failed. Please try again.');
            $this->redirect('/login');
        }

        return '';
    }

    // OAuth: Apple
    public function appleRedirect(): void
    {
        $clientId = $_ENV['APPLE_CLIENT_ID'] ?? '';
        $redirectUri = $_ENV['APPLE_REDIRECT_URI'] ?? '';

        if (empty($clientId)) {
            $this->flash('error', 'Apple login is not configured');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code id_token',
            'scope' => 'name email',
            'state' => $state,
            'response_mode' => 'form_post',
        ]);

        $this->redirect("https://appleid.apple.com/auth/authorize?{$params}");
    }

    public function appleCallback(): string
    {
        $code = $_POST['code'] ?? '';
        $state = $_POST['state'] ?? '';
        $idToken = $_POST['id_token'] ?? '';

        if (empty($code) || $state !== ($_SESSION['oauth_state'] ?? '')) {
            $this->flash('error', 'Invalid authentication response');
            $this->redirect('/login');
        }

        unset($_SESSION['oauth_state']);

        try {
            // Decode ID token to get user info (Apple includes it in the callback)
            $tokenParts = explode('.', $idToken);
            $payload = json_decode(base64_decode($tokenParts[1]), true);

            $email = $payload['email'] ?? '';
            $appleUserId = $payload['sub'] ?? '';

            if (empty($email) || empty($appleUserId)) {
                throw new \Exception('Missing user information');
            }

            // Apple only sends name on first authorization
            $userData = $_POST['user'] ?? null;
            $firstName = null;
            $lastName = null;

            if ($userData) {
                $userDataDecoded = json_decode($userData, true);
                $firstName = $userDataDecoded['name']['firstName'] ?? null;
                $lastName = $userDataDecoded['name']['lastName'] ?? null;
            }

            // Find or create user
            $user = $this->auth->findOrCreateOAuthUser('apple', [
                'id' => $appleUserId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            $this->auth->login($user);
            $this->auth->setRememberToken($user['id']);

            $hasAccount = $this->db->prepare("SELECT 1 FROM account_members WHERE user_id = ? LIMIT 1");
            $hasAccount->execute([$user['id']]);

            $this->redirect($hasAccount->fetch() ? '/dashboard' : '/account/setup');

        } catch (\Exception $e) {
            error_log("Apple OAuth error: " . $e->getMessage());
            $this->flash('error', 'Authentication failed. Please try again.');
            $this->redirect('/login');
        }

        return '';
    }
}
