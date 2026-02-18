<?php
/**
 * Router PHP para el servidor built-in de PHP
 * Maneja URLs limpias sin .php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Remove leading slash
$uri = ltrim($uri, '/');

// If empty, serve index.php
if ($uri === '') {
    return false; // Let PHP serve index.php
}

// If file exists, serve it directly
if (file_exists(__DIR__ . '/' . $uri)) {
    return false;
}

// If directory exists, serve it
if (is_dir(__DIR__ . '/' . $uri)) {
    return false;
}

// Try to add .php extension
$phpFile = __DIR__ . '/' . $uri . '.php';
if (file_exists($phpFile)) {
    include $phpFile;
    return true;
}

// Try to serve as index file in directory
$indexFile = __DIR__ . '/' . $uri . '/index.php';
if (file_exists($indexFile)) {
    include $indexFile;
    return true;
}

// 404 - File not found
http_response_code(404);
if (file_exists(__DIR__ . '/404.php')) {
    include __DIR__ . '/404.php';
} else {
    echo "404 - Page not found";
}
return true;
