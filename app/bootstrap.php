<?php
declare(strict_types=1);

// Base paths
define('BASE_PATH', __DIR__ . '/..');
define('DATA_PATH', BASE_PATH . '/data');

// Ensure sessions are available for controllers/services
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple PSR-4–style autoloader for the App\ namespace
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

