<?php
/**
 * SiteScope public entry point
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $base = BASE_PATH . '/app/';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = $base . $relative . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

require BASE_PATH . '/app/Core/helpers.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isInstallRoute = str_starts_with($requestPath, '/install');

if (!file_exists(BASE_PATH . '/config/installed.lock') && !$isInstallRoute) {
    header('Location: /install/');
    exit;
}

if ($isInstallRoute) {
    $installerIndex = BASE_PATH . '/install/index.php';
    if (file_exists($installerIndex)) {
        require $installerIndex;
    } else {
        http_response_code(404);
        echo '404 - Installer not found.';
    }
    exit;
}

$appConfig = require BASE_PATH . '/config/app.php';

$debugEnabled = $appConfig['debug'] ?? false;
$debugLogErrors = (bool) env('APP_DEBUG_LOG', true);
$debugToken = (string) env('APP_DEBUG_TOKEN', '');
$requestDebugToken = (string) ($_GET['debug_token'] ?? $_SERVER['HTTP_X_DEBUG_TOKEN'] ?? '');
$debugMode = $debugEnabled;

if (!$debugMode && $debugToken !== '' && $requestDebugToken !== '' && hash_equals($debugToken, $requestDebugToken)) {
    $debugMode = true;
}

if ($debugMode) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

set_exception_handler(function (Throwable $e) use ($debugMode, $debugLogErrors): void {
    if ($debugLogErrors) {
        app_log('ERROR', $e->getMessage(), [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => ($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'trace' => $e->getTraceAsString(),
        ]);
    }

    http_response_code(500);

    if ($debugMode) {
        $type = htmlspecialchars(get_class($e), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $line = (int) $e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>500 - Application Error</title>
  <style>
    body{font-family:monospace;background:#1e1e2e;color:#cdd6f4;margin:0;padding:2rem}
    h1{color:#f38ba8;font-size:1.4rem;margin-bottom:.5rem}
    .badge{display:inline-block;background:#313244;padding:.2rem .6rem;border-radius:4px;font-size:.85rem;color:#89b4fa}
    .block{background:#181825;border-left:4px solid #f38ba8;padding:1rem 1.2rem;margin:1rem 0;border-radius:0 6px 6px 0;white-space:pre-wrap;overflow-wrap:break-word;overflow:auto}
    .label{font-size:.75rem;color:#6c7086;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem}
  </style>
</head>
<body>
  <h1>Uncaught Exception</h1>
  <span class="badge">{$type}</span>
  <div class="label" style="margin-top:1rem">Message</div>
  <div class="block">{$message}</div>
  <div class="label">Location</div>
  <div class="block">{$file} : {$line}</div>
  <div class="label">Stack Trace</div>
  <div class="block">{$trace}</div>
</body>
</html>
HTML;
    } else {
        $logHint = $debugLogErrors ? '<p>Check <code>storage/logs</code> on the server for the latest error log.</p>' : '';
        echo '<!DOCTYPE html><html><head><title>500 Server Error</title></head>'
           . '<body><h1>500 - Server Error</h1>'
           . '<p>Something went wrong. Please try again later.</p>'
           . $logHint
           . '</body></html>';
    }
    exit;
});

set_error_handler(function (int $severity, string $message, string $file, int $line) use ($debugLogErrors): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    if ($debugLogErrors) {
        app_log('PHP', $message, [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'url' => ($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/'),
        ]);
    }

    throw new \ErrorException($message, 0, $severity, $file, $line);
});

\App\Core\Session::start();

$request = new \App\Core\Request();
$router = require BASE_PATH . '/routes.php';
$router->dispatch($request);
