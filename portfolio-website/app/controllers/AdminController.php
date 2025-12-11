<?php

namespace App\Controllers;

use App\Core\Session;

// Explicitly require AuthController since it's not auto-loaded
require_once dirname(__DIR__) . '/controllers/AuthController.php';

use App\Controllers\AuthController;

class AdminController extends BaseController {

    public function __construct() {
        // Require authentication for all admin pages
        AuthController::requireAuth();
    }

    /**
     * Override view method to automatically include baseUrl
     */
    protected function view(string $view, array $data = []) {
        // Detect environment and set base URL
        if ($_SERVER['HTTP_HOST'] === 'localhost' ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            $baseUrl = '/vuyaniM01/portfolio-website/public';
        } else {
            $baseUrl = '/public';
        }

        $data['baseUrl'] = $baseUrl;
        parent::view($view, $data);
    }

    /**
     * Admin dashboard home
     */
    public function index() {
        // Gather statistics
        $blogPostModel = $this->model('BlogPost');
        $projectModel = $this->model('Project');
        $resourceModel = $this->model('Resource');
        $contactModel = $this->model('Contact');
        $userModel = $this->model('User');

        // Get counts
        $stats = [
            'total_posts' => $blogPostModel->getPostCount(),
            'draft_posts' => $blogPostModel->getPostCount('draft'),
            'published_posts' => $blogPostModel->getPostCount('published'),
            'total_projects' => $projectModel->getProjectCount(),
            'draft_projects' => $projectModel->getProjectCount('draft'),
            'published_projects' => $projectModel->getProjectCount('published'),
            'total_resources' => $resourceModel->getResourceCount(),
            'draft_resources' => $resourceModel->getResourceCount('draft'),
            'published_resources' => $resourceModel->getResourceCount('published'),
            'total_contacts' => count($contactModel->getAllSubmissions()),
            'unread_contacts' => $contactModel->getUnreadCount(),
            'total_users' => count($userModel->getAllAdmins())
        ];

        // Get recent content
        $recentPosts = $blogPostModel->getAllPosts(null, null, 5, 0);
        $recentProjects = $projectModel->getAllProjects(null, null, null, 5, 0);
        $recentContacts = array_slice($contactModel->getAllSubmissions(), 0, 5);

        $data = [
            'title' => 'Admin Dashboard - Vuyani Magibisela',
            'username' => Session::get('username'),
            'email' => Session::get('email'),
            'role' => Session::get('user_role'),
            'success' => Session::getFlash('success'),
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'recentProjects' => $recentProjects,
            'recentContacts' => $recentContacts
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
            'users' => $users,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/users', $data);
    }

    /**
     * Create new user (AJAX)
     */
    public function createUser() {
        header('Content-Type: application/json');

        AuthController::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit;
        }

        if (strlen($data['password']) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
            exit;
        }

        $userModel = $this->model('User');

        // Check if username or email already exists
        if ($userModel->findByUsername($data['username'])) {
            echo json_encode(['success' => false, 'error' => 'Username already exists']);
            exit;
        }

        if ($userModel->findByEmail($data['email'])) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit;
        }

