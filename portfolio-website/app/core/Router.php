<?php
/**
 * Router Class
 * URL routing and controller dispatch
 */

class Router {
    protected $currentController = DEFAULT_CONTROLLER;
    protected $currentMethod = DEFAULT_METHOD;
    protected $params = [];
    
    /**
     * Constructor - Parse URL and set controller, method, and parameters
     */
    public function __construct() {
        $url = $this->getUrl();
        
        // Get controller
        if(isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            // Set controller
            $this->currentController = ucwords($url[0]);
            // Unset 0 index
            unset($url[0]);
        }
        
        // Instantiate controller
        $controllerClass = '\\App\\Controllers\\' . $this->currentController . 'Controller';
        $this->currentController = new $controllerClass;
        
        // Get method
        if(isset($url[1])) {
            if(method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                // Unset 1 index
                unset($url[1]);
            }
        }
        
        // Get parameters
        $this->params = $url ? array_values($url) : [];
        
        // Call the controller method with parameters
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }
    
    /**
     * Parse URL into controller, method, and parameters
     * @return array The URL parts
     */
    public function getUrl() {
        if(isset($_GET['url'])) {
            // Trim trailing slash, sanitize URL, and explode into parts
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        
        return [];
    }
}
