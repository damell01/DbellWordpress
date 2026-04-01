<?php
namespace App\Core;

class Request {
    private array $get;
    private array $post;
    public array $server;
    private array $files;

    public function __construct() {
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->server = $_SERVER;
        $this->files  = $_FILES;
    }

    public function method(): string {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool { return $this->method() === 'POST'; }
    public function isGet():  bool { return $this->method() === 'GET'; }

    public function get(string $key, mixed $default = null): mixed {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array {
        return array_merge($this->get, $this->post);
    }

    public function has(string $key): bool {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function only(array $keys): array {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) $result[$key] = $this->input($key);
        }
        return $result;
    }

    public function ip(): string {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $header) {
            if (!empty($this->server[$header])) {
                $ip = explode(',', $this->server[$header])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    public function userAgent(): string {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function path(): string {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }

    public function fullUrl(): string {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri    = $this->server['REQUEST_URI'] ?? '/';
        return "{$scheme}://{$host}{$uri}";
    }

    public function file(string $key): ?array {
        return $this->files[$key] ?? null;
    }

    public function validate(array $rules): array {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            $value    = $this->input($field, '');
            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                } elseif ($r === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email.';
                } elseif (str_starts_with($r, 'max:') && strlen($value) > (int)substr($r, 4)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is too long.';
                } elseif (str_starts_with($r, 'min:') && strlen($value) < (int)substr($r, 4)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is too short.';
                } elseif ($r === 'url' && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid URL.';
                }
            }
        }
        return $errors;
    }
}
