<?php
/**
 * Database Configuration - Kanak Beauty Parlour
 * PDO connection with environment variable support
 */

// Define application root constant
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Load .env file if it exists
$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            // Strip surrounding quotes if present
            $value = trim($value, '"\'');
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Database credentials — fall back to XAMPP defaults
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'kanak_beauty_parlour';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // In production, log the error rather than displaying it
    error_log('Database connection failed: ' . $e->getMessage());
    if (getenv('APP_ENV') === 'development') {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c00;">
                <h2>Database Connection Error</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Please check your <code>.env</code> file or XAMPP MySQL service.</p>
             </div>');
    } else {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c00;">
                <h2>Service Unavailable</h2>
                <p>We are experiencing technical difficulties. Please try again later.</p>
             </div>');
    }
}

return $pdo;
