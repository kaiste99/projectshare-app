<?php
// Error reporting
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Start session
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Lax',
]);

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Set default timezone
date_default_timezone_set('Europe/Berlin');

// CORS headers for API
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Get request info
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simple router
$routes = [
    // Public pages
    'GET' => [
        '/' => 'HomeController@index',
        '/login' => 'AuthController@showLogin',
        '/register' => 'AuthController@showRegister',
        '/auth/google' => 'AuthController@googleRedirect',
        '/auth/google/callback' => 'AuthController@googleCallback',
        '/auth/apple' => 'AuthController@appleRedirect',
        '/auth/apple/callback' => 'AuthController@appleCallback',
        '/auth/microsoft' => 'AuthController@microsoftRedirect',
        '/auth/microsoft/callback' => 'AuthController@microsoftCallback',
        '/logout' => 'AuthController@logout',
        '/verify-email' => 'AuthController@showVerifyEmail',

        // Dashboard (protected)
        '/dashboard' => 'DashboardController@index',

        // Account management
        '/account' => 'AccountController@index',
        '/account/settings' => 'AccountController@settings',
        '/account/team' => 'AccountController@team',
        '/account/team/invite' => 'AccountController@showInvite',

        // Projects
        '/projects' => 'ProjectController@index',
        '/projects/create' => 'ProjectController@create',
        '/projects/{id}' => 'ProjectController@show',
        '/projects/{id}/edit' => 'ProjectController@edit',
        '/projects/{id}/plan' => 'ProjectController@plan',
        '/projects/{id}/stakeholders' => 'ProjectController@stakeholders',
        '/projects/{id}/files' => 'ProjectController@files',
        '/projects/{id}/feedback' => 'ProjectController@feedback',

        // Stakeholder portal (public with token)
        '/share/{token}' => 'ShareController@index',
        '/share/{token}/plan' => 'ShareController@plan',
        '/share/{token}/files' => 'ShareController@files',
        '/share/{token}/file/{id}' => 'ShareController@viewFile',

        // Feedback form (public with token)
        '/feedback/{token}' => 'FeedbackController@show',

        // Accept invitation
        '/invite/{token}' => 'InviteController@show',

        // API endpoints
        '/api/projects/{id}/tracking' => 'ApiController@getTracking',
    ],
    'POST' => [
        '/login' => 'AuthController@login',
        '/register' => 'AuthController@register',
        '/send-code' => 'AuthController@sendCode',
        '/verify-code' => 'AuthController@verifyCode',

        // Account
        '/account/settings' => 'AccountController@updateSettings',
        '/account/team/invite' => 'AccountController@sendInvite',
        '/account/team/{id}/remove' => 'AccountController@removeMember',

        // Projects
        '/projects/store' => 'ProjectController@store',
        '/projects/{id}/update' => 'ProjectController@update',
        '/projects/{id}/status' => 'ProjectController@updateStatus',

        // Plan management
        '/projects/{id}/plan/task' => 'PlanController@addTask',
        '/projects/{id}/plan/task/{taskId}' => 'PlanController@updateTask',
        '/projects/{id}/plan/task/{taskId}/impact' => 'PlanController@addImpact',
        '/projects/{id}/plan/publish' => 'PlanController@publish',

        // Stakeholders
        '/projects/{id}/stakeholders/add' => 'StakeholderController@add',
        '/projects/{id}/stakeholders/{stakeholderId}/update' => 'StakeholderController@update',
        '/projects/{id}/stakeholders/{stakeholderId}/regenerate' => 'StakeholderController@regenerateLink',

        // Files
        '/projects/{id}/files/upload' => 'FileController@upload',
        '/projects/{id}/files/{fileId}/delete' => 'FileController@delete',

        // Stakeholder actions
        '/share/{token}/acknowledge/{fileId}' => 'ShareController@acknowledge',

        // Feedback
        '/feedback/{token}' => 'FeedbackController@submit',

        // Invitation
        '/invite/{token}/accept' => 'InviteController@accept',

        // Notifications
        '/projects/{id}/notify' => 'NotificationController@send',
    ],
    'DELETE' => [
        '/projects/{id}' => 'ProjectController@delete',
        '/projects/{id}/plan/task/{taskId}' => 'PlanController@deleteTask',
    ],
];

// Route matching with parameters
function matchRoute(string $uri, array $routes): ?array
{
    foreach ($routes as $pattern => $handler) {
        // Convert route pattern to regex
        $regex = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return ['handler' => $handler, 'params' => $params];
        }
    }
    return null;
}

// Match the route
$match = matchRoute($requestUri, $routes[$requestMethod] ?? []);

if ($match) {
    [$controllerName, $method] = explode('@', $match['handler']);
    $controllerClass = "App\\Controllers\\{$controllerName}";

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $method)) {
            try {
                echo $controller->$method($match['params']);
            } catch (Exception $e) {
                if ($_ENV['APP_DEBUG'] ?? false) {
                    echo "Error: " . $e->getMessage();
                } else {
                    http_response_code(500);
                    include __DIR__ . '/../src/Views/pages/error.php';
                }
            }
            exit;
        }
    }
}

// 404 Not Found
http_response_code(404);
include __DIR__ . '/../src/Views/pages/404.php';
