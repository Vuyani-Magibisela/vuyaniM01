<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class BlogController extends BaseController {

    public function index() {
        // Load blog posts for the main blog page
        $blogModel = $this->model('BlogPost');
        $posts = $blogModel->getRecentPosts(10); // Get 10 most recent posts
        $featuredPosts = $blogModel->getFeaturedPosts(3); // Get 3 featured posts
        
        $data = [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts
        ];
        
        $this->view('blog/index', $data);
    }
    
    public function article($slug = '') {
        if (empty($slug)) {
            // Redirect to blog index if no slug provided
            header('Location: ' . '/blog');
            exit;
        }
        
        // Load the specific article
        $blogModel = $this->model('BlogPost');
        $post = $blogModel->getPostBySlug($slug);
        
        if (!$post) {
            // Article not found, could redirect to 404 page
            header('Location: ' . '/blog');
            exit;
        }
        
        // Increment view count
        $blogModel->incrementViews($post['id']);
        
        // Get related posts
        $relatedPosts = $blogModel->getRelatedPosts($post['id'], $post['category_id'], 3);
        
        $data = [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ];
        
        $this->view('blog/article', $data);
    }
    
    public function resources() {
        // Load resources/downloadable content
        $resourceModel = $this->model('Resource');
        $resources = $resourceModel->getAllPublished();
        
        $data = [
            'resources' => $resources
        ];
        
        $this->view('blog/resources', $data);
    }
    
    public function downloadResource($resourceId) {
        // Check if user is logged in for protected resources
        $isLoggedIn = isset($_SESSION['user_id']);
        
        $resourceModel = $this->model('Resource');
        $resource = $resourceModel->getById($resourceId);
        
        if (!$resource) {
            // Resource not found
            header('Location: ' . '/blog/resources');
            exit;
        }
        
        // Check if resource requires login
        if ($resource['requires_login'] && !$isLoggedIn) {
            // Redirect to login page
            $_SESSION['redirect_after_login'] = "/blog/download-resource/{$resourceId}";
            header('Location: ' . '/auth/login');
            exit;
        }
        
        // Increment download count
        $resourceModel->incrementDownloads($resourceId);
        
        // Set appropriate headers and send the file
        $filePath = $resource['file_path'];
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            // For dummy data - just redirect back with a success message
            header('Location: ' . '/blog/resources?download=success');
            exit;
        }
    }
    
    public function category($slug = '') {
        if (empty($slug)) {
            // Redirect to blog index if no slug provided
            header('Location: ' . '/blog');
            exit;
        }
        
        $blogModel = $this->model('BlogPost');
        $posts = $blogModel->getPostsByCategory($slug, 10);
        
        $data = [
            'posts' => $posts,
            'category' => $slug
        ];
        
        $this->view('blog/index', $data);
    }
}