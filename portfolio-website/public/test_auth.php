<?php
// Simple test to check if authentication is working
session_start();

echo "<h2>Session Debug Information</h2>\n";
echo "<pre>\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n";
echo "\nSession Data:\n";
print_r($_SESSION);

echo "\n\nCookies:\n";
print_r($_COOKIE);

echo "\n\nIs Authenticated: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
echo "\nTo test upload, you need to be logged in first.\n";
echo "</pre>\n";
