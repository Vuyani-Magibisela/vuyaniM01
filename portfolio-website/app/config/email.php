<?php
/**
 * Email Configuration
 *
 * Values come from the environment (Docker Compose) or an untracked .env
 * file. Locally this points at Mailpit, so no mail ever reaches a real
 * inbox during development.
 *
 * See .env.example for the full list of supported keys.
 */

require_once dirname(__DIR__) . '/core/Env.php';

use App\Core\Env;

return [
    'smtp_host'       => Env::get('MAIL_HOST', 'mailpit'),
    'smtp_port'       => Env::int('MAIL_PORT', 1025),
    'smtp_username'   => Env::get('MAIL_USERNAME', ''),
    'smtp_password'   => Env::get('MAIL_PASSWORD', ''),
    // '' | 'tls' | 'ssl' — Mailpit needs no encryption.
    'smtp_encryption' => Env::get('MAIL_ENCRYPTION', ''),
    'smtp_auth'       => Env::bool('MAIL_AUTH', false),

    'from_email'      => Env::get('MAIL_FROM_EMAIL', 'admin@vuyanimagibisela.co.za'),
    'from_name'       => Env::get('MAIL_FROM_NAME', 'Vuyani Magibisela'),

    // Where contact-form notifications are delivered.
    'admin_email'     => Env::get('MAIL_ADMIN_EMAIL', 'admin@vuyanimagibisela.co.za'),
    'admin_name'      => Env::get('MAIL_ADMIN_NAME', 'Vuyani Magibisela'),
];
