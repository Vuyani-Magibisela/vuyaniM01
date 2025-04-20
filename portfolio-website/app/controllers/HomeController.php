<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Display the home page
     *
     * @return void
     */
    public function index()
    {
        // You can load data for the home page here
        $data = [
            'title' => 'Welcome to My Portfolio',
            'description' => 'Showcasing my work and experience',
            'featured_projects' => $this->getFeaturedProjects(),
            'recent_posts' => $this->getRecentPosts()
        ];

        // Render the home page view with data
        $this->view('home/index', $data);
    }

    /**
     * Get featured projects to display on the home page
     *
     * @return array
     */
    private function getFeaturedProjects()
    {
        // fetch featured projects from database
        return[];

    }

    /**
     * Get recent blog posts to display on the home page
     *
     * @return array
     */
    private function getRecentPosts()
    {
        // fetch recent posts from the database
        return[];        
      
    }

    /**
     * About page method
     *
     * @return void
     */
    public function about()
    {
        $data = [
            'title' => 'About Me',
            'description' => 'Learn more about my skills and experience'
        ];

        $this->view('home/about', $data);
    }

    /**
     * Resume/CV page method
     *
     * @return void
     */
    public function resume()
    {
        $data = [
            'title' => 'My Resume',
            'description' => 'Professional experience and education'
        ];

        $this->view('home/resume', $data);
    }
}