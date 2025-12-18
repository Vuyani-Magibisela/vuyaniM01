<?php

namespace App\Models;

use Exception;

class User extends BaseModel {
    private $table = 'users';

    /**
     * Find user by username
     * @param string $username
     * @return object|null
     */
    public function findByUsername($username) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1 LIMIT 1";
        $result = $this->query($query, ['username' => $username], false);
        return $result ? (object)$result : null;
    }

    /**
     * Find user by email
     * @param string $email
     * @return object|null
     */
    public function findByEmail($email) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1 LIMIT 1";
        $result = $this->query($query, ['email' => $email], false);
        return $result ? (object)$result : null;
    }

    /**
     * Find user by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        if (!$this->isConnected()) {
            return null;
        }

        $result = parent::getById($this->table, $id);
        return $result ? (object)$result : null;
    }

    /**
     * Find user by remember token
     * @param string $token
     * @return object|null
     */
    public function findByRememberToken($token) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE remember_token = :token AND is_active = 1 LIMIT 1";
        $result = $this->query($query, ['token' => $token], false);
        return $result ? (object)$result : null;
    }

    /**
     * Create new user (admin only)
     * @param array $data
     * @return int|false User ID or false on failure
     */
    public function createUser($data) {
        if (!$this->isConnected()) {
            error_log('User creation attempted without database connection');
            return false;
        }

        try {
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            $query = "INSERT INTO {$this->table}
                      (username, email, password, first_name, last_name, role, is_active, created_at)
                      VALUES (:username, :email, :password, :first_name, :last_name, :role, :is_active, NOW())";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => $data['role'] ?? 'user',
                'is_active' => $data['is_active'] ?? 1
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log('User creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password against hash
     * @param string $password Plain text password
     * @param string $hash Hashed password from database
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Update remember token for persistent login
     * @param int $userId
     * @param string|null $token Token or null to clear
     * @return bool
     */
    public function updateRememberToken($userId, $token = null) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $query = "UPDATE {$this->table} SET remember_token = :token WHERE id = :id";
            return $this->query($query, ['token' => $token, 'id' => $userId], false) !== false;
        } catch (Exception $e) {
            error_log('Remember token update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $query = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = :id";
            return $this->query($query, ['id' => $userId], false) !== false;
        } catch (Exception $e) {
            error_log('Last login update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get failed login attempts for a user
     * Note: This is tracked in session storage via Session class
     * @return int
     */
    public function getFailedAttempts() {
        // This will be tracked in session storage
        // Return 0 here, actual tracking in AuthController
        return 0;
    }

    /**
     * Update user password
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        if (!$this->isConnected()) {
            error_log('Password update error: No database connection');
            return false;
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            $query = "UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id";
            $params = ['password' => $hashedPassword, 'id' => $userId];

            error_log('Password update query: ' . $query);
            error_log('Password update params (id only): ' . $userId);

            $result = $this->query($query, $params, false);
            error_log('Password update result: ' . json_encode($result));

            return $result !== false;
        } catch (Exception $e) {
            error_log('Password update error: ' . $e->getMessage());
            error_log('Password update stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get all active admin users
     * @return array
     */
    public function getAllAdmins() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT id, username, email, first_name, last_name, role, created_at, updated_at
                  FROM {$this->table}
                  WHERE role = 'admin' AND is_active = 1
                  ORDER BY created_at DESC";

        return $this->query($query);
    }

    /**
     * Deactivate user account
     * @param int $userId
     * @return bool
     */
    public function deactivate($userId) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $query = "UPDATE {$this->table} SET is_active = 0, updated_at = NOW() WHERE id = :id";
            return $this->query($query, ['id' => $userId], false) !== false;
        } catch (Exception $e) {
            error_log('User deactivation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate user account
     * @param int $userId
     * @return bool
     */
    public function activate($userId) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $query = "UPDATE {$this->table} SET is_active = 1, updated_at = NOW() WHERE id = :id";
            return $this->query($query, ['id' => $userId], false) !== false;
        } catch (Exception $e) {
            error_log('User activation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new user (basic registration)
     */
    public function createBasicUser($data) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            $query = "INSERT INTO {$this->table} (username, email, password, role, created_at, updated_at)
                      VALUES (:username, :email, :password, :role, NOW(), NOW())";

            $params = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'role' => $data['role'] ?? 'user'
            ];

            $result = $this->query($query, $params, false);

            if ($result !== false) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log('User creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user information
     */
    public function updateUser($userId, $data) {
        if (!$this->isConnected()) {
            error_log('User update error: No database connection');
            return false;
        }

        try {
            $query = "UPDATE {$this->table}
                      SET username = :username,
                          email = :email,
                          role = :role,
                          updated_at = NOW()
                      WHERE id = :id";

            $params = [
                'id' => $userId,
                'username' => $data['username'],
                'email' => $data['email'],
                'role' => $data['role'] ?? 'editor'
            ];

            error_log('User update query: ' . $query);
            error_log('User update params: ' . json_encode($params));

            $result = $this->query($query, $params, false);
            error_log('User update result: ' . json_encode($result));

            return $result !== false;
        } catch (Exception $e) {
            error_log('User update error: ' . $e->getMessage());
            error_log('User update stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            return $this->query($query, ['id' => $userId], false) !== false;
        } catch (Exception $e) {
            error_log('User deletion error: ' . $e->getMessage());
            return false;
        }
    }
}
