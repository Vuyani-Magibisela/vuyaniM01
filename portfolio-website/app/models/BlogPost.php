<?php

namespace App\Models;

use PDO;

class BlogPost extends BaseModel {
    private $table = 'blog_posts';
    private $dummyData;
    
    public function __construct() {
        parent::__construct();
        
        // If database connection failed, load dummy data
        if (!$this->isConnected()) {
            $dummyDataPath = dirname(__DIR__) . '/data/blog_dummy_data.php';
            if (file_exists($dummyDataPath)) {
                $this->dummyData = require $dummyDataPath;
            } else {
                $this->dummyData = [
                    'posts' => [],
                    'featuredPosts' => [],
                    'categories' => []
                ];
                error_log('Blog dummy data file not found: ' . $dummyDataPath);
            }
        }
    }
    
    public function getRecentPosts($limit = 10) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            $posts = array_slice($this->dummyData['posts'], 0, $limit);
            return $posts;
        }
        
        // Use database if available
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  WHERE p.status = 'published' AND p.published_at <= NOW()
                  ORDER BY p.published_at DESC
                  LIMIT :limit";
                  
        return $this->query($query, ['limit' => $limit]);
    }
    
    public function getPostBySlug($slug) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            foreach ($this->dummyData['posts'] as $post) {
                if ($post['slug'] === $slug) {
                    return $post;
                }
            }
            return null;
        }
        
        // Use database if available
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name, u.first_name, u.last_name
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  WHERE p.slug = :slug AND p.status = 'published' AND p.published_at <= NOW()";
                  
        $post = $this->query($query, ['slug' => $slug], false);
        
        if ($post) {
            // Get tags for this post
            $tagsQuery = "SELECT t.name, t.slug 
                          FROM tags t
                          JOIN blog_post_tags pt ON t.id = pt.tag_id
                          WHERE pt.post_id = :post_id";
                          
            // Execute the query and assign the result to post['tags']
            $tags = $this->query($tagsQuery, ['post_id' => $post['id']]);
            $post['tags'] = $tags;
        }
        
        return $post;
    }
    
    public function getRelatedPosts($postId, $categoryId, $limit = 3) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            $relatedPosts = [];
            $count = 0;
            
            foreach ($this->dummyData['posts'] as $post) {
                if ($post['id'] != $postId && $post['category_id'] == $categoryId && $count < $limit) {
                    $relatedPosts[] = [
                        'id' => $post['id'],
                        'title' => $post['title'],
                        'slug' => $post['slug'],
                        'excerpt' => $post['excerpt'],
                        'featured_image' => $post['featured_image'],
                        'published_at' => $post['published_at']
                    ];
                    $count++;
                }
            }
            
            return $relatedPosts;
        }
        
        // Use database if available
        $query = "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.published_at
                  FROM {$this->table} p
                  WHERE p.id != :post_id AND p.category_id = :category_id 
                        AND p.status = 'published' AND p.published_at <= NOW()
                  ORDER BY p.published_at DESC
                  LIMIT :limit";
                  
        return $this->query($query, [
            'post_id' => $postId,
            'category_id' => $categoryId,
            'limit' => $limit
        ]);
    }
    
    public function incrementViews($postId) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            foreach ($this->dummyData['posts'] as &$post) {
                if ($post['id'] == $postId) {
                    $post['views']++;
                    break;
                }
            }
            return true;
        }
        
        // Use database if available
        $query = "UPDATE {$this->table} SET views = views + 1 WHERE id = :id";
        return $this->query($query, ['id' => $postId], false);
    }
    
    public function getFeaturedPosts($limit = 3) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            return isset($this->dummyData['featuredPosts']) ? 
                   array_slice($this->dummyData['featuredPosts'], 0, $limit) : [];
        }
        
        // Use database if available
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  WHERE p.is_featured = 1 AND p.status = 'published' AND p.published_at <= NOW()
                  ORDER BY p.published_at DESC
                  LIMIT :limit";
                  
        return $this->query($query, ['limit' => $limit]);
    }
    
    public function getPostsByCategory($categorySlug, $limit = 10, $offset = 0) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            $posts = [];
            $count = 0;
            
            foreach ($this->dummyData['posts'] as $post) {
                if ($post['category_slug'] == $categorySlug && $count < $limit) {
                    $posts[] = $post;
                    $count++;
                }
            }
            
            return $posts;
        }
        
        // Use database if available
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.username as author_name
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  WHERE c.slug = :category_slug AND p.status = 'published' AND p.published_at <= NOW()
                  ORDER BY p.published_at DESC
                  LIMIT :limit OFFSET :offset";
                  
        return $this->query($query, [
            'category_slug' => $categorySlug,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
}