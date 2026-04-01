<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AuthMiddleware {
    public function handle(Request $request): void {
        if (!Session::isAuthenticated()) {
            flash('error', 'Please log in to continue.');
            redirect(url('login'));
        }
    }
}
