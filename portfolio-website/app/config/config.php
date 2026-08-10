<?php
/**
 * Application Configuration
 *
 * Defines $baseUrl — the URL prefix every internal link is built from.
 *
 *   Docker (DocumentRoot = public/)      APP_BASE_URL=""
 *   cPanel (root .htaccess -> public/)   APP_BASE_URL="/public"
 *   XAMPP  (htdocs subfolder)            APP_BASE_URL="/vuyaniM01/portfolio-website/public"
 *
 * Note: callers use `require_once`, so $baseUrl is only bound in the first
 * scope that requires this file. The BASE_URL constant below is always
 * available and is the safer thing to reference in new code.
 */

require_once dirname(__DIR__) . '/core/Env.php';

use App\Core\Env;

$baseUrl = Env::get('APP_BASE_URL', '');

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

if (!defined('APP_ENV')) {
    define('APP_ENV', Env::get('APP_ENV', 'production'));
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', Env::bool('APP_DEBUG', false));
}
