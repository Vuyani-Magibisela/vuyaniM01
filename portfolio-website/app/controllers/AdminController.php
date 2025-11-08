<?php

namespace App\Controllers;

use App\Core\Session;

class AdminController extends BaseController {

    public function __construct() {
        // Require authentication for all admin pages
        AuthController::requireAuth();
    }

    /**
     * Admin dashboard home
     */
    public function index() {
        $data = [
            'title' => 'Admin Dashboard - Vuyani Magibisela',
            'username' => Session::get('username'),
            'email' => Session::get('email'),
            'role' => Session::get('user_role'),
            'success' => Session::getFlash('success')
        ];

        $this->view('admin/dashboard', $data);
    }

    /**
     * Manage users (admin only)
     */
    public function users() {
        // Only admins can access user management
        AuthController::requireAdmin();

        $userModel = $this->model('User');
        $users = $userModel->getAllAdmins();

        $data = [
            'title' => 'Manage Users - Admin Dashboard',
            'users' => $users
        ];

        $this->view('admin/users', $data);
    }

    /**
     * Manage blog posts
     */
    public function blog() {
        $data = [
            'title' => 'Manage Blog - Admin Dashboard'
        ];

        $this->view('admin/blog', $data);
    }

    /**
     * Manage projects
     */
    public function projects() {
        $data = [
            'title' => 'Manage Projects - Admin Dashboard'
        ];

        $this->view('admin/projects', $data);
    }

    /**
     * View contact submissions
     */
    public function contacts() {
        $contactModel = $this->model('Contact');
        $submissions = $contactModel->getAllSubmissions();

        $data = [
            'title' => 'Contact Submissions - Admin Dashboard',
            'submissions' => $submissions
        ];

        $this->view('admin/contacts', $data);
    }
}
