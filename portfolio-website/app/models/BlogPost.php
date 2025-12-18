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

            // Execute the query and add tags to post array
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

    // ========================================
    // ADMIN CRUD METHODS
    // ========================================

    /**
     * Get all posts for admin (including drafts)
     * @param string|null $status Filter by status (draft/published)
     * @param string|null $search Search term for title
     * @param int $limit Posts per page
     * @param int $offset Pagination offset
     * @return array
     */
    public function getAllPosts($status = null, $search = null, $limit = 20, $offset = 0) {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                         u.username as author_name, u.first_name, u.last_name
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.author_id = u.id
                  WHERE 1=1";

        $params = [];

        if ($status) {
            $query .= " AND p.status = :status";
            $params['status'] = $status;
        }

        if ($search) {
            $query .= " AND p.title LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->query($query, $params);
    }

    /**
     * Get total count of posts (for pagination)
     * @param string|null $status
     * @param string|null $search
     * @return int
     */
    public function getPostCount($status = null, $search = null) {
        if (!$this->isConnected()) {
            return 0;
        }

        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($status) {
            $query .= " AND status = :status";
            $params['status'] = $status;
        }

        if ($search) {
            $query .= " AND title LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $result = $this->query($query, $params, false);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get post by ID (for editing)
     * @param int $id
     * @return object|null
     */
    public function getPostById($id) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT p.*, c.name as category_name
                  FROM {$this->table} p
                  LEFT JOIN blog_categories c ON p.category_id = c.id
                  WHERE p.id = :id";

        $post = $this->query($query, ['id' => $id], false);

        if ($post) {
            // Get tags and add to post array
            $post['tags'] = $this->getPostTags($id);
            // Convert to object for admin editing
            return (object)$post;
        }

        return null;
    }

    /**
     * Create new blog post
     * @param array $data
     * @return int|false Post ID or false on failure
     */
    public function createPost($data) {
        if (!$this->isConnected()) {
            error_log('Post creation attempted without database connection');
            return false;
        }

        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }

            // Set published_at if status is published
            $publishedAt = null;
            if ($data['status'] === 'published') {
                $publishedAt = $data['published_at'] ?? date('Y-m-d H:i:s');
            }

            $query = "INSERT INTO {$this->table}
                      (title, slug, excerpt, content, featured_image, category_id, author_id, status, is_featured, published_at, created_at)
                      VALUES (:title, :slug, :excerpt, :content, :featured_image, :category_id, :author_id, :status, :is_featured, :published_at, NOW())";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'],
                'featured_image' => $data['featured_image'] ?? null,
                'category_id' => $data['category_id'],
                'author_id' => $data['author_id'],
                'status' => $data['status'] ?? 'draft',
                'is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
                'published_at' => $publishedAt
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (\Exception $e) {
            error_log('Post creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update blog post
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePost($id, $data) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Generate slug if title changed but slug not provided
            if (isset($data['title']) && empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title'], $id);
            }

            // Handle published_at
            $publishedAt = $data['published_at'] ?? null;
            if ($data['status'] === 'published' && !$publishedAt) {
                // Get current post to check if it's transitioning from draft
                $currentPost = $this->getPostById($id);
                if ($currentPost && $currentPost->status === 'draft') {
                    $publishedAt = date('Y-m-d H:i:s');
                }
            }

            $query = "UPDATE {$this->table}
                      SET title = :title,
                          slug = :slug,
                          excerpt = :excerpt,
                          content = :content,
                          featured_image = :featured_image,
                          category_id = :category_id,
                          status = :status,
                          is_featured = :is_featured,
                          published_at = :published_at,
                          updated_at = NOW()
                      WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'],
                'featured_image' => $data['featured_image'] ?? null,
                'category_id' => $data['category_id'],
                'status' => $data['status'] ?? 'draft',
                'is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
                'published_at' => $publishedAt
            ]);
        } catch (\Exception $e) {
            error_log('Post update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete blog post
     * @param int $id
     * @return bool
     */
    public function deletePost($id) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Delete associated tags (should cascade automatically, but being explicit)
            $this->query("DELETE FROM blog_post_tags WHERE post_id = :id", ['id' => $id], false);

            // Delete the post
            return parent::delete($this->table, $id);
        } catch (\Exception $e) {
            error_log('Post deletion error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle featured status
     * @param int $id
     * @return bool
     */
    public function toggleFeatured($id) {
        if (!$this->isConnected()) {
            return false;
        }

        $query = "UPDATE {$this->table} SET is_featured = NOT is_featured WHERE id = :id";
        return $this->query($query, ['id' => $id], false) !== false;
    }

    /**
     * Update post status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        if (!$this->isConnected()) {
            return false;
        }

        if (!in_array($status, ['draft', 'published'])) {
            return false;
        }

        $publishedAt = null;
        if ($status === 'published') {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $query = "UPDATE {$this->table}
                  SET status = :status, published_at = :published_at
                  WHERE id = :id";

        return $this->query($query, [
            'id' => $id,
            'status' => $status,
            'published_at' => $publishedAt
        ], false) !== false;
    }

    /**
     * Generate URL-friendly slug from title
     * @param string $title
     * @param int|null $excludeId ID to exclude when checking uniqueness
     * @return string
     */
    public function generateSlug($title, $excludeId = null) {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    private function slugExists($slug, $excludeId = null) {
        if (!$this->isConnected()) {
            return false;
        }

        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";

        if ($excludeId) {
            $query .= " AND id != :id";
            $result = $this->query($query, ['slug' => $slug, 'id' => $excludeId], false);
        } else {
            $result = $this->query($query, ['slug' => $slug], false);
        }

        return $result && $result['count'] > 0;
    }

    /**
     * Attach tags to post
     * @param int $postId
     * @param array $tagIds
     * @return bool
     */
    public function attachTags($postId, $tagIds) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // First, remove existing tags
            $this->query("DELETE FROM blog_post_tags WHERE post_id = :id", ['id' => $postId], false);

            // Then add new tags
            if (!empty($tagIds)) {
                $query = "INSERT INTO blog_post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
                $stmt = $this->db->prepare($query);

                foreach ($tagIds as $tagId) {
                    $stmt->execute([
                        'post_id' => $postId,
                        'tag_id' => $tagId
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Tag attachment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tags for a post
     * @param int $postId
     * @return array
     */
    public function getPostTags($postId) {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT t.id, t.name, t.slug
                  FROM tags t
                  JOIN blog_post_tags pt ON t.id = pt.tag_id
                  WHERE pt.post_id = :post_id
                  ORDER BY t.name ASC";

        return $this->query($query, ['post_id' => $postId]);
    }
}