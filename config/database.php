<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking');
define('DB_PORT', 3306);

// Application Configuration
define('SITE_NAME', 'Hotel Booking System');
define('SITE_URL', 'http://localhost/hotel/');

// Session Configuration
ini_set('session.gc_maxlifetime', 3600);
session_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>
