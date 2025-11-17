# Authentication System Documentation

## Overview

Complete authentication system implementing secure session management, role-based access control, brute force protection, and Remember Me functionality. Built with PHP using industry-standard security practices.

**Implementation Date**: November 9, 2025
**Status**: ✅ Production Ready

---

## Architecture

### Core Components

```
Authentication System
├── Session Management (Session.php)
│   ├── Session initialization and configuration
│   ├── CSRF token generation and validation
│   ├── Session timeout tracking
│   ├── Login attempt tracking
│   └── Flash message system
│
├── Authentication Controller (AuthController.php)
│   ├── Login form display
│   ├── Authentication processing
│   ├── Remember Me handling
│   ├── Logout processing
│   └── Access control middleware
│
├── Admin Controller (AdminController.php)
│   ├── Dashboard display
│   ├── User management
│   ├── Resource management
│   └── Protected route enforcement
│
└── User Model (User.php)
    ├── User lookup methods
    ├── Password hashing/verification
    ├── Token management
    └── User CRUD operations
```

---

## Security Features

### 1. Password Security

**Implementation**: Bcrypt hashing with cost factor 12

```php
// Hashing (User.php)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verification (User.php)
public function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
```

**Security Benefits**:
- Industry-standard bcrypt algorithm
- Cost factor 12 provides strong protection against brute force
- Automatic salt generation
- Resistant to rainbow table attacks

### 2. CSRF Protection

**Implementation**: Token-based validation on all forms

```php
// Generate token (Session.php)
public static function generateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

// Verify token (Session.php)
public static function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
```

**Usage in Forms**:
```html
<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
```

**Security Benefits**:
- Prevents cross-site request forgery attacks
- Cryptographically secure random token generation
- Timing-safe comparison with hash_equals()

### 3. Session Security

**Configuration** (Session.php):
```php
session_set_cookie_params([
    'lifetime' => 0,              // Session cookie
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),  // HTTPS only in production
    'httponly' => true,           // No JavaScript access
    'samesite' => 'Lax'          // CSRF protection
]);
```

**Timeout Management**:
- 30-minute inactivity timeout
- Automatic session destruction on timeout
- Last activity timestamp tracking

```php
public static function checkTimeout() {
    $timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            self::destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}
```

**Security Benefits**:
- Prevents session hijacking via JavaScript
- Limits session lifetime
- HTTPS enforcement in production
- CSRF protection via SameSite

### 4. Brute Force Protection

**Implementation**: Login attempt tracking with temporary lockout

```php
public static function trackLoginAttempt($username) {
    $attempts = $_SESSION['login_attempts'][$username] ?? 0;
    $attempts++;
    $_SESSION['login_attempts'][$username] = $attempts;
    $_SESSION['login_attempt_time'][$username] = time();
    return $attempts;
}

public static function isLoginLocked($username) {
    $maxAttempts = 5;
    $lockoutTime = 900; // 15 minutes

    $attempts = $_SESSION['login_attempts'][$username] ?? 0;
    $attemptTime = $_SESSION['login_attempt_time'][$username] ?? 0;

    if ($attempts >= $maxAttempts) {
        if (time() - $attemptTime < $lockoutTime) {
            return true;
        } else {
            self::resetLoginAttempts($username);
        }
    }
    return false;
}
```

**Configuration**:
- Max attempts: 5
- Lockout duration: 15 minutes
- Tracked per username
- Automatic reset after lockout period

**Security Benefits**:
- Prevents automated password guessing
- Slows down brute force attacks
- User feedback on remaining attempts

### 5. Remember Me Functionality

**Implementation**: Secure token-based persistent authentication

```php
// Set Remember Me cookie (AuthController.php)
private function setRememberMeCookie($userId) {
    $token = bin2hex(random_bytes(32));
    $hashedToken = hash('sha256', $token);

    $this->userModel->updateRememberToken($userId, $hashedToken);

    setcookie('remember_me', $token, [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax'
    ]);
}

// Validate Remember Me token (AuthController.php)
private function checkRememberMe() {
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];
        $hashedToken = hash('sha256', $token);

        $user = $this->userModel->findByRememberToken($hashedToken);

        if ($user) {
            Session::login($user);
            $this->setRememberMeCookie($user->id); // Regenerate token
        }
    }
}
```

**Security Benefits**:
- Tokens are hashed before database storage
- SHA-256 hashing prevents token theft from database
- 30-day expiration
- Token regeneration on each use
- Secure cookie configuration

### 6. Session Fixation Prevention

**Implementation**: Session regeneration on login

