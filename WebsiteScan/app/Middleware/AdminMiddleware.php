<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AdminMiddleware {
    public function handle(Request $request): void {
        if (!Session::isAdmin()) {
            flash('error', 'Admin access required.');
            redirect(url('admin/login'));
        }
    }
}
