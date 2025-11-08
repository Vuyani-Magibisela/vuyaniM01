<?php
/**
 * Script to create a test admin user
 * Run this once to create your first admin account
 */

// Set CLI environment to localhost for database config
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/app/core/Database.php';

try {
    $db = App\Core\Database::connect();

    // Test user credentials
    $username = 'admin';
    $email = 'admin@vuyanimagibisela.co.za';
    $password = 'Admin@2025'; // Change this to your desired password
    $firstName = 'Vuyani';
    $lastName = 'Magibisela';

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Check if user already exists
    $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute(['username' => $username, 'email' => $email]);

    if ($stmt->fetch()) {
        echo "✓ User already exists!\n";
        echo "  Username: {$username}\n";
        echo "  Email: {$email}\n";
        echo "\n";
        echo "You can login with these credentials.\n";
        exit;
    }

    // Insert new user
    $query = "INSERT INTO users (username, email, password, first_name, last_name, role, is_active, created_at)
              VALUES (:username, :email, :password, :first_name, :last_name, 'admin', 1, NOW())";

    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'first_name' => $firstName,
        'last_name' => $lastName
    ]);

    if ($result) {
        echo "✓ Test admin user created successfully!\n\n";
        echo "Login Credentials:\n";
        echo "==================\n";
        echo "Username: {$username}\n";
        echo "Email:    {$email}\n";
        echo "Password: {$password}\n";
        echo "\n";
        echo "Login URL: http://localhost:8000/auth\n";
        echo "\n";
        echo "IMPORTANT: Change the password after first login!\n";
    } else {
        echo "✗ Failed to create user.\n";
    }

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. MySQL is running\n";
    echo "2. Database 'vuyanim' exists\n";
    echo "3. Users table has been created\n";
    echo "4. Database credentials in app/config/database.php are correct\n";
}
