<?php
/**
 * Email Configuration
 *
 * Environment-specific SMTP configuration for email sending
 * Detects local development vs production environment
 */

// Detect environment (same pattern as database.php)
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
if ($httpHost === 'localhost' ||
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($httpHost, 'localhost') !== false) {

    // Local development configuration
    // Using production SMTP for local testing
    return [
        'smtp_host' => 'mail.vuyanimagibisela.co.za',
        'smtp_port' => 465, // SSL port
        'smtp_username' => 'admin@vuyanimagibisela.co.za',
        'smtp_password' => '@*7.dQ.I=NkO7X]v',
        'smtp_encryption' => 'ssl',
        'from_email' => 'admin@vuyanimagibisela.co.za',
        'from_name' => 'Vuyani Magibisela',
        'admin_email' => 'admin@vuyanimagibisela.co.za',
        'admin_name' => 'Vuyani Magibisela'
    ];

} else {

    // Production server configuration
    // Using admin@vuyanimagibisela.co.za domain email
    return [
        'smtp_host' => 'mail.vuyanimagibisela.co.za',
        'smtp_port' => 465, // SSL port
        'smtp_username' => 'admin@vuyanimagibisela.co.za',
        'smtp_password' => '@*7.dQ.I=NkO7X]v',
        'smtp_encryption' => 'ssl',
        'from_email' => 'admin@vuyanimagibisela.co.za',
        'from_name' => 'Vuyani Magibisela',
        'admin_email' => 'admin@vuyanimagibisela.co.za', // Where contact notifications are sent
        'admin_name' => 'Vuyani Magibisela'
    ];
}
