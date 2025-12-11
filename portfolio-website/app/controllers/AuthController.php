<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * Display login form
     */
    public function index() {
        require_once dirname(dirname(__DIR__)) . '/app/config/config.php';

        // Check if already authenticated
        if (Session::isAuthenticated()) {
            header('Location: ' . $baseUrl . '/admin');
            exit;
        }

        // Check for remember me cookie
        $this->checkRememberMe();

        // If still not authenticated after remember me check, show login form
        if (!Session::isAuthenticated()) {
            $data = [
                'title' => 'Login - Vuyani Magibisela',
                'error' => Session::getFlash('error'),
                'success' => Session::getFlash('success'),
                'csrf_token' => Session::generateCsrfToken(),
                'baseUrl' => $baseUrl
            ];

            $this->view('auth/login', $data);
        }
    }

    /**
     * Process login attempt
     */
    public function authenticate() {
        require_once dirname(dirname(__DIR__)) . '/app/config/config.php';

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/auth');
            exit;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header('Location: ' . $baseUrl . '/auth');
            exit;
        }

        // Get login credentials
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        // Validate input
        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Please provide both username and password.');
            header('Location: ' . $baseUrl . '/auth');
            exit;
        }

        // Check if login is locked due to too many attempts
        if (Session::isLoginLocked($username)) {
            $remaining = Session::getLockoutRemaining($username);
            $minutes = ceil($remaining / 60);
            Session::setFlash('error', "Too many failed login attempts. Please try again in {$minutes} minute(s).");
            header('Location: ' . $baseUrl . '/auth');
            exit;
        }

        // Try to find user by username or email
        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            $user = $this->userModel->findByEmail($username);
        }

        // Verify user exists and password is correct
        if (!$user || !$this->userModel->verifyPassword($password, $user->password)) {
            // Track failed attempt
            $attempts = Session::trackLoginAttempt($username);
            $remainingAttempts = max(0, 5 - $attempts);

            if ($remainingAttempts > 0) {
                Session::setFlash('error', "Invalid credentials. {$remainingAttempts} attempt(s) remaining.");
            } else {
                Session::setFlash('error', 'Too many failed attempts. Account locked for 15 minutes.');
            }

            header('Location: ' . $baseUrl . '/auth');
            exit;
        }

        // Successful login
        Session::login($user);
        Session::resetLoginAttempts($username);

        // Update last login time
        $this->userModel->updateLastLogin($user->id);

        // Handle "Remember Me"
        if ($rememberMe) {
            $this->setRememberMeCookie($user->id);
        }

        // Redirect to admin dashboard
        Session::setFlash('success', 'Welcome back, ' . $user->first_name . '!');
        header('Location: ' . $baseUrl . '/admin');
        exit;
    }

    /**
     * Logout user
     */
    public function logout() {
        // Clear remember me cookie
        $this->clearRememberMeCookie();

        // Get user ID before destroying session
        $userId = Session::getUserId();

        // Clear remember token from database
        if ($userId) {
            $this->userModel->updateRememberToken($userId, null);
        }

        // Destroy session
        Session::logout();

        // Redirect to login
        Session::setFlash('success', 'You have been logged out successfully.');
        header('Location: /auth');
        exit;
    }

    /**
     * Set remember me cookie
     * @param int $userId
     */
    private function setRememberMeCookie($userId) {
        // Generate secure random token
        $token = bin2hex(random_bytes(32));

        // Hash the token before storing in database
        $hashedToken = hash('sha256', $token);

        // Store hashed token in database
        $this->userModel->updateRememberToken($userId, $hashedToken);

        // Set cookie with raw token (30 days)
        $cookieOptions = [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        setcookie('remember_me', $token, $cookieOptions);
    }

    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            $cookieOptions = [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            setcookie('remember_me', '', $cookieOptions);
            unset($_COOKIE['remember_me']);
        }
    }

    /**
     * Check and process remember me cookie
     */
    private function checkRememberMe() {
        require_once dirname(dirname(__DIR__)) . '/app/config/config.php';

        // Skip if already authenticated
        if (Session::isAuthenticated()) {
            return;
        }

        // Check if remember me cookie exists
        if (!isset($_COOKIE['remember_me'])) {
            return;
        }

        $token = $_COOKIE['remember_me'];
        $hashedToken = hash('sha256', $token);

        // Find user by remember token
        $user = $this->userModel->findByRememberToken($hashedToken);

        if ($user) {
            // Auto-login user
            Session::login($user);
            $this->userModel->updateLastLogin($user->id);

            // Regenerate remember token for security
            $this->setRememberMeCookie($user->id);

            // Redirect to admin
            header('Location: ' . $baseUrl . '/admin');
            exit;
        } else {
            // Invalid token, clear cookie
            $this->clearRememberMeCookie();
        }
    }

    /**
     * Display registration form
     */
    public function register() {
        require_once dirname(dirname(__DIR__)) . '/app/config/config.php';

        // Check if already authenticated
        if (Session::isAuthenticated()) {
            header('Location: ' . $baseUrl . '/admin');
            exit;
        }

        $data = [
            'title' => 'Sign Up - Vuyani Magibisela',
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'csrf_token' => Session::generateCsrfToken(),
            'baseUrl' => $baseUrl
        ];

        $this->view('auth/register', $data);
    }

    /**
     * Process registration form submission
     */
    public function processRegistration() {
        require_once dirname(dirname(__DIR__)) . '/app/config/config.php';

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $baseUrl . '/auth/register');
            exit;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($csrfToken)) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header('Location: ' . $baseUrl . '/auth/register');
            exit;
        }

        // Get registration data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');

        // Validate input
        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }

        // Check if username already exists
        if ($this->userModel->findByUsername($username)) {
            $errors[] = 'Username already taken.';
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email already registered.';
        }

        // If validation errors, redirect back
        if (!empty($errors)) {
            Session::setFlash('error', implode(' ', $errors));
            header('Location: ' . $baseUrl . '/auth/register');
            exit;
        }

        // Create new user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'user' // Default role
        ];

        $userId = $this->userModel->createBasicUser($userData);

        if ($userId) {
            Session::setFlash('success', 'Registration successful! Please login.');
            header('Location: ' . $baseUrl . '/auth');
            exit;
        } else {
            Session::setFlash('error', 'Registration failed. Please try again.');
            header('Location: ' . $baseUrl . '/auth/register');
            exit;
        }
    }

    /**
     * Password reset request (future implementation)
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Forgot Password - Vuyani Magibisela',
            'csrf_token' => Session::generateCsrfToken()
        ];

        // TODO: Implement password reset view
        Session::setFlash('info', 'Password reset functionality will be available soon.');
        header('Location: /auth');
        exit;
    }

    /**
     * Middleware to check authentication before accessing protected pages
     * Call this in controllers that require authentication
     */
    public static function requireAuth() {
        Session::init();

        // Check session timeout
        if (!Session::checkTimeout()) {
            Session::setFlash('error', 'Your session has expired. Please login again.');
            header('Location: /auth');
            exit;
        }

        // Check if authenticated
        if (!Session::isAuthenticated()) {
            Session::setFlash('error', 'Please login to access this page.');
            header('Location: /auth');
            exit;
        }
    }

    /**
     * Middleware to check admin role
     */
    public static function requireAdmin() {
        self::requireAuth();

        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Admin privileges required.');
            header('Location: /');
            exit;
        }
    }
}
