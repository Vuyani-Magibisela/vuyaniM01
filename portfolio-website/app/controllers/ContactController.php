<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Contact;

class ContactController extends BaseController {

    public function index() {
        // Load the view for the contact page
        $this->view('contact/index');
    }
    
    public function submit() {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contactModel = $this->model('Contact');
            
            // Sanitize input data
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'subject' => trim($_POST['subject'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
            ];
            
            // Basic validation
            $errors = [];
            
            if (empty($data['name'])) {
                $errors[] = 'Name is required';
            }
            
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
            
            if (empty($data['message'])) {
                $errors[] = 'Message is required';
            }
            
            if (strlen($data['message']) > 1000) {
                $errors[] = 'Message must be less than 1000 characters';
            }
            
            // If no errors, save to database
            if (empty($errors)) {
                $result = $contactModel->saveSubmission($data);

                if ($result) {
                    // Send email notifications
                    $logFile = dirname(dirname(__DIR__)) . '/logs/email-debug.log';
                    try {
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Creating Email service...\n", FILE_APPEND);
                        $emailService = new \App\Core\Email();
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Email service created. Last error: " . $emailService->getLastError() . "\n", FILE_APPEND);

                        // Send confirmation email to visitor
                        $confResult = $emailService->sendContactConfirmation([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'subject' => $data['subject'],
                            'message' => $data['message']
                        ]);
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Confirmation email result: " . ($confResult ? 'SUCCESS' : 'FAILED') . " Error: " . $emailService->getLastError() . "\n", FILE_APPEND);

                        // Send notification email to admin
                        $notifResult = $emailService->sendContactNotification([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'subject' => $data['subject'],
                            'message' => $data['message'],
                            'ip_address' => $data['ip_address'],
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Admin notification result: " . ($notifResult ? 'SUCCESS' : 'FAILED') . " Error: " . $emailService->getLastError() . "\n", FILE_APPEND);

                    } catch (\Exception $e) {
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
                    }

                    // Return JSON response for AJAX
                    if (isset($_POST['ajax'])) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Thank you for your message! I\'ll get back to you soon.']);
                        exit;
                    }
                    
                    // Redirect with success message
                    header('Location: ' . $this->getBaseUrl() . '/contact?success=1');
                    exit;
                } else {
                    $errors[] = 'Failed to send message. Please try again.';
                }
            }
            
            // Return errors for AJAX
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            
            // Load view with errors
            $this->view('contact/index', ['errors' => $errors, 'formData' => $data]);
        } else {
            // Redirect to contact page if not POST
            header('Location: ' . $this->getBaseUrl() . '/contact');
            exit;
        }
    }
    
    private function getBaseUrl() {
        // Get base URL from config
        require_once dirname(__DIR__, 2) . '/config/config.php';
        return $baseUrl;
    }
}