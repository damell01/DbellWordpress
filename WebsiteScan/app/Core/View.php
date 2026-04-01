<?php
namespace App\Core;

class View {
    private static array $shared = [];

    public static function share(string $key, mixed $value): void {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = []): void {
        $data = array_merge(self::$shared, $data);
        extract($data, EXTR_SKIP);
        $viewPath = base_path("app/Views/{$view}.php");
        if (!file_exists($viewPath)) {
            abort(500, "View not found: {$view}");
        }
        require $viewPath;
    }

    public static function renderWithLayout(string $layout, string $view, array $data = []): void {
        $data = array_merge(self::$shared, $data);
        extract($data, EXTR_SKIP);
        // Capture view content
        ob_start();
        $viewPath = base_path("app/Views/{$view}.php");
        if (!file_exists($viewPath)) {
            abort(500, "View not found: {$view}");
        }
        require $viewPath;
        $content = ob_get_clean();

        // Render layout with content
        $layoutPath = base_path("app/Views/layouts/{$layout}.php");
        if (!file_exists($layoutPath)) {
            abort(500, "Layout not found: {$layout}");
        }
        require $layoutPath;
    }

    public static function partial(string $partial, array $data = []): void {
        extract(array_merge(self::$shared, $data), EXTR_SKIP);
        $path = base_path("app/Views/partials/{$partial}.php");
        if (file_exists($path)) require $path;
    }
}
