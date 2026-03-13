<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service Class
 *
 * Handles all email sending functionality using PHPMailer with SMTP
 * Provides convenient methods for common email templates
 */
class Email {
    private $mailer;
    private $config;
    private $lastError = '';

    /**
     * Initialize email service with configuration
     */
    public function __construct() {
        $this->config = require dirname(__DIR__) . '/config/email.php';
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configure PHPMailer with SMTP settings
     */
    private function configure() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_encryption'];
            $this->mailer->Port = $this->config['smtp_port'];

            // From address
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            $this->lastError = 'Email configuration error: ' . $e->getMessage();
            error_log($this->lastError);
        }
    }

    /**
     * Send a basic email
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body HTML body
     * @param string $altBody Plain text alternative
     * @return bool Success status
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            // Clear any previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Set recipient
            $this->mailer->addAddress($to);

            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            // Send
            $result = $this->mailer->send();

            if (!$result) {
                $this->lastError = 'Failed to send email to: ' . $to;
                error_log($this->lastError);
            }

            return $result;

        } catch (Exception $e) {
            $this->lastError = 'Email send error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Send email using a template
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $template Template file name (without .php)
     * @param array $data Data to pass to template
     * @return bool Success status
     */
    public function sendTemplate($to, $subject, $template, $data = []) {
        try {
            // Get base URL for links in templates
            $data['baseUrl'] = $this->getBaseUrl();

            // Load and render template
            $body = $this->renderTemplate($template, $data);

            if ($body === false) {
                $this->lastError = 'Template not found: ' . $template;
                error_log($this->lastError);
                return false;
            }

            return $this->send($to, $subject, $body);

        } catch (Exception $e) {
            $this->lastError = 'Template email error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Send contact form confirmation to visitor
     *
     * @param array $contactData Contact form data
     * @return bool Success status
     */
    public function sendContactConfirmation($contactData) {
        return $this->sendTemplate(
            $contactData['email'],
            'Thank you for contacting us',
            'contact_confirmation',
            $contactData
        );
    }

    /**
     * Send contact form notification to admin
     *
     * @param array $contactData Contact form data
     * @return bool Success status
     */
    public function sendContactNotification($contactData) {
        return $this->sendTemplate(
            $this->config['admin_email'],
            'New Contact Form Submission',
            'contact_admin_notification',
            $contactData
        );
    }

    /**
     * Send subscription verification email
     *
     * @param string $email Subscriber email
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendSubscriptionVerification($email, $token) {
        $baseUrl = $this->getBaseUrl();
        $verificationUrl = $baseUrl . '/subscription/verify/' . $token;

        return $this->sendTemplate(
            $email,
            'Please verify your blog subscription',
            'subscription_verification',
            [
                'email' => $email,
                'verificationUrl' => $verificationUrl,
                'token' => $token
            ]
        );
    }

    /**
     * Send welcome email after subscription verification
     *
     * @param string $email Subscriber email
     * @param string $unsubscribeToken Unsubscribe token
     * @return bool Success status
     */
    public function sendSubscriptionWelcome($email, $unsubscribeToken) {
        $baseUrl = $this->getBaseUrl();
        $unsubscribeUrl = $baseUrl . '/subscription/unsubscribe/' . $unsubscribeToken;

        return $this->sendTemplate(
            $email,
            'Welcome to the blog!',
            'subscription_welcome',
            [
                'email' => $email,
                'unsubscribeUrl' => $unsubscribeUrl
            ]
        );
    }

    /**
     * Send new blog post notification to a subscriber
     *
     * @param string $email Subscriber email
     * @param string $unsubscribeToken Unsubscribe token
     * @param array $postData Post data (title, excerpt, slug, featured_image)
     * @return bool Success status
     */
    public function sendNewPostNotification($email, $unsubscribeToken, $postData) {
        $baseUrl = $this->getBaseUrl();
        $unsubscribeUrl = $baseUrl . '/subscription/unsubscribe/' . $unsubscribeToken;
        $articleUrl = $baseUrl . '/blog/article/' . ($postData['slug'] ?? '');

        return $this->sendTemplate(
            $email,
            'New Post: ' . ($postData['title'] ?? 'New Blog Post'),
            'new_post_notification',
            [
                'email' => $email,
                'title' => $postData['title'] ?? '',
                'excerpt' => $postData['excerpt'] ?? '',
                'slug' => $postData['slug'] ?? '',
                'featured_image' => $postData['featured_image'] ?? null,
                'articleUrl' => $articleUrl,
                'unsubscribeUrl' => $unsubscribeUrl
            ]
        );
    }

    /**
     * Render an email template
     *
     * @param string $template Template filename (without .php)
     * @param array $data Data to extract into template scope
     * @return string|false Rendered HTML or false on error
     */
    private function renderTemplate($template, $data = []) {
        $templatePath = dirname(__DIR__) . '/views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            return false;
        }

        // Extract data into local scope
        extract($data);

        // Start output buffering
        ob_start();

        // Include template
        include $templatePath;

        // Get and return rendered content
        return ob_get_clean();
    }

    /**
     * Get base URL for the application
     *
     * @return string Base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Detect local vs production environment
        if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
            return $protocol . '://' . $host . '/vuyaniM01/portfolio-website/public';
        } else {
            return $protocol . '://' . $host . '/public';
        }
    }

    /**
     * Get last error message
     *
     * @return string Error message
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Test email configuration by sending a test email
     *
     * @param string $testEmail Email address to send test to
     * @return bool Success status
     */
    public function sendTestEmail($testEmail) {
        $subject = 'Email Configuration Test';
        $body = '<h1>Test Email</h1><p>If you received this email, your email configuration is working correctly!</p>';

        return $this->send($testEmail, $subject, $body);
    }
}
