<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class CsrfMiddleware {
    public function handle(Request $request): void {
        if ($request->isPost()) {
            $token   = $request->post('_csrf_token', '');
            $session = Session::get('_csrf_token', '');
            if (!$session || !hash_equals($session, $token)) {
                http_response_code(403);
                die('Invalid CSRF token. Please go back and try again.');
            }
        }
    }
}
