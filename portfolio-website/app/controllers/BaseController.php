<?php
/**
 * Base Controller
 * Loads models and views
 */

class BaseController {
    /**
     * Load model
     * @param string $model The model name
     * @return object The model instance
     */
    public function model($model) {
        // Check if model file exists
        if(file_exists('../app/models/' . $model . '.php')) {
            // Require model file
            require_once '../app/models/' . $model . '.php';
            
            // Instantiate model
            return new $model();
        } else {
            // Model not found
            die('Model ' . $model . ' not found');
        }
    }
    
    /**
     * Load view
     * @param string $view The view name
     * @param array $data The data to pass to the view
     * @return void
     */
    public function view($view, $data = []) {
        // Check if view file exists
        if(file_exists('../app/views/' . $view . '.php')) {
            // Extract data to make it available in the view
            extract($data);
            
            // Require view file
            require_once '../app/views/' . $view . '.php';
        } else {
            // View not found
            die('View ' . $view . ' not found');
        }
    }
    
    /**
     * JSON response
     * @param mixed $data The data to return as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to another page
     * @param string $url The URL to redirect to
     * @return void
     */
    public function redirect($url) {
        header('Location: ' . URL_ROOT . '/' . $url);
        exit;
    }
    
    /**
     * Set flash message in session
     * @param string $name Message name
     * @param string $message Message content
     * @param string $class CSS class for the message
     * @return void
     */
    public function setFlash($name, $message, $class = 'alert alert-success') {
        $_SESSION['flash'][$name] = [
            'message' => $message,
            'class' => $class
        ];
    }
    
    /**
     * Get and remove flash message from session
     * @param string $name Message name
     * @return mixed The flash message or false if not exists
     */
    public function getFlash($name) {
        if(isset($_SESSION['flash'][$name])) {
            $flash = $_SESSION['flash'][$name];
            unset($_SESSION['flash'][$name]);
            return $flash;
        }
        
        return false;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     * @return bool
     */
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Require user to be logged in
     * @return void Redirects if not logged in
     */
    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            $this->setFlash('login_required', 'Please login to access this page', 'alert alert-danger');
            $this->redirect('auth/login');
        }
    }
    
    /**
     * Require user to be admin
     * @return void Redirects if not admin
     */
    public function requireAdmin() {
        if(!$this->isAdmin()) {
            $this->setFlash('admin_required', 'You are not authorized to access this page', 'alert alert-danger');
            $this->redirect('');
        }
    }
}