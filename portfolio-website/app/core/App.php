<?php
namespace App\core;

class App {
    public static function loadController($controllerName, $methodName = 'index', $params = []) {
        $controllerFile = "../app/controllers/" . $controllerName . ".php";
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = 'App\\controllers\\' . $controllerName;
            $controller = new $controllerClass();
            if (method_exists($controller, $methodName)) {
                call_user_func_array([$controller, $methodName], $params);
            }
        } else {
            echo "Controller not found.";
        }
    }
}
        