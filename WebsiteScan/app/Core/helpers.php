<?php
// app/Core/helpers.php - Global helper functions

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val === false) return $default;
        return match (strtolower($val)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            default            => $val,
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        static $configs = [];
        $parts = explode('.', $key);
        $file  = $parts[0];
        if (!isset($configs[$file])) {
            $path = dirname(__DIR__, 2) . "/config/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }
        $value = $configs[$file];
        foreach (array_slice($parts, 1) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = rtrim(config('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $normalizedPath = ltrim($path, '/');
        $assetUrl = url('assets/' . $normalizedPath);
        $assetFile = public_path('assets' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPath));

        if (is_file($assetFile)) {
            return $assetUrl . '?v=' . filemtime($assetFile);
        }

        return $assetUrl;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\Session::get('_csrf_token') ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string {
        return e(\App\Core\Session::getFlash('_old')[$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, string $message): void {
        \App\Core\Session::setFlash($key, $message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $key): ?string {
        return \App\Core\Session::getFlash($key);
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void {
        http_response_code($code);
        $messages = [404 => 'Not Found', 403 => 'Forbidden', 500 => 'Server Error'];
        $title = $messages[$code] ?? 'Error';
        echo "<!DOCTYPE html><html><head><title>{$code} {$title}</title></head><body><h1>{$code} {$title}</h1><p>" . e($message) . "</p></body></html>";
        exit;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return round($diff / 60) . 'm ago';
        if ($diff < 86400) return round($diff / 3600) . 'h ago';
        return round($diff / 86400) . 'd ago';
    }
}

if (!function_exists('app_log')) {
    function app_log(string $level, string $message, array $context = []): void {
        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextJson = $context ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$timestamp}] {$level}: {$message}";
        if ($contextJson) {
            $line .= ' ' . $contextJson;
        }
        $line .= PHP_EOL;

        @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log', $line, FILE_APPEND);
    }
}
