<?php
namespace App\Controllers;

use App\Core\{Request, Session, Database};
use App\Models\{User};

class AuthController extends BaseController {
    public function loginForm(Request $request): void {
        if (Session::isAdmin()) { $this->redirect('admin'); return; }
        $this->view('auth/login', ['title' => 'Admin Login']);
    }

    public function login(Request $request): void {
        $errors = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if ($errors) {
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('admin/login');
            return;
        }

        // Rate limiting for login
        $ip    = $request->ip();
        $db    = Database::getInstance();
        $key   = 'login_attempts_' . md5($ip);
        $count = (int)($db->scalar("SELECT COUNT(*) FROM admin_activity_logs WHERE action = 'login_fail' AND ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)", [$ip]) ?? 0);
        if ($count >= 5) {
            Session::setFlash('error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->redirect('admin/login');
            return;
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($request->post('email'));

        if (!$user || !$userModel->verifyPassword($request->post('password'), $user['password_hash'])) {
            // Log failed attempt
            $db->insert('admin_activity_logs', [
                'user_id'    => null,
                'action'     => 'login_fail',
                'details'    => 'Email: ' . $request->post('email'),
                'ip_address' => $ip,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('admin/login');
            return;
        }

        if ($user['role'] !== 'admin') {
            Session::setFlash('error', 'You do not have admin access.');
            $this->redirect('admin/login');
            return;
        }

        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);

        $db->insert('admin_activity_logs', [
            'user_id'    => $user['id'],
            'action'     => 'login',
            'details'    => 'Successful login',
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect('admin');
    }

    public function logout(Request $request): void {
        Session::flush();
        $this->redirect('admin/login');
    }
}
