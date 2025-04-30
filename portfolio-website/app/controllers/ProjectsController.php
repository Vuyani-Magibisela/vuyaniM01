<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProjectsController extends BaseController {

    public function index() {
        // Load the view for the main projects page
        $this->view('projects/index');
    }
    
    public function digitalDesign() {
        // Load the digital design projects view
        $this->view('projects/digital_design');
    }
    
    public function webDev() {
        // Load the web development projects view
        $this->view('projects/web_dev');
    }
    
    public function appDev() {
        // Load the app development projects view
        $this->view('projects/app_dev');
    }
    
    public function gameDev() {
        // Load the game development projects view
        $this->view('projects/game_dev');
    }
    
    public function makerProjects() {
        // Load the maker projects view
        $this->view('projects/maker_projects');
    }
    
    /**
     * Show a single project
     * @param string $category Project category
     * @param int $id Project ID
     */
    public function show($category, $id) {
        // In a real-world scenario, you would fetch this data from a database
        // For now, we'll use static data for demonstration
        
        // Sample project data (would come from a model in a real application)
        $project = $this->getSampleProject($category, $id);
        
        // Get previous and next projects (for navigation)
        $prevProject = $this->getSamplePrevProject($category, $id);
        $nextProject = $this->getSampleNextProject($category, $id);
        
        // Get related projects
        $relatedProjects = $this->getSampleRelatedProjects($category, $id);
        
        // Pass data to the view
        $data = [
            'project' => $project,
            'prevProject' => $prevProject,
            'nextProject' => $nextProject,
            'relatedProjects' => $relatedProjects
        ];
        
        // Load the project detail view with data
        $this->view('projects/project_detail', $data);
    }
    
    /**
     * Get sample project data (this would normally come from a database)
     * @param string $category Project category
     * @param int $id Project ID
     * @return array Project data
     */
    private function getSampleProject($category, $id) {
        // This is just example data - in a real application, you would fetch this from a database
        return [
            'id' => $id,
            'title' => 'Sample Project ' . $id,
            'category' => $category,
            'date' => 'January 2025',
            'tags' => ['PHP', 'JavaScript', 'CSS'],
            'main_image' => 'project' . $id . '.jpg',
            'gallery' => ['project' . $id . '_1.jpg', 'project' . $id . '_2.jpg', 'project' . $id . '_3.jpg'],
            'description' => '<p>This is a sample project description. In a real application, this would be fetched from a database. This text describes the project, its goals, and the process of creating it.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies aliquam, nisl nisl aliquet nisl, eget aliquam nisl nisl eget nisl. Nullam auctor, nisl eget ultricies aliquam, nisl nisl aliquet nisl, eget aliquam nisl nisl eget nisl.</p>',
            'challenges' => '<p>During the development of this project, several challenges were encountered. This section describes those challenges and how they were overcome.</p>',
            'technologies' => ['PHP', 'MySQL', 'JavaScript', 'CSS', 'HTML'],
            'link' => 'https://example.com',
            'github' => 'https://github.com/example/project'
        ];
    }
    
    /**
     * Get sample previous project data for navigation
     * @param string $category Project category
     * @param int $id Project ID
     * @return array|null Previous project data or null if none
     */
    private function getSamplePrevProject($category, $id) {
        // In a real application, you would query the database for the previous project
        if ($id > 1) {
            return [
                'id' => $id - 1,
                'title' => 'Sample Project ' . ($id - 1),
                'category' => $category
            ];
        }
        return null;
    }
    
    /**
     * Get sample next project data for navigation
     * @param string $category Project category
     * @param int $id Project ID
     * @return array|null Next project data or null if none
     */
    private function getSampleNextProject($category, $id) {
        // In a real application, you would query the database for the next project
        // For demo purposes, let's assume there are 5 projects per category
        if ($id < 5) {
            return [
                'id' => $id + 1,
                'title' => 'Sample Project ' . ($id + 1),
                'category' => $category
            ];
        }
        return null;
    }
    
    /**
     * Get sample related projects
     * @param string $category Project category
     * @param int $id Project ID
     * @return array Related projects data
     */
    private function getSampleRelatedProjects($category, $id) {
        // In a real application, you would query the database for related projects
        $relatedProjects = [];
        
        // Add 3 related projects (excluding the current one)
        for ($i = 1; $i <= 3; $i++) {
            $relatedId = ($id + $i) % 5 + 1;
            if ($relatedId != $id) {
                $relatedProjects[] = [
                    'id' => $relatedId,
                    'title' => 'Related Project ' . $relatedId,
                    'category' => $category,
                    'thumbnail' => 'project' . $relatedId . '.jpg'
                ];
            }
        }
        
        return $relatedProjects;
    }
}