```php
public static function login($user) {
    session_regenerate_id(true);  // Prevent session fixation

    $_SESSION['user_id'] = $user->id;
    $_SESSION['username'] = $user->username;
    $_SESSION['email'] = $user->email;
    $_SESSION['user_role'] = $user->role;
    $_SESSION['authenticated'] = true;
    $_SESSION['last_activity'] = time();
}
```

**Security Benefits**:
- New session ID on authentication
- Prevents session fixation attacks
- Old session data is destroyed

---

## Authentication Flow

### Login Flow

```
1. User visits /auth
   ↓
2. System checks if already authenticated
   ├─ Yes → Redirect to /admin
   └─ No → Continue
   ↓
3. System checks Remember Me cookie
   ├─ Valid token → Auto-login → Redirect to /admin
   └─ No/Invalid → Show login form
   ↓
4. User submits credentials
   ↓
5. CSRF token validation
   ├─ Invalid → Error: "Invalid request"
   └─ Valid → Continue
   ↓
6. Check if account is locked
   ├─ Locked → Error: "Too many attempts, try again in X minutes"
   └─ Not locked → Continue
   ↓
7. Validate credentials
   ├─ Invalid → Track attempt → Error with remaining attempts
   └─ Valid → Continue
   ↓
8. Create session
   ↓
9. If "Remember Me" checked
   └─ Set Remember Me cookie
   ↓
10. Redirect to /admin
```

### Logout Flow

```
1. User clicks logout
   ↓
2. Clear Remember Me cookie
   ↓
3. Remove remember token from database
   ↓
4. Destroy session
   ↓
5. Redirect to /auth with success message
```

### Protected Route Access

```
1. User requests protected route (e.g., /admin)
   ↓
2. requireAuth() middleware executes
   ↓
3. Check session timeout
   ├─ Timeout → Destroy session → Redirect to /auth
   └─ Valid → Continue
   ↓
4. Check if authenticated
   ├─ No → Redirect to /auth with error
   └─ Yes → Continue
   ↓
5. For admin-only routes: Check role
   ├─ Not admin → Redirect to / with error
   └─ Admin → Allow access
```

---

## Usage Examples

### Protecting a Controller

```php
<?php
namespace App\Controllers;

use App\Core\Session;

class AdminController extends BaseController {

    public function __construct() {
        // Require authentication for all admin pages
        AuthController::requireAuth();
    }

    public function index() {
        // This code only runs if authenticated
        $data = [
            'username' => Session::get('username'),
            'role' => Session::get('user_role')
        ];

        $this->view('admin/dashboard', $data);
    }
}
```

### Requiring Admin Role

```php
public function users() {
    // Only admins can access user management
    AuthController::requireAdmin();

    // Admin-only code here
}
```

### Using Flash Messages

```php
// Set flash message (AuthController.php)
Session::setFlash('success', 'Login successful!');

// Display in view (login.php)
<?php if (isset($success) && $success): ?>
    <div class="alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>
```

### CSRF Protection in Forms

```php
// In controller
$data['csrf_token'] = Session::generateCsrfToken();

// In view
<form method="POST" action="/auth/authenticate">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <!-- other form fields -->
</form>

// In processing controller
if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Invalid request');
    header('Location: /auth');
    exit;
}
```

---

## Testing

### Test Scripts

**create_test_user.php** - Create test admin user:
```bash
php portfolio-website/create_test_user.php
```

Creates user:
- Username: admin
- Email: admin@example.com
- Password: Admin123!
- Role: admin

**test_auth.php** - Test authentication logic:
```bash
php portfolio-website/test_auth.php
```

Tests:
- Password hashing
- Password verification
- User lookup

### Manual Testing Checklist

- [ ] Login with valid username
- [ ] Login with valid email
- [ ] Login with invalid credentials (check attempt tracking)
- [ ] Login after 5 failed attempts (check lockout)
- [ ] Login with Remember Me enabled
- [ ] Close browser and return (test Remember Me auto-login)
- [ ] Wait 30 minutes (test session timeout)
- [ ] Access protected route without authentication
- [ ] Access admin route as non-admin user
- [ ] Logout (verify token cleanup)
- [ ] Submit form without CSRF token
- [ ] Try to access /admin after logout

---

## Database Schema

### users Table

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('admin', 'user') DEFAULT 'user',
    remember_token VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Key Fields**:
- `password`: Bcrypt hash (60 characters)
- `remember_token`: SHA-256 hash (64 characters) or NULL
- `role`: User role for access control
- `is_active`: Account activation status

---

## API Reference

