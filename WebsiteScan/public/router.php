<?php
// Built-in server router - serves static files directly, routes dynamic requests to index.php
$file = __DIR__ . $_SERVER['REQUEST_URI'];
$file = parse_url($file, PHP_URL_PATH);
if (is_file($file)) {
    // Serve static file
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimes = [
        'css' => 'text/css', 'js' => 'application/javascript',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'gif' => 'image/gif',
        'ico' => 'image/x-icon', 'svg' => 'image/svg+xml', 'woff' => 'font/woff',
        'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject',
        'map' => 'application/json',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($file);
    return true;
}
// Dynamic request - go through app
require __DIR__ . '/index.php';
