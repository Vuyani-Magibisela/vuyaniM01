<?php
// Detect environment and set base URL accordingly
if ($_SERVER['HTTP_HOST'] === 'localhost' || 
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || 
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    // Local development
    $baseUrl = '/vuyaniM01/portfolio-website/public';
} else {
    // Live server - since we're using the redirect method
    $baseUrl = '/public';
}
?>