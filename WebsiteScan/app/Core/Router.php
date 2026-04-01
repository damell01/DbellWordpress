<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, string|callable $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string|callable $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middleware): void {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => '#^' . $pattern . '$#',
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void {
        $method = $request->method();
        $path   = $request->path();
        // Strip script prefix for subdirectory installs
        $script = dirname($request->server['SCRIPT_NAME'] ?? '/index.php');
        $appBase = $script;
        if ($appBase !== '/' && str_ends_with($appBase, '/public')) {
            $appBase = rtrim(dirname($appBase), '/');
            if ($appBase === '') {
                $appBase = '/';
            }
        }
        if ($script !== '/' && str_starts_with($path, $script)) {
            $path = substr($path, strlen($script)) ?: '/';
        } elseif ($appBase !== '/' && str_starts_with($path, $appBase)) {
            $path = substr($path, strlen($appBase)) ?: '/';
        }
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $path, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                $middlewareClass = "App\\Middleware\\{$mw}Middleware";
                if (class_exists($middlewareClass)) {
                    (new $middlewareClass())->handle($request);
                }
            }

            $handler = $route['handler'];
            if (is_callable($handler)) {
                $handler($request, $params);
                return;
            }

            [$class, $method_name] = explode('@', $handler);
            $fullClass = "App\\Controllers\\{$class}";
            if (!class_exists($fullClass)) {
                abort(500, "Controller {$fullClass} not found.");
            }
            (new $fullClass())->$method_name($request, $params);
            return;
        }

        abort(404, 'Page not found.');
    }

    public function __get(string $name): mixed {
        return null;
    }
}
