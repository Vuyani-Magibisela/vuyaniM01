<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Core\Session;

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

        // Load reactions and approved comments
        $reactionModel = $this->model('Reaction');
        $commentModel  = $this->model('Comment');

        $data = [
            'post'           => $post,
            'relatedPosts'   => $relatedPosts,
            'reactionCounts' => $reactionModel->getCounts($post['id']),
            'comments'       => $commentModel->getApproved($post['id']),
            'isPreview'      => false,
        ];

        $this->view('blog/article', $data);
    }

    /**
     * Handle emoji reaction toggle (AJAX POST)
     * POST /blog/react
     */
    public function react() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        // CSRF check
        $token = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $emoji  = trim($_POST['emoji'] ?? '');

        $reactionModel = $this->model('Reaction');

        if ($postId <= 0 || !$reactionModel->isAllowed($emoji)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }

        // Session-based rate limit: max 30 reaction toggles per minute
        $rateBucket = 'reaction_count_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
        $rateWindow = 'reaction_window_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
        $now = time();
        if (Session::get($rateWindow, 0) < $now - 60) {
            Session::set($rateWindow, $now);
            Session::set($rateBucket, 0);
        }
        $count = (int)Session::get($rateBucket, 0);
        if ($count >= 30) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many reactions. Please slow down.']);
            exit;
        }
        Session::set($rateBucket, $count + 1);

        $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $result = $reactionModel->toggle($postId, $emoji, $ipHash);

        echo json_encode(['success' => true, 'action' => $result['action'], 'emoji' => $emoji, 'count' => $result['count']]);
        exit;
    }

    /**
     * Handle comment submission (AJAX POST)
     * POST /blog/comment
     */
    public function comment() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        // CSRF check
        $token = $_POST['csrf_token'] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            exit;
        }

        // Honeypot — bots fill this, humans don't
        if (!empty($_POST['website'])) {
            // Silently succeed to fool bots
            echo json_encode(['success' => true, 'message' => 'Comment submitted for review.']);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $commentModel = $this->model('Comment');

        // Rate limit: max 5 comments per 5 minutes per IP
        if ($commentModel->countByIp($ip, 300) >= 5) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many comments. Please wait a few minutes before commenting again.']);
            exit;
        }

        $postId  = (int)($_POST['post_id'] ?? 0);
        $name    = strip_tags(trim($_POST['author_name'] ?? ''));
        $email   = trim($_POST['author_email'] ?? '');
        $content = strip_tags(trim($_POST['content'] ?? ''));

        // Validate
        $errors = [];
        if ($postId <= 0)                            $errors[] = 'Invalid post.';
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Name must be 2–100 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors[] = 'Please provide a valid email address.';
        if (mb_strlen($content) < 10 || mb_strlen($content) > 2000) $errors[] = 'Comment must be 10–2000 characters.';

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
            exit;
        }

        $id = $commentModel->createComment([
            'post_id'      => $postId,
            'author_name'  => $name,
            'author_email' => $email,
            'content'      => $content,
            'ip_address'   => $ip,
            'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Thank you! Your comment has been submitted for review and will appear once approved.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not save your comment. Please try again.']);
        }
        exit;
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
            'post'           => $postArr,
            'relatedPosts'   => $relatedPosts,
            'reactionCounts' => [],
            'comments'       => [],
            'isPreview'      => true,
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