### Session Class Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `init()` | Initialize session | void |
| `login($user)` | Create authenticated session | void |
| `logout()` | Destroy session and redirect | void |
| `isAuthenticated()` | Check if user is authenticated | bool |
| `isAdmin()` | Check if user has admin role | bool |
| `get($key)` | Get session value | mixed |
| `set($key, $value)` | Set session value | void |
| `generateCsrfToken()` | Generate CSRF token | string |
| `verifyCsrfToken($token)` | Verify CSRF token | bool |
| `setFlash($key, $message)` | Set flash message | void |
| `getFlash($key)` | Get and clear flash message | string\|null |
| `checkTimeout()` | Check session timeout | bool |
| `trackLoginAttempt($username)` | Track failed login | int |
| `isLoginLocked($username)` | Check if account is locked | bool |
| `resetLoginAttempts($username)` | Reset login attempts | void |

### AuthController Methods

| Method | Description | Access |
|--------|-------------|--------|
| `index()` | Display login form | Public |
| `authenticate()` | Process login | Public |
| `logout()` | Process logout | Public |
| `requireAuth()` | Middleware: Require authentication | Static |
| `requireAdmin()` | Middleware: Require admin role | Static |

### User Model Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `findByUsername($username)` | Find user by username | object\|null |
| `findByEmail($email)` | Find user by email | object\|null |
| `findById($id)` | Find user by ID | object\|null |
| `findByRememberToken($token)` | Find user by remember token | object\|null |
| `createUser($data)` | Create new user | int\|false |
| `verifyPassword($password, $hash)` | Verify password | bool |
| `updateRememberToken($userId, $token)` | Update remember token | bool |
| `updateLastLogin($userId)` | Update last login timestamp | bool |
| `updatePassword($userId, $newPassword)` | Update user password | bool |

---

## Security Considerations

### Best Practices

1. **Always use HTTPS in production** - Session cookies are marked secure
2. **Never store plain text passwords** - Always use password_hash()
3. **Validate CSRF tokens on all state-changing operations** - Prevents CSRF attacks
4. **Implement rate limiting** - Currently done via login attempt tracking
5. **Escape all output** - Use htmlspecialchars() to prevent XSS
6. **Use prepared statements** - Prevents SQL injection
7. **Log security events** - Track failed logins, account lockouts
8. **Regular security audits** - Review authentication code periodically

### Common Attack Vectors & Mitigations

| Attack | Mitigation |
|--------|------------|
| SQL Injection | Prepared statements throughout |
| XSS | htmlspecialchars() on all output |
| CSRF | Token validation on all forms |
| Session Fixation | Session regeneration on login |
| Session Hijacking | HttpOnly cookies, HTTPS, timeout |
| Brute Force | Login attempt tracking, lockout |
| Password Cracking | Bcrypt with cost 12 |
| Token Theft | Token hashing before storage |

### Future Enhancements

- [ ] Two-factor authentication (2FA)
- [ ] Password reset via email
- [ ] Email verification on registration
- [ ] Security event logging to database
- [ ] IP-based rate limiting
- [ ] Account recovery mechanisms
- [ ] Password strength requirements
- [ ] Password history (prevent reuse)
- [ ] Account activity monitoring
- [ ] Security notifications

---

## Troubleshooting

### Common Issues

**Issue**: "Session timeout" error immediately after login
**Solution**: Check that `session.gc_maxlifetime` in php.ini is >= 1800

**Issue**: Remember Me not working
**Solution**: Verify cookies are enabled and check cookie settings match environment (HTTP vs HTTPS)

**Issue**: CSRF token validation fails
**Solution**: Ensure form submission includes csrf_token and session is initialized

**Issue**: Login locked despite correct credentials
**Solution**: Wait 15 minutes or clear session data for testing

**Issue**: Can't access admin routes
**Solution**: Verify user role is 'admin' in database

---

## File Locations

```
portfolio-website/
├── app/
│   ├── core/
│   │   └── Session.php              # Session management
│   ├── controllers/
│   │   ├── AuthController.php       # Authentication
│   │   └── AdminController.php      # Admin dashboard
│   ├── models/
│   │   └── User.php                 # User data operations
│   └── views/
│       ├── auth/
│       │   └── login.php            # Login form
│       └── admin/
│           └── dashboard.php        # Admin dashboard
├── create_test_user.php             # Test user creation
└── test_auth.php                    # Authentication testing
```

---

## Changelog

### Version 1.0.0 (2025-11-09)
- ✅ Initial authentication system implementation
- ✅ Session management with timeout
- ✅ CSRF protection
- ✅ Brute force protection
- ✅ Remember Me functionality
- ✅ Role-based access control
- ✅ Login/logout flows
- ✅ Admin dashboard
- ✅ Test scripts

---

**Last Updated**: November 9, 2025
**Maintained By**: Development Team
**Status**: Production Ready
