<?php
namespace App\core;

class App {
    public static function loadController($controllerName, $methodName = 'index', $params = []) {
        // Get the base path of the application
        $basePath = dirname(dirname(dirname(__FILE__)));
        
        // Load BaseController first if it hasn't been loaded
        $baseControllerFile = $basePath . "/app/controllers/BaseController.php";
        if (file_exists($baseControllerFile) && !class_exists('App\\Controllers\\BaseController')) {
            require_once $baseControllerFile;
        }
        
        // Load the specific controller
        $controllerFile = $basePath . "/app/controllers/" . $controllerName . ".php";
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = 'App\\controllers\\' . $controllerName;
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $params);
                } else {
                    echo "Method '$methodName' not found in controller '$controllerName'.";
                }
            } else {
                echo "Controller class '$controllerClass' not found.";
            }
        } else {
            echo "Controller file not found: $controllerFile";
        }
    }
}