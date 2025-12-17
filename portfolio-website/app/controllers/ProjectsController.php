<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProjectsController extends BaseController {

    /**
     * Display all published projects (main projects page)
     */
    public function index() {
        $projectModel = $this->model('Project');

        // Get all published projects
        $projects = $projectModel->getAllPublished();

        // Get featured projects
        $featuredProjects = $projectModel->getFeaturedProjects(6);

        // Get categories for filtering
        $categories = $projectModel->getCategories();

        $data = [
            'title' => 'Projects - Vuyani Magibisela',
            'projects' => $projects,
            'featuredProjects' => $featuredProjects,
            'categories' => $categories
        ];

        $this->view('projects/index', $data);
    }

    /**
     * Display projects filtered by category
     * @param string $categorySlug Category slug (e.g., 'web-dev', 'app-dev')
     */
    public function category($categorySlug = '') {
        if (empty($categorySlug)) {
            header('Location: /projects');
            exit;
        }

        $projectModel = $this->model('Project');
        $categories = $projectModel->getCategories();

        // Find category by slug
        $categoryId = null;
        $categoryName = '';
        foreach ($categories as $category) {
            if ($category['slug'] === $categorySlug) {
                $categoryId = $category['id'];
                $categoryName = $category['name'];
                break;
            }
        }

        if (!$categoryId) {
            // Category not found, redirect to all projects
            header('Location: /projects');
            exit;
        }

        // Get projects for this category
        $projects = $projectModel->getAllPublished(null, $categoryId);

        $data = [
            'title' => $categoryName . ' Projects - Vuyani Magibisela',
            'projects' => $projects,
            'categories' => $categories,
            'currentCategory' => $categorySlug,
            'categoryName' => $categoryName
        ];

        $this->view('projects/category', $data);
    }

    /**
     * Display a single project by slug
     * @param string $slug Project slug
     */
    public function detail($slug = '') {
        if (empty($slug)) {
            header('Location: /projects');
            exit;
        }

        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectBySlug($slug);

        if (!$project) {
            // Project not found, redirect to projects page
            header('Location: /projects');
            exit;
        }

        // Get related projects from the same category
        $relatedProjects = $projectModel->getAllPublished(3, $project['category_id']);

        // Remove current project from related projects
        $relatedProjects = array_filter($relatedProjects, function($p) use ($project) {
            return $p['id'] !== $project['id'];
        });

        // Limit to 3 related projects
        $relatedProjects = array_slice($relatedProjects, 0, 3);

        $data = [
            'title' => $project['title'] . ' - Projects',
            'project' => $project,
            'relatedProjects' => $relatedProjects
        ];

        $this->view('projects/project_detail', $data);
    }

    /**
     * Legacy method - redirect to detail by slug
     * Maps old URLs like /projects/web-dev/1 to new slug-based URLs
     */
    public function show($categorySlug = '', $id = '') {
        if (empty($id)) {
            header('Location: /projects');
            exit;
        }

        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($id);

        if (!$project || $project['status'] !== 'published') {
            header('Location: /projects');
            exit;
        }

        // Redirect to slug-based URL
        header('Location: /projects/detail/' . $project['slug']);
        exit;
    }

    // Legacy category-specific methods - redirect to category filter

    public function digitalDesign() {
        header('Location: /projects/category/digital-design');
        exit;
    }

    public function webDev() {
        header('Location: /projects/category/web-dev');
        exit;
    }

    public function appDev() {
        header('Location: /projects/category/app-dev');
        exit;
    }

    public function gameDev() {
        header('Location: /projects/category/game-dev');
        exit;
    }

    public function makerProjects() {
        header('Location: /projects/category/maker');
        exit;
    }
}
