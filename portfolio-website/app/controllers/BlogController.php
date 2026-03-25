<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class BlogController extends BaseController {

    public function index() {
        // Load blog posts for the main blog page
        $blogModel = $this->model('BlogPost');
        $featuredPosts = $blogModel->getFeaturedPosts(3); // Get 3 featured posts

        // Collect featured post IDs to exclude from latest
        $excludeIds = array_map(function($post) {
            return $post['id'];
        }, $featuredPosts);

        $posts = $blogModel->getRecentPosts(10, $excludeIds); // Exclude featured

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

        // Parse markdown content if needed
        $post['content'] = $this->parseContent($post['content']);

        // Get related posts
        $relatedPosts = $blogModel->getRelatedPosts($post['id'], $post['category_id'], 3);

        $data = [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'isPreview' => false
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
        $resource = $resourceModel->findById($resourceId);
        
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
    
    public function preview($id = '') {
        if (empty($id) || !isset($_SESSION['user_id'])) {
            header('Location: /blog');
            exit;
        }

        $blogModel = $this->model('BlogPost');
        $post = $blogModel->getPostById($id);

        if (!$post) {
            header('Location: /admin/blog');
            exit;
        }

        // Convert object to array for template compatibility
        $postArr = (array)$post;

        // Parse markdown content if needed
        $postArr['content'] = $this->parseContent($postArr['content']);

        // Ensure required fields exist for the template
        if (empty($postArr['published_at'])) {
            $postArr['published_at'] = $postArr['created_at'] ?? date('Y-m-d H:i:s');
        }
        if (empty($postArr['author_name'])) {
            $postArr['author_name'] = 'Admin';
        }
        if (empty($postArr['views'])) {
            $postArr['views'] = 0;
        }
        if (empty($postArr['category_slug'])) {
            $postArr['category_slug'] = 'uncategorized';
        }
        if (empty($postArr['category_name'])) {
            $postArr['category_name'] = $postArr['category_name'] ?? 'Uncategorized';
        }

        $relatedPosts = [];

        $data = [
            'post' => $postArr,
            'relatedPosts' => $relatedPosts,
            'isPreview' => true
        ];

        $this->view('blog/article', $data);
    }

    /**
     * Detect if content is markdown (not HTML) and parse accordingly
     */
    private function parseContent($content) {
        $trimmed = trim($content);

        // If empty, return as-is
        if (empty($trimmed)) {
            return $content;
        }

        // Heuristic: if content starts with an HTML tag, treat as HTML
        if (preg_match('/^<[a-z!]/i', $trimmed)) {
            return $content;
        }

        // Content looks like markdown — parse it
        $basePath = dirname(dirname(dirname(__FILE__)));
        $parsedownPath = $basePath . '/app/libraries/Parsedown.php';

        if (file_exists($parsedownPath)) {
            require_once $parsedownPath;
            $parsedown = new \Parsedown();
            $parsedown->setSafeMode(true);
            return $parsedown->text($trimmed);
        }

        // Fallback: return raw content wrapped in <p> tags
        return '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
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