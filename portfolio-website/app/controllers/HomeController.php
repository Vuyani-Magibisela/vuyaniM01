<?php
namespace App\controllers;

use App\Controllers\BaseController;

class HomeController extends BaseController {
    public function index() {
        // Load recent blog posts for homepage
        $blogModel = $this->model('BlogPost');
        $latestPosts = $blogModel->getRecentPosts(3);

        $data = [
            'latestPosts' => $latestPosts
        ];

        $this->view('home/index', $data);
    }
}
