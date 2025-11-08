<?php
/**
 * Authentication System Test Script
 * Tests all major authentication features
 */

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Session.php';
require_once __DIR__ . '/app/models/BaseModel.php';
require_once __DIR__ . '/app/models/User.php';

use App\Core\Session;
use App\Models\User;

echo "=================================\n";
echo "Authentication System Test\n";
echo "=================================\n\n";

// Initialize session
Session::init();

// Create User model instance
$userModel = new User();

echo "Test 1: Find User by Username\n";
echo "--------------------------------\n";
$user = $userModel->findByUsername('admin');
if ($user) {
    echo "✓ User found!\n";
    echo "  ID: {$user->id}\n";
    echo "  Username: {$user->username}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role: {$user->role}\n";
    echo "  Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ User not found\n";
}
echo "\n";

echo "Test 2: Password Verification\n";
echo "--------------------------------\n";
$testPassword = 'Admin@2025';
if ($user) {
    $isValid = $userModel->verifyPassword($testPassword, $user->password);
    if ($isValid) {
        echo "✓ Password verification successful\n";
    } else {
        echo "✗ Password verification failed\n";
    }
} else {
    echo "✗ Cannot test - user not found\n";
}
echo "\n";

echo "Test 3: Session Management\n";
echo "--------------------------------\n";
if ($user) {
    Session::login($user);
    echo "✓ User logged in\n";
    echo "  Session User ID: " . Session::getUserId() . "\n";
    echo "  Session Username: " . Session::get('username') . "\n";
    echo "  Is Authenticated: " . (Session::isAuthenticated() ? 'Yes' : 'No') . "\n";
    echo "  Is Admin: " . (Session::isAdmin() ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Cannot test - user not found\n";
}
echo "\n";

echo "Test 4: Remember Token\n";
echo "--------------------------------\n";
$token = bin2hex(random_bytes(32));
$hashedToken = hash('sha256', $token);
$tokenUpdated = $userModel->updateRememberToken($user->id, $hashedToken);
if ($tokenUpdated) {
    echo "✓ Remember token set\n";

    // Try to find user by token
    $userByToken = $userModel->findByRememberToken($hashedToken);
    if ($userByToken) {
        echo "✓ User found by remember token\n";
        echo "  Username: {$userByToken->username}\n";
    } else {
        echo "✗ User not found by token\n";
    }
} else {
    echo "✗ Failed to set remember token\n";
}
echo "\n";

echo "Test 5: Login Attempt Tracking\n";
echo "--------------------------------\n";
$identifier = 'testuser';
$attempts1 = Session::trackLoginAttempt($identifier);
$attempts2 = Session::trackLoginAttempt($identifier);
$attempts3 = Session::trackLoginAttempt($identifier);
echo "✓ Tracked login attempts\n";
echo "  Attempt 1: {$attempts1} failed\n";
echo "  Attempt 2: {$attempts2} failed\n";
echo "  Attempt 3: {$attempts3} failed\n";

$isLocked = Session::isLoginLocked($identifier);
echo "  Is Locked: " . ($isLocked ? 'No (needs 5 attempts)' : 'No') . "\n";

// Track 2 more to trigger lock
Session::trackLoginAttempt($identifier);
Session::trackLoginAttempt($identifier);
$isLocked = Session::isLoginLocked($identifier);
echo "  After 5 attempts: " . ($isLocked ? 'Locked' : 'Not Locked') . "\n";

if ($isLocked) {
    $remaining = Session::getLockoutRemaining($identifier);
    echo "  Lockout time remaining: " . ceil($remaining / 60) . " minutes\n";
}
echo "\n";

echo "Test 6: Session Timeout\n";
echo "--------------------------------\n";
$timeoutValid = Session::checkTimeout();
echo "✓ Session timeout check: " . ($timeoutValid ? 'Valid' : 'Expired') . "\n";
echo "  Last activity: " . date('Y-m-d H:i:s', Session::get('LAST_ACTIVITY')) . "\n";
echo "\n";

echo "Test 7: Flash Messages\n";
echo "--------------------------------\n";
Session::setFlash('success', 'This is a test success message');
Session::setFlash('error', 'This is a test error message');
$successMsg = Session::getFlash('success');
$errorMsg = Session::getFlash('error');
echo "✓ Flash messages set and retrieved\n";
echo "  Success: {$successMsg}\n";
echo "  Error: {$errorMsg}\n";
// Try to get again (should be null)
$successMsg2 = Session::getFlash('success');
echo "  Second retrieval: " . ($successMsg2 === null ? 'null (correct)' : 'still exists (wrong)') . "\n";
echo "\n";

echo "Test 8: CSRF Token\n";
echo "--------------------------------\n";
$csrfToken = Session::generateCsrfToken();
echo "✓ CSRF token generated: " . substr($csrfToken, 0, 16) . "...\n";
$isValid = Session::verifyCsrfToken($csrfToken);
echo "  Verification: " . ($isValid ? 'Valid' : 'Invalid') . "\n";
$isInvalid = Session::verifyCsrfToken('invalid_token');
echo "  Invalid token test: " . ($isInvalid ? 'Accepted (wrong!)' : 'Rejected (correct)') . "\n";
echo "\n";

echo "=================================\n";
echo "All Tests Completed!\n";
echo "=================================\n";
echo "\nYou can now test in the browser:\n";
echo "1. Visit: http://localhost:8000/auth\n";
echo "2. Login with:\n";
echo "   Username: admin\n";
echo "   Password: Admin@2025\n";
echo "3. You should be redirected to: http://localhost:8000/admin\n";
echo "\n";

// Clean up
Session::logout();
echo "Session cleaned up.\n";
