<?php

namespace App\core;


class Router {
    public function dispatch() {
        $url = $_GET['url'] ?? 'home/index';
        $url = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));

        $controller = ucfirst($url[0]) . 'Controller';
        $method = $url[1] ?? 'index';
        $params = array_slice($url, 2);

        App::loadController($controller, $method, $params);

    }
}
