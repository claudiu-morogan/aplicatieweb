<?php
/**
 * AplicatieWeb Configuration File
 *
 * IMPORTANT: Copy this file to config.php and update with your settings
 * Never commit config.php to version control!
 */

// Environment (development or production)
define('APP_ENV', 'development'); // Change to 'production' on live server

// Database Configuration
define('DB_HOST', 'localhost');     // Use 'localhost' for shared hosting, 'db' for Docker
define('DB_NAME', 'aplicatieweb');
define('DB_USER', 'root');          // Update for shared hosting
define('DB_PASS', 'rootpassword');  // Update for shared hosting
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'AplicatieWeb');
define('APP_URL', 'http://localhost:8080'); // Update for production: https://aplicatieweb.ro
define('APP_TIMEZONE', 'Europe/Bucharest');

// Paths (auto-detected, usually no need to change)
define('BASE_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('MEDIA_PATH', PUBLIC_PATH . '/media');

// URLs
define('BASE_URL', APP_URL);
define('ADMIN_URL', BASE_URL . '/admin');
define('API_URL', BASE_URL . '/api');
define('MEDIA_URL', BASE_URL . '/media');

// Security
define('SESSION_NAME', 'aweb_session');
define('SESSION_LIFETIME', 86400); // 24 hours in seconds
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_FILE_TYPES', ['application/pdf', 'text/plain']);

// Pagination
define('POSTS_PER_PAGE', 10);
define('ADMIN_POSTS_PER_PAGE', 20);

// Features
define('ANALYTICS_ENABLED', true);
define('COMMENTS_ENABLED', false); // Future feature
define('API_ENABLED', true);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/php_errors.log');
}

// Timezone
date_default_timezone_set(APP_TIMEZONE);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1); // HTTPS only in production
}
