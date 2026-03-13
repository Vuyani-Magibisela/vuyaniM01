<?php

namespace App\Models;

use PDO;
use Exception;

class Subscriber extends BaseModel {
    private $table = 'subscribers';

    /**
     * Create a new subscription or return existing token
     *
     * @param string $email Subscriber email
     * @param string|null $ipAddress IP address
     * @param string|null $userAgent User agent string
     * @return string|false Verification token on success, false if already verified
     */
    public function subscribe($email, $ipAddress = null, $userAgent = null) {
        // Check if not connected
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Check if email already exists
            $existing = $this->getByEmail($email);

            if ($existing) {
                // If unsubscribed, allow re-subscription with new token
                if ($existing->status === 'unsubscribed') {
                    $token = bin2hex(random_bytes(32));
                    $query = "UPDATE {$this->table}
                             SET status = 'pending',
                                 verification_token = :token,
                                 subscribed_at = NOW(),
                                 ip_address = :ip,
                                 user_agent = :ua
                             WHERE email = :email";

                    $this->query($query, [
                        'token' => $token,
                        'email' => $email,
                        'ip' => $ipAddress,
                        'ua' => $userAgent
                    ], false);

                    return $token;
                }

                // If verified, return false (already subscribed)
                if ($existing->status === 'verified') {
                    return false;
                }

                // If pending, return existing token (allow resend)
                return $existing->verification_token;
            }

            // New subscription
            $token = bin2hex(random_bytes(32));
            $query = "INSERT INTO {$this->table}
                     (email, verification_token, status, ip_address, user_agent, subscribed_at)
                     VALUES (:email, :token, 'pending', :ip, :ua, NOW())";

            $this->query($query, [
                'email' => $email,
                'token' => $token,
                'ip' => $ipAddress,
                'ua' => $userAgent
            ], false);

            return $token;

        } catch (Exception $e) {
            error_log('Subscription error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify a subscription using token
     *
     * @param string $token Verification token
     * @return bool Success status
     */
    public function verify($token) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $subscriber = $this->getByToken($token);

            if (!$subscriber || $subscriber->status === 'verified') {
                return false;
            }

            $query = "UPDATE {$this->table}
                     SET status = 'verified', verified_at = NOW()
                     WHERE verification_token = :token";

            return $this->query($query, ['token' => $token], false);

        } catch (Exception $e) {
            error_log('Verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsubscribe using token
     *
     * @param string $token Verification token
     * @return bool Success status
     */
    public function unsubscribe($token) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $subscriber = $this->getByToken($token);

            if (!$subscriber) {
                return false;
            }

            $query = "UPDATE {$this->table}
                     SET status = 'unsubscribed', unsubscribed_at = NOW()
                     WHERE verification_token = :token";

            return $this->query($query, ['token' => $token], false);

        } catch (Exception $e) {
            error_log('Unsubscribe error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscriber by email
     *
     * @param string $email Email address
     * @return object|false Subscriber data or false
     */
    public function getByEmail($email) {
        if (!$this->isConnected()) {
            return false;
        }

        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        return $this->query($query, ['email' => $email], false);
    }

    /**
     * Get subscriber by token
     *
     * @param string $token Verification token
     * @return object|false Subscriber data or false
     */
    public function getByToken($token) {
        if (!$this->isConnected()) {
            return false;
        }

        $query = "SELECT * FROM {$this->table} WHERE verification_token = :token";
        return $this->query($query, ['token' => $token], false);
    }

    /**
     * Check if email is subscribed (verified)
     *
     * @param string $email Email address
     * @return bool True if verified subscriber
     */
    public function isSubscribed($email) {
        $subscriber = $this->getByEmail($email);
        return $subscriber && $subscriber->status === 'verified';
    }

    /**
     * Get all subscribers
     *
     * @return array Subscribers array
     */
    public function getAllSubscribers() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table} ORDER BY subscribed_at DESC";
        return $this->query($query, [], true);
    }

    /**
     * Get all verified subscribers
     *
     * @return array Subscribers array
     */
    public function getAllVerified() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table}
                 WHERE status = 'verified'
                 ORDER BY verified_at DESC";

        return $this->query($query, [], true);
    }

    /**
     * Get all pending subscribers
     *
     * @return array Subscribers array
     */
    public function getAllPending() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table}
                 WHERE status = 'pending'
                 ORDER BY subscribed_at DESC";

        return $this->query($query, [], true);
    }

    /**
     * Get all unsubscribed subscribers
     *
     * @return array Subscribers array
     */
    public function getAllUnsubscribed() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table}
                 WHERE status = 'unsubscribed'
                 ORDER BY unsubscribed_at DESC";

        return $this->query($query, [], true);
    }

    /**
     * Get subscriber count by status
     *
     * @param string|null $status Status filter (pending, verified, unsubscribed) or null for all
     * @return int Count
     */
    public function getCount($status = null) {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            if ($status) {
                $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = :status";
                $result = $this->query($query, ['status' => $status], false);
            } else {
                $query = "SELECT COUNT(*) as count FROM {$this->table}";
                $result = $this->query($query, [], false);
            }

            return $result ? (int)$result->count : 0;

        } catch (Exception $e) {
            error_log('Count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete subscriber by ID
     *
     * @param int $id Subscriber ID
     * @return bool Success status
     */
    public function deleteSubscriber($id) {
        if (!$this->isConnected()) {
            return false;
        }

        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->query($query, ['id' => $id], false);
    }
}