        // Create user
        $userId = $userModel->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'editor'
        ]);

        if ($userId) {
            echo json_encode(['success' => true, 'userId' => $userId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create user']);
        }
        exit;
    }

    /**
     * Update user (AJAX)
     */
    public function updateUser($id) {
        header('Content-Type: application/json');

        AuthController::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate
        if (empty($data['username']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'error' => 'Username and email are required']);
            exit;
        }

        $userModel = $this->model('User');

        // Check if username exists for different user
        $existingUser = $userModel->findByUsername($data['username']);
        if ($existingUser && $existingUser['id'] != $id) {
            echo json_encode(['success' => false, 'error' => 'Username already exists']);
            exit;
        }

        // Check if email exists for different user
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit;
        }

        // Update user
        $success = $userModel->updateUser($id, [
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'editor'
        ]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user']);
        }
        exit;
    }

    /**
     * Reset user password (AJAX)
     */
    public function resetUserPassword($id) {
        header('Content-Type: application/json');

        AuthController::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['password'])) {
            echo json_encode(['success' => false, 'error' => 'Password is required']);
            exit;
        }

        if (strlen($data['password']) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
            exit;
        }

        $userModel = $this->model('User');

        // Update password
        $success = $userModel->updatePassword($id, $data['password']);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to reset password']);
        }
        exit;
    }

    /**
     * Delete user (AJAX)
     */
    public function deleteUser($id) {
        header('Content-Type: application/json');

        AuthController::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        // Prevent deleting yourself
        if ($id == Session::getUserId()) {
            echo json_encode(['success' => false, 'error' => 'You cannot delete your own account']);
            exit;
        }

        $userModel = $this->model('User');

        $success = $userModel->deleteUser($id);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
        }
        exit;
    }

    /**
     * Manage blog posts - List all posts
     */
    public function blog() {
        $blogPostModel = $this->model('BlogPost');

        // Get filter and search parameters
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Get posts and total count
        $posts = $blogPostModel->getAllPosts($status, $search, $perPage, $offset);
        $totalPosts = $blogPostModel->getPostCount($status, $search);
        $totalPages = ceil($totalPosts / $perPage);

        $data = [
            'title' => 'Manage Blog - Admin Dashboard',
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
            'status' => $status,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/blog', $data);
    }

    /**
     * Create new blog post - Show form
     */
    public function createBlogPost() {
        $categoryModel = $this->model('Category');
        $categories = $categoryModel->getAllCategories();

        $data = [
            'title' => 'Create New Post - Blog Admin',
            'categories' => $categories,
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/blog_form', $data);
    }

    /**
     * Store new blog post - Handle form submission
     */
    public function storeBlogPost() {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/blog');
            exit;
        }

        // Verify CSRF token
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header('Location: /admin/createBlogPost');
            exit;
        }

        // Validate input
        $errors = $this->validateBlogPost($_POST);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header('Location: /admin/createBlogPost');
            exit;
        }

        $blogPostModel = $this->model('BlogPost');
        $tagModel = $this->model('Tag');

        // Prepare post data
        $postData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'],
            'featured_image' => $_POST['featured_image'] ?? null,
            'category_id' => (int)$_POST['category_id'],
            'author_id' => Session::getUserId(),
            'status' => $_POST['status'] ?? 'draft',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'published_at' => $_POST['published_at'] ?? null
        ];

        // Create post
        $postId = $blogPostModel->createPost($postData);

        if ($postId) {
            // Handle tags
            if (!empty($_POST['tags'])) {
                $tagNames = array_map('trim', explode(',', $_POST['tags']));
                $tagIds = $tagModel->findOrCreateTags($tagNames);
                $blogPostModel->attachTags($postId, $tagIds);
            }

            Session::setFlash('success', 'Blog post created successfully!');
            header('Location: /admin/blog');
        } else {
            Session::setFlash('error', 'Failed to create blog post. Please try again.');
            header('Location: /admin/createBlogPost');
        }
        exit;
    }

    /**
     * Edit blog post - Show form
     */
    public function editBlogPost($id) {
        $blogPostModel = $this->model('BlogPost');
        $categoryModel = $this->model('Category');

        $post = $blogPostModel->getPostById($id);

        if (!$post) {
            Session::setFlash('error', 'Blog post not found.');
            header('Location: /admin/blog');
            exit;
        }

        $categories = $categoryModel->getAllCategories();

        // Get tags as comma-separated string
        $tagNames = array_map(function($tag) {
            return $tag['name'];
        }, $post->tags);
        $tagsString = implode(', ', $tagNames);

        $data = [
            'title' => 'Edit Post - Blog Admin',
            'post' => $post,
            'categories' => $categories,
            'tagsString' => $tagsString,
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/blog_form', $data);
    }

    /**
     * Update blog post - Handle form submission
     */
    public function updateBlogPost($id) {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/blog');
            exit;
        }

        // Verify CSRF token
        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header("Location: /admin/editBlogPost/$id");
            exit;
        }

        // Validate input
        $errors = $this->validateBlogPost($_POST, $id);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header("Location: /admin/editBlogPost/$id");
            exit;
        }

        $blogPostModel = $this->model('BlogPost');
        $tagModel = $this->model('Tag');

        // Prepare post data
        $postData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'],
            'featured_image' => $_POST['featured_image'] ?? null,
            'category_id' => (int)$_POST['category_id'],
            'status' => $_POST['status'] ?? 'draft',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'published_at' => $_POST['published_at'] ?? null
        ];

        // Update post
        $success = $blogPostModel->updatePost($id, $postData);

        if ($success) {
            // Handle tags
            if (isset($_POST['tags'])) {
                $tagNames = !empty($_POST['tags']) ? array_map('trim', explode(',', $_POST['tags'])) : [];
                $tagIds = $tagModel->findOrCreateTags($tagNames);
                $blogPostModel->attachTags($id, $tagIds);
            }

            Session::setFlash('success', 'Blog post updated successfully!');
            header('Location: /admin/blog');
        } else {
            Session::setFlash('error', 'Failed to update blog post. Please try again.');
            header("Location: /admin/editBlogPost/$id");
        }
        exit;
    }

    /**
     * Delete blog post
     */
    public function deleteBlogPost($id) {
        $blogPostModel = $this->model('BlogPost');

        $success = $blogPostModel->deletePost($id);

        if ($success) {
            Session::setFlash('success', 'Blog post deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete blog post.');
        }

        header('Location: /admin/blog');
        exit;
    }

    /**
     * Toggle featured status (AJAX)
     */
    public function toggleFeatured($id) {
        header('Content-Type: application/json');

        $type = $_GET['type'] ?? 'post'; // 'post' or 'project'

        if ($type === 'project') {
            $projectModel = $this->model('Project');
            $success = $projectModel->toggleFeatured($id);
        } else {
            $blogPostModel = $this->model('BlogPost');
            $success = $blogPostModel->toggleFeatured($id);
        }

        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Upload image (AJAX)
     */
    public function uploadImage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'No image provided']);
            exit;
        }

        $file = $_FILES['image'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, WEBP, and GIF allowed.']);
            exit;
        }

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB.']);
            exit;
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('blog_') . '_' . time() . '.' . $extension;

        // Upload path
        $uploadDir = dirname(__DIR__, 2) . '/public/images/blog/uploads/';
        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Return relative URL
            $imageUrl = '/images/blog/uploads/' . $filename;

            echo json_encode([
                'success' => true,
                'url' => $imageUrl,
                'filename' => $filename
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
        }

        exit;
    }

    /**
     * Validate blog post data
     */
    private function validateBlogPost($data, $excludeId = null) {
        $errors = [];

        // Title validation
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 200) {
            $errors[] = 'Title must not exceed 200 characters';
        }

        // Content validation
        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }

        // Category validation
        if (empty($data['category_id'])) {
            $errors[] = 'Category is required';
        }

        // Slug validation (if provided)
        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens';
            }

            // Check uniqueness
            $blogPostModel = $this->model('BlogPost');
            if ($excludeId) {
                // Editing - check if slug exists for different post
                $existing = $blogPostModel->getAllPosts(null, null, 1, 0);
                // This is a simplified check - in production, you'd have a dedicated method
            }
        }

        return $errors;
    }

    /**
     * Category management - List categories
     */
    public function categories() {
        $categoryModel = $this->model('Category');
        $categories = $categoryModel->getAllCategories();

        // Get post count for each category
        foreach ($categories as &$category) {
            $category['post_count'] = $categoryModel->getPostCount($category['id']);
        }

        $data = [
            'title' => 'Manage Categories - Blog Admin',
            'categories' => $categories,
            'csrf_token' => Session::generateCsrfToken(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/categories', $data);
    }

    /**
     * Create category
     */
    public function storeCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/categories');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request');
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');

        $categoryData = [
            'name' => trim($_POST['name']),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? '')
        ];

        $categoryId = $categoryModel->createCategory($categoryData);

        if ($categoryId) {
            Session::setFlash('success', 'Category created successfully!');
        } else {
            Session::setFlash('error', 'Failed to create category.');
        }

        header('Location: /admin/categories');
        exit;
    }

    /**
     * Update category
     */
    public function updateCategory($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/categories');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request');
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');

        $categoryData = [
            'name' => trim($_POST['name']),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? '')
        ];

        $success = $categoryModel->updateCategory($id, $categoryData);

        if ($success) {
            Session::setFlash('success', 'Category updated successfully!');
        } else {
            Session::setFlash('error', 'Failed to update category.');
        }

        header('Location: /admin/categories');
        exit;
    }

    /**
     * Delete category
     */
    public function deleteCategory($id) {
        $categoryModel = $this->model('Category');

        $success = $categoryModel->deleteCategory($id);

        if ($success) {
            Session::setFlash('success', 'Category deleted successfully.');
        } else {
            Session::setFlash('error', 'Cannot delete category. It may have blog posts associated with it.');
        }

        header('Location: /admin/categories');
        exit;
    }

    /**
     * Manage projects - List all projects
     */
    public function projects() {
        $projectModel = $this->model('Project');

        // Get filter and search parameters
        $status = $_GET['status'] ?? null;
        $categoryId = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Get projects and total count
        $projects = $projectModel->getAllProjects($status, $categoryId, $search, $perPage, $offset);
        $totalProjects = $projectModel->getProjectCount($status, $categoryId, $search);
        $totalPages = ceil($totalProjects / $perPage);

        // Get categories for filter
        $categories = $projectModel->getCategories();

        $data = [
            'title' => 'Manage Projects - Admin Dashboard',
            'projects' => $projects,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProjects' => $totalProjects,
            'status' => $status,
            'categoryId' => $categoryId,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/projects', $data);
    }

    /**
     * Create new project - Show form
     */
    public function createProject() {
        $projectModel = $this->model('Project');
        $categories = $projectModel->getCategories();

        $data = [
            'title' => 'Create New Project - Admin',
            'categories' => $categories,
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/project_form', $data);
    }

    /**
     * Store new project - Handle form submission
     */
    public function storeProject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/projects');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header('Location: /admin/createProject');
            exit;
        }

        // Validate input
        $errors = $this->validateProject($_POST);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header('Location: /admin/createProject');
            exit;
        }

        $projectModel = $this->model('Project');

        // Prepare project data
        $projectData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'content' => $_POST['content'],
            'category_id' => (int)$_POST['category_id'],
            'featured_image' => $_POST['featured_image'] ?? null,
            'client' => trim($_POST['client'] ?? ''),
            'completion_date' => $_POST['completion_date'] ?? null,
            'technologies' => trim($_POST['technologies'] ?? ''),
            'project_url' => trim($_POST['project_url'] ?? ''),
            'github_url' => trim($_POST['github_url'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => Session::getUserId()
        ];

        // Create project
        $projectId = $projectModel->createProject($projectData);

        if ($projectId) {
            // Handle gallery images if provided
            if (!empty($_POST['gallery_images'])) {
                $galleryImages = json_decode($_POST['gallery_images'], true);
                if (is_array($galleryImages)) {
                    foreach ($galleryImages as $index => $imagePath) {
                        $projectModel->addProjectImage($projectId, $imagePath, null, $index + 1);
                    }
                }
            }

            Session::setFlash('success', 'Project created successfully!');
            header('Location: /admin/projects');
        } else {
            Session::setFlash('error', 'Failed to create project. Please try again.');
            header('Location: /admin/createProject');
        }
        exit;
    }

    /**
     * Edit project - Show form
     */
    public function editProject($id) {
        $projectModel = $this->model('Project');

        $project = $projectModel->getProjectById($id);

        if (!$project) {
            Session::setFlash('error', 'Project not found.');
            header('Location: /admin/projects');
            exit;
        }

        $categories = $projectModel->getCategories();

        $data = [
            'title' => 'Edit Project - Admin',
            'project' => $project,
            'categories' => $categories,
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/project_form', $data);
    }

    /**
     * Update project - Handle form submission
     */
    public function updateProject($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/projects');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header("Location: /admin/editProject/$id");
            exit;
        }

        // Validate input
        $errors = $this->validateProject($_POST, $id);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header("Location: /admin/editProject/$id");
            exit;
        }

        $projectModel = $this->model('Project');

        // Prepare project data
        $projectData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'content' => $_POST['content'],
            'category_id' => (int)$_POST['category_id'],
            'featured_image' => $_POST['featured_image'] ?? null,
            'client' => trim($_POST['client'] ?? ''),
            'completion_date' => $_POST['completion_date'] ?? null,
            'technologies' => trim($_POST['technologies'] ?? ''),
            'project_url' => trim($_POST['project_url'] ?? ''),
            'github_url' => trim($_POST['github_url'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'draft'
        ];

        // Update project
        $success = $projectModel->updateProject($id, $projectData);

        if ($success) {
            Session::setFlash('success', 'Project updated successfully!');
            header('Location: /admin/projects');
        } else {
            Session::setFlash('error', 'Failed to update project. Please try again.');
            header("Location: /admin/editProject/$id");
        }
        exit;
    }

    /**
     * Delete project
     */
    public function deleteProject($id) {
        $projectModel = $this->model('Project');

        $success = $projectModel->deleteProject($id);

        if ($success) {
            Session::setFlash('success', 'Project deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete project.');
        }

        header('Location: /admin/projects');
        exit;
    }

    /**
     * Upload project image (AJAX)
     */
    public function uploadProjectImage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'No image provided']);
            exit;
        }

        $file = $_FILES['image'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, WEBP, and GIF allowed.']);
            exit;
        }

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB.']);
            exit;
        }

        // Determine upload directory based on type
        $type = $_POST['type'] ?? 'featured'; // 'featured' or 'gallery'

        if ($type === 'gallery') {
            $uploadDir = dirname(__DIR__, 2) . '/public/images/projects/gallery/';
            $prefix = 'gallery_';
        } else {
            $uploadDir = dirname(__DIR__, 2) . '/public/images/projects/uploads/';
            $prefix = 'project_';
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($prefix) . '_' . time() . '.' . $extension;

        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Return relative URL
            $imageUrl = ($type === 'gallery')
                ? '/images/projects/gallery/' . $filename
                : '/images/projects/uploads/' . $filename;

            echo json_encode([
                'success' => true,
                'url' => $imageUrl,
                'filename' => $filename
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
        }

        exit;
    }

    /**
     * Validate project data
     */
    private function validateProject($data, $excludeId = null) {
        $errors = [];

        // Title validation
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 200) {
            $errors[] = 'Title must not exceed 200 characters';
        }

        // Content validation
        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }

        // Category validation
        if (empty($data['category_id'])) {
            $errors[] = 'Category is required';
        }

        // Slug validation (if provided)
        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens';
            }
        }

        return $errors;
    }

    /**
     * Manage resources - List all resources
     */
    public function resources() {
        $resourceModel = $this->model('Resource');

        // Get filter and search parameters
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Get resources and total count
        $resources = $resourceModel->getAllResources($status, $search, $perPage, $offset);
        $totalResources = $resourceModel->getResourceCount($status, $search);
        $totalPages = ceil($totalResources / $perPage);

        $data = [
            'title' => 'Manage Resources - Admin Dashboard',
            'resources' => $resources,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalResources' => $totalResources,
            'status' => $status,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/resources', $data);
    }

    /**
     * Create new resource - Show form
     */
    public function createResource() {
        $data = [
            'title' => 'Create New Resource - Admin',
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/resource_form', $data);
    }

    /**
     * Store new resource - Handle form submission
     */
    public function storeResource() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/resources');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header('Location: /admin/createResource');
            exit;
        }

        // Validate input
        $errors = $this->validateResource($_POST);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header('Location: /admin/createResource');
            exit;
        }

        $resourceModel = $this->model('Resource');

        // Prepare resource data
        $resourceData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'file_path' => $_POST['file_path'],
            'file_size' => (int)$_POST['file_size'],
            'file_type' => $_POST['file_type'],
            'thumbnail' => $_POST['thumbnail'] ?? null,
            'requires_login' => isset($_POST['requires_login']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => Session::getUserId()
        ];

        // Create resource
        $resourceId = $resourceModel->createResource($resourceData);

        if ($resourceId) {
            Session::setFlash('success', 'Resource created successfully!');
            header('Location: /admin/resources');
        } else {
            Session::setFlash('error', 'Failed to create resource. Please try again.');
            header('Location: /admin/createResource');
        }
        exit;
    }

    /**
     * Edit resource - Show form
     */
    public function editResource($id) {
        $resourceModel = $this->model('Resource');

        $resource = $resourceModel->getResourceById($id);

        if (!$resource) {
            Session::setFlash('error', 'Resource not found.');
            header('Location: /admin/resources');
            exit;
        }

        $data = [
            'title' => 'Edit Resource - Admin',
            'resource' => $resource,
            'csrf_token' => Session::generateCsrfToken(),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/resource_form', $data);
    }

    /**
     * Update resource - Handle form submission
     */
    public function updateResource($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/resources');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Invalid request. Please try again.');
            header("Location: /admin/editResource/$id");
            exit;
        }

        // Validate input
        $errors = $this->validateResource($_POST, $id);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header("Location: /admin/editResource/$id");
            exit;
        }

        $resourceModel = $this->model('Resource');

        // Prepare resource data
        $resourceData = [
            'title' => trim($_POST['title']),
            'slug' => !empty($_POST['slug']) ? trim($_POST['slug']) : null,
            'description' => trim($_POST['description'] ?? ''),
            'file_path' => $_POST['file_path'],
            'file_size' => (int)$_POST['file_size'],
            'file_type' => $_POST['file_type'],
            'thumbnail' => $_POST['thumbnail'] ?? null,
            'requires_login' => isset($_POST['requires_login']) ? 1 : 0,
            'status' => $_POST['status'] ?? 'draft'
        ];

        // Update resource
        $success = $resourceModel->updateResource($id, $resourceData);

        if ($success) {
            Session::setFlash('success', 'Resource updated successfully!');
            header('Location: /admin/resources');
        } else {
            Session::setFlash('error', 'Failed to update resource. Please try again.');
            header("Location: /admin/editResource/$id");
        }
        exit;
    }

    /**
     * Delete resource
     */
    public function deleteResource($id) {
        $resourceModel = $this->model('Resource');

        $success = $resourceModel->deleteResource($id);

        if ($success) {
            Session::setFlash('success', 'Resource deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete resource.');
        }

        header('Location: /admin/resources');
        exit;
    }

    /**
     * Upload resource file (AJAX)
     */
    public function uploadResourceFile() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        if (!isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'error' => 'No file provided']);
            exit;
        }

        $file = $_FILES['file'];
        $resourceModel = $this->model('Resource');

        // Validate file type
        if (!$resourceModel->validateFileType($file['type'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit;
        }

        // Validate file size (max 50MB for resources)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 50MB.']);
            exit;
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('resource_') . '_' . time() . '.' . $extension;

        // Upload path
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/resources/';
        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Return file info
            $fileUrl = '/uploads/resources/' . $filename;

            echo json_encode([
                'success' => true,
                'url' => $fileUrl,
                'filename' => $filename,
                'size' => $file['size'],
                'type' => $file['type']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        }

        exit;
    }

    /**
     * Validate resource data
     */
    private function validateResource($data, $excludeId = null) {
        $errors = [];

        // Title validation
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 200) {
            $errors[] = 'Title must not exceed 200 characters';
        }

        // File path validation
        if (empty($data['file_path'])) {
            $errors[] = 'File is required';
        }

        // Slug validation (if provided)
        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens';
            }
        }

        return $errors;
    }

    /**
     * View contact submissions
     */
    public function contacts() {
        $contactModel = $this->model('Contact');
        $submissions = $contactModel->getAllSubmissions();

        $data = [
            'title' => 'Contact Submissions - Admin Dashboard',
            'submissions' => $submissions,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ];

        $this->view('admin/contacts', $data);
    }

    /**
     * Mark contact as read (AJAX)
     */
    public function markContactRead($id) {
        header('Content-Type: application/json');

        $contactModel = $this->model('Contact');
        $success = $contactModel->markAsRead($id);

        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Delete contact submission (AJAX)
     */
    public function deleteContact($id) {
        header('Content-Type: application/json');

        $contactModel = $this->model('Contact');
        $success = $contactModel->deleteSubmission($id);

        echo json_encode(['success' => $success]);
        exit;
    }
}
