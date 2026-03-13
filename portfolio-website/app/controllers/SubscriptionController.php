<?php

namespace App\Controllers;

use App\Core\Email;
use App\Models\Subscriber;

class SubscriptionController extends BaseController {

    /**
     * Handle subscription request (AJAX)
     * POST /subscription/subscribe
     */
    public function subscribe() {
        // Suppress display_errors for JSON endpoint — warnings corrupt JSON output
        ini_set('display_errors', 0);
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $subscriberModel = $this->model('Subscriber');

        // Check if already verified
        if ($subscriberModel->isSubscribed($email)) {
            echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our blog.']);
            exit;
        }

        // Create subscription
        $token = $subscriberModel->subscribe(
            $email,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if ($token) {
            // Send verification email
            try {
                error_log('[Subscription] Attempting to send verification email to: ' . $email);
                $emailService = new Email();
                $result = $emailService->sendSubscriptionVerification($email, $token);
                error_log('[Subscription] Email send result for ' . $email . ': ' . ($result ? 'SUCCESS' : 'FAILED'));

                if ($result === false) {
                    $lastError = method_exists($emailService, 'getLastError') ? $emailService->getLastError() : 'unknown';
                    error_log('[Subscription] Email send failed. Last error: ' . $lastError);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Could not send verification email. Please try again later.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Please check your email to confirm your subscription.'
                    ]);
                }
            } catch (\Exception $e) {
                error_log('[Subscription] Email exception for ' . $email . ': ' . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'An error occurred while sending the verification email. Please try again later.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }

        exit;
    }

    /**
     * Verify subscription via email link
     * GET /subscription/verify/{token}
     */
    public function verify($token) {
        $subscriberModel = $this->model('Subscriber');

        if ($subscriberModel->verify($token)) {
            // Get subscriber data to send welcome email
            $subscriber = $subscriberModel->getByToken($token);

            if ($subscriber) {
                // Send welcome email
                try {
                    $emailService = new Email();
                    $emailService->sendSubscriptionWelcome($subscriber->email, $token);
                } catch (\Exception $e) {
                    error_log('Welcome email error: ' . $e->getMessage());
                }
            }

            // Redirect to blog with success message
            header('Location: ' . $this->getBaseUrl() . '/blog?subscribed=1');
        } else {
            // Redirect with error
            header('Location: ' . $this->getBaseUrl() . '/blog?subscription_error=1');
        }

        exit;
    }

    /**
     * Unsubscribe via email link
     * GET /subscription/unsubscribe/{token}
     */
    public function unsubscribe($token) {
        $subscriberModel = $this->model('Subscriber');

        if ($subscriberModel->unsubscribe($token)) {
            // Show unsubscribe confirmation page
            $this->view('subscription/unsubscribed', [
                'message' => 'You have been successfully unsubscribed from our blog.'
            ]);
        } else {
            // Show error page
            $this->view('subscription/unsubscribed', [
                'error' => 'Invalid unsubscribe link. The link may have expired or is invalid.'
            ]);
        }
    }

    /**
     * Temporary SMTP diagnostic endpoint
     * GET /subscription/testEmail
     * TODO: Remove after confirming SMTP works
     */
    public function testEmail() {
        ini_set('display_errors', 0);
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            error_log('[SMTP-Test] Starting SMTP diagnostic test');
            $emailService = new Email();
            $result = $emailService->sendSubscriptionVerification('admin@vuyanimagibisela.co.za', 'test-token-diagnostic');
            $lastError = method_exists($emailService, 'getLastError') ? $emailService->getLastError() : null;

            error_log('[SMTP-Test] Result: ' . ($result ? 'SUCCESS' : 'FAILED') . ($lastError ? ' Error: ' . $lastError : ''));

            echo json_encode([
                'smtp_test' => true,
                'send_result' => $result ? 'success' : 'failed',
                'error' => $lastError,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            error_log('[SMTP-Test] Exception: ' . $e->getMessage());
            echo json_encode([
                'smtp_test' => true,
                'send_result' => 'exception',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        exit;
    }

    /**
     * Get base URL for the application
     *
     * @return string Base URL
     */
    private function getBaseUrl() {
        require_once dirname(__DIR__) . '/config/config.php';
        return $baseUrl;
    }
}
