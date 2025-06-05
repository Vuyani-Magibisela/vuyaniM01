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
        
        // Check if model class exists, if not try to load it
        if (!class_exists($modelClass)) {
            $basePath = dirname(dirname(dirname(__FILE__)));
            
            // Load BaseModel first if not loaded
            $baseModelFile = $basePath . '/app/models/BaseModel.php';
            if (file_exists($baseModelFile) && !class_exists('App\\Models\\BaseModel')) {
                require_once $baseModelFile;
            }
            
            // Load the specific model
            $modelFile = $basePath . '/app/models/' . $model . '.php';
            if (file_exists($modelFile)) {
                require_once $modelFile;
            }
        }
        
        // Check if model class exists now
        if (class_exists($modelClass)) {
            return new $modelClass();
        } else {
            // Model does not exist
            die('Model does not exist: ' . $model . ' (Class: ' . $modelClass . ')');
        }
    }

    /**
     * Load view
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view(string $view, array $data = []) {
        // Get the application base path - works for both local and live server
        $basePath = dirname(dirname(dirname(__FILE__)));
        
        // Construct the full path to the view file
        $viewPath = $basePath . '/app/views/' . $view . '.php';

        // Check if view file exists
        if (file_exists($viewPath)) {
            // Extract data array to variables
            extract($data);

            // Require view file
            require_once $viewPath;
        } else {
            // View does not exist
            // For debugging - show the path that was attempted
            die('View does not exist: ' . $viewPath . '<br>Base path: ' . $basePath);
        }
    }
}