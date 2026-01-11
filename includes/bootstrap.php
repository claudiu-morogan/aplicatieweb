<?php
/**
 * Bootstrap file - Include this at the top of every PHP file
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Load core includes
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Initialize database connection (early check)
try {
    db();
} catch (Exception $e) {
    if (APP_ENV === 'development') {
        die("Bootstrap error: " . $e->getMessage());
    } else {
        die("Application error. Please contact support.");
    }
}
