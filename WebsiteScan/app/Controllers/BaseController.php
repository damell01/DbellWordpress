<?php
namespace App\Controllers;

use App\Core\{Request, View, Session};
use App\Models\{Setting};

abstract class BaseController {
    protected Setting $settings;

    public function __construct() {
        $this->settings = new Setting();
        View::share('appName', $this->settings->get('site_name', config('app.name', 'VerityScan')));
        View::share('settings', $this->settings);
    }

    protected function view(string $view, array $data = []): void {
        $this->sendNoCacheHeaders();
        View::renderWithLayout('main', "pages/{$view}", $data);
    }

    protected function adminView(string $view, array $data = []): void {
        $this->sendNoCacheHeaders();
        View::renderWithLayout('admin', "admin/{$view}", $data);
    }

    protected function redirect(string $path): void {
        redirect(url($path));
    }

    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        echo json_encode($data);
        exit;
    }

    protected function sendNoCacheHeaders(): void {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
}
