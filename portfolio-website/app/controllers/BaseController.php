<?php

namespace App\Controllers;

/**
 * Base Controller
 * Loads the models and views
 */
abstract class BaseController {

    /**
     * Load model
     * @param string $model Model name
     * @return object Model object
     */
    protected function model(string $model) {
        $modelClass = 'App\\Models\\' . $model;
        // Check if model class exists
        if (class_exists($modelClass)) {
            return new $modelClass();
        } else {
            // Model does not exist
            die('Model does not exist: ' . $model);
        }
    }

    /**
     * Load view
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view(string $view, array $data = []) {
        // Construct the full path to the view file
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';

        // Check if view file exists
        if (file_exists($viewPath)) {
            // Extract data array to variables
            extract($data);

            // Require view file
            require_once $viewPath;
        } else {
            // View does not exist
            // Consider a more robust error handling mechanism (e.g., logging, showing a 404 page)
            die('View does not exist: ' . $viewPath);
        }
    }
}
