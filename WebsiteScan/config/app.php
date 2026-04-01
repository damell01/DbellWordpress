<?php
// config/app.php - Application configuration loader

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!str_contains($line, '=')) continue;
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!function_exists('detectAppUrl')) {
    function detectAppUrl(): string {
        $configuredUrl = env('APP_URL');
        if (is_string($configuredUrl) && trim($configuredUrl) !== '') {
            return rtrim($configuredUrl, '/');
        }

        $isHttps = false;

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
            $isHttps = $forwardedProto === 'https';
        } elseif (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            $isHttps = true;
        } elseif (($_SERVER['SERVER_PORT'] ?? null) === '443') {
            $isHttps = true;
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $host = trim(explode(',', (string) $host)[0]);

        return ($isHttps ? 'https' : 'http') . '://' . $host;
    }
}

$envFile = dirname(__DIR__) . '/.env';
loadEnv($envFile);
$debugOverrideFile = __DIR__ . '/debug.local.php';
$debugOverrides = file_exists($debugOverrideFile) ? (require $debugOverrideFile) : [];

return [
    'name'        => env('APP_NAME', 'DBell Website Scanner'),
    'url'         => detectAppUrl(),
    'env'         => env('APP_ENV', 'production'),
    'debug'       => array_key_exists('debug', $debugOverrides) ? (bool) $debugOverrides['debug'] : (bool) env('APP_DEBUG', false),
    'key'         => env('APP_KEY', 'default-insecure-key-change-me'),
    'admin_email' => env('ADMIN_EMAIL', ''),
    'rate_limit'  => [
        'audits' => (int) env('RATE_LIMIT_AUDITS', 5),
        'window' => (int) env('RATE_LIMIT_WINDOW', 3600),
    ],
    'session_lifetime' => (int) env('SESSION_LIFETIME', 7200),
];
