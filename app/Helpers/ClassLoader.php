<?php
class ClassLoader {
    public static function register() {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $base = str_replace($prefix, '', $class) . '.php';
            $path = 'app/Controllers/' . $base;
            if (file_exists($path)) {
                require_once $path;
            }
            $path = 'app/Models/' . $base;
            if (file_exists($path)) {
                require_once $path;
            }
        });
    }
}
?>
