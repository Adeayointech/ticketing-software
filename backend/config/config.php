<?php
// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Application Configuration
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400); // 24 hours

// CORS Configuration
define('ALLOWED_ORIGINS', $_ENV['ALLOWED_ORIGINS'] ?? '*');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);

// QR Code Configuration
// Update to point to web-accessible uploads/qrcodes directory
define('QR_CODE_DIR', realpath(__DIR__ . '/../../../../xampp/htdocs/ticketing-backend/uploads/qrcodes/') . '/');
define('QR_CODE_SIZE', 10);
define('QR_CODE_MARGIN', 2);

// Timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

// Error Reporting
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Create necessary directories
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
// Ensure QR_CODE_DIR exists (web root uploads/qrcodes)
if (!file_exists(QR_CODE_DIR)) {
    mkdir(QR_CODE_DIR, 0755, true);
}
