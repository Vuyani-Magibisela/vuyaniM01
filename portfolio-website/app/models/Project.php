<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Project extends BaseModel
{
    protected $table = 'projects';

    /**
     * Get all projects with optional filters and pagination (Admin)
     */
    public function getAllProjects($status = null, $categoryId = null, $search = null, $limit = 20, $offset = 0)
    {
        if (!$this->isConnected()) {
            error_log("Database not connected in Project::getAllProjects");
            return [];
        }

        try {
            $sql = "SELECT p.*,
                    pc.name as category_name,
                    u.username as author_name,
                    (SELECT COUNT(*) FROM project_images WHERE project_id = p.id) as image_count
                    FROM {$this->table} p
                    LEFT JOIN project_categories pc ON p.category_id = pc.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE 1=1";

            $params = [];

            if ($status) {
                $sql .= " AND p.status = :status";
                $params[':status'] = $status;
            }

            if ($categoryId) {
                $sql .= " AND p.category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }

            if ($search) {
                $sql .= " AND (p.title LIKE :search OR p.description LIKE :search OR p.technologies LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Project::getAllProjects - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of projects with filters
     */
    public function getProjectCount($status = null, $categoryId = null, $search = null)
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
            $params = [];

            if ($status) {
                $sql .= " AND status = :status";
                $params[':status'] = $status;
            }

            if ($categoryId) {
                $sql .= " AND category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }

            if ($search) {
                $sql .= " AND (title LIKE :search OR description LIKE :search OR technologies LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error in Project::getProjectCount - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get project by ID with all related data
     */
    public function getProjectById($id)
    {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $sql = "SELECT p.*,
                    pc.name as category_name,
                    u.username as author_name
                    FROM {$this->table} p
                    LEFT JOIN project_categories pc ON p.category_id = pc.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                // Get project images
                $project['images'] = $this->getProjectImages($id);
            }

            return $project;
        } catch (\PDOException $e) {
            error_log("Error in Project::getProjectById - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get project by slug
     */
    public function getProjectBySlug($slug)
    {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $sql = "SELECT p.*,
                    pc.name as category_name,
                    u.username as author_name
                    FROM {$this->table} p
                    LEFT JOIN project_categories pc ON p.category_id = pc.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.slug = :slug AND p.status = 'published'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':slug' => $slug]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                $project['images'] = $this->getProjectImages($project['id']);
            }

            return $project;
        } catch (\PDOException $e) {
            error_log("Error in Project::getProjectBySlug - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new project
     */
    public function createProject($data)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            } else {
                $data['slug'] = $this->generateSlug($data['slug']);
            }

            // Set published_at if status is published
            $publishedAt = null;
            if ($data['status'] === 'published') {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $sql = "INSERT INTO {$this->table}
                    (title, slug, description, content, category_id, featured_image,
                     client, completion_date, technologies, project_url, github_url,
                     is_featured, status, author_id, published_at, created_at, updated_at)
                    VALUES
                    (:title, :slug, :description, :content, :category_id, :featured_image,
                     :client, :completion_date, :technologies, :project_url, :github_url,
                     :is_featured, :status, :author_id, :published_at, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':content' => $data['content'],
                ':category_id' => $data['category_id'],
                ':featured_image' => $data['featured_image'] ?? null,
                ':client' => $data['client'] ?? null,
                ':completion_date' => $data['completion_date'] ?? null,
                ':technologies' => $data['technologies'] ?? null,
                ':project_url' => $data['project_url'] ?? null,
                ':github_url' => $data['github_url'] ?? null,
                ':is_featured' => $data['is_featured'] ?? 0,
                ':status' => $data['status'] ?? 'draft',
                ':author_id' => $data['author_id'],
                ':published_at' => $publishedAt
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (\PDOException $e) {
            error_log("Error in Project::createProject - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing project
     */
    public function updateProject($id, $data)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Get current project
            $currentProject = $this->getProjectById($id);
            if (!$currentProject) {
                return false;
            }

            // Generate slug if changed
            if (!empty($data['slug']) && $data['slug'] !== $currentProject['slug']) {
                $data['slug'] = $this->generateSlug($data['slug'], $id);
            } elseif (!empty($data['title']) && $data['title'] !== $currentProject['title']) {
                $data['slug'] = $this->generateSlug($data['title'], $id);
            }

            // Handle published_at timestamp
            $publishedAt = $currentProject['published_at'];
            if (isset($data['status'])) {
                if ($data['status'] === 'published' && $currentProject['status'] === 'draft') {
                    $publishedAt = date('Y-m-d H:i:s');
                } elseif ($data['status'] === 'draft') {
                    $publishedAt = null;
                }
            }

            $sql = "UPDATE {$this->table} SET
                    title = :title,
                    slug = :slug,
                    description = :description,
                    content = :content,
                    category_id = :category_id,
                    featured_image = :featured_image,
                    client = :client,
                    completion_date = :completion_date,
                    technologies = :technologies,
                    project_url = :project_url,
                    github_url = :github_url,
                    is_featured = :is_featured,
                    status = :status,
                    published_at = :published_at,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'] ?? $currentProject['title'],
                ':slug' => $data['slug'] ?? $currentProject['slug'],
                ':description' => $data['description'] ?? $currentProject['description'],
                ':content' => $data['content'] ?? $currentProject['content'],
                ':category_id' => $data['category_id'] ?? $currentProject['category_id'],
                ':featured_image' => $data['featured_image'] ?? $currentProject['featured_image'],
                ':client' => $data['client'] ?? $currentProject['client'],
                ':completion_date' => $data['completion_date'] ?? $currentProject['completion_date'],
                ':technologies' => $data['technologies'] ?? $currentProject['technologies'],
                ':project_url' => $data['project_url'] ?? $currentProject['project_url'],
                ':github_url' => $data['github_url'] ?? $currentProject['github_url'],
                ':is_featured' => $data['is_featured'] ?? $currentProject['is_featured'],
                ':status' => $data['status'] ?? $currentProject['status'],
                ':published_at' => $publishedAt
            ]);
        } catch (\PDOException $e) {
            error_log("Error in Project::updateProject - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete project and associated images
     */
    public function deleteProject($id)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Delete associated images first
            $sql = "DELETE FROM project_images WHERE project_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Delete project
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error in Project::deleteProject - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $sql = "UPDATE {$this->table} SET is_featured = NOT is_featured WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error in Project::toggleFeatured - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique slug
     */
    public function generateSlug($title, $excludeId = null)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug exists
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId) {
                $sql .= " AND id != :id";
                $params[':id'] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error in Project::slugExists - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get project images
     */
    public function getProjectImages($projectId)
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $sql = "SELECT * FROM project_images WHERE project_id = :project_id ORDER BY display_order ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Project::getProjectImages - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add image to project gallery
     */
    public function addProjectImage($projectId, $imagePath, $caption = null, $displayOrder = null)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Get max display order if not provided
            if ($displayOrder === null) {
                $sql = "SELECT MAX(display_order) FROM project_images WHERE project_id = :project_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':project_id' => $projectId]);
                $maxOrder = $stmt->fetchColumn();
                $displayOrder = ($maxOrder ?? 0) + 1;
            }

            $sql = "INSERT INTO project_images (project_id, image_path, caption, display_order, created_at)
                    VALUES (:project_id, :image_path, :caption, :display_order, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':project_id' => $projectId,
                ':image_path' => $imagePath,
                ':caption' => $caption,
                ':display_order' => $displayOrder
            ]);
        } catch (\PDOException $e) {
            error_log("Error in Project::addProjectImage - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete project image
     */
    public function deleteProjectImage($imageId)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $sql = "DELETE FROM project_images WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $imageId]);
        } catch (\PDOException $e) {
            error_log("Error in Project::deleteProjectImage - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update image display order
     */
    public function updateImageOrder($imageId, $displayOrder)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $sql = "UPDATE project_images SET display_order = :display_order WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $imageId,
                ':display_order' => $displayOrder
            ]);
        } catch (\PDOException $e) {
            error_log("Error in Project::updateImageOrder - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all published projects (for public view)
     */
    public function getAllPublished($limit = null, $categoryId = null)
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $sql = "SELECT p.*, pc.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN project_categories pc ON p.category_id = pc.id
                    WHERE p.status = 'published'";

            $params = [];

            if ($categoryId) {
                $sql .= " AND p.category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }

            $sql .= " ORDER BY p.is_featured DESC, p.published_at DESC";

            if ($limit) {
                $sql .= " LIMIT :limit";
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($limit) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Project::getAllPublished - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured projects
     */
    public function getFeaturedProjects($limit = 6)
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $sql = "SELECT p.*, pc.name as category_name
                    FROM {$this->table} p
                    LEFT JOIN project_categories pc ON p.category_id = pc.id
                    WHERE p.status = 'published' AND p.is_featured = 1
                    ORDER BY p.published_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Project::getFeaturedProjects - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project categories
     */
    public function getCategories()
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $sql = "SELECT * FROM project_categories ORDER BY name ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Project::getCategories - " . $e->getMessage());
            return [];
        }
    }
}
