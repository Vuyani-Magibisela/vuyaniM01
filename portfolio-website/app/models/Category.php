<?php

namespace App\Models;

use Exception;

class Category extends BaseModel {
    private $table = 'blog_categories';

    /**
     * Get all categories
     * @return array
     */
    public function getAllCategories() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return $this->query($query);
    }

    /**
     * Get category by ID
     * @param int $id
     * @return object|null
     */
    public function getCategoryById($id) {
        if (!$this->isConnected()) {
            return null;
        }

        $result = parent::getById($this->table, $id);
        return $result ? (object)$result : null;
    }

    /**
     * Get category by slug
     * @param string $slug
     * @return object|null
     */
    public function getCategoryBySlug($slug) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $result = $this->query($query, ['slug' => $slug], false);
        return $result ? (object)$result : null;
    }

    /**
     * Create new category
     * @param array $data
     * @return int|false Category ID or false on failure
     */
    public function createCategory($data) {
        if (!$this->isConnected()) {
            error_log('Category creation attempted without database connection');
            return false;
        }

        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            $query = "INSERT INTO {$this->table} (name, slug, description, created_at)
                      VALUES (:name, :slug, :description, NOW())";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log('Category creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update category
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory($id, $data) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Generate slug if name changed but slug not provided
            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name'], $id);
            }

            $query = "UPDATE {$this->table}
                      SET name = :name,
                          slug = :slug,
                          description = :description,
                          updated_at = NOW()
                      WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null
            ]);
        } catch (Exception $e) {
            error_log('Category update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete category
     * @param int $id
     * @return bool
     */
    public function deleteCategory($id) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Check if category has posts
            $query = "SELECT COUNT(*) as count FROM blog_posts WHERE category_id = :id";
            $result = $this->query($query, ['id' => $id], false);

            if ($result && $result['count'] > 0) {
                error_log("Cannot delete category $id: has {$result['count']} posts");
                return false;
            }

            return parent::delete($this->table, $id);
        } catch (Exception $e) {
            error_log('Category deletion error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate URL-friendly slug from name
     * @param string $name
     * @param int|null $excludeId ID to exclude when checking uniqueness
     * @return string
     */
    public function generateSlug($name, $excludeId = null) {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug); // Remove duplicate hyphens
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
     * @param int|null $excludeId ID to exclude from check
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
     * Get post count for category
     * @param int $id
     * @return int
     */
    public function getPostCount($id) {
        if (!$this->isConnected()) {
            return 0;
        }

        $query = "SELECT COUNT(*) as count FROM blog_posts WHERE category_id = :id AND status = 'published'";
        $result = $this->query($query, ['id' => $id], false);

        return $result ? (int)$result['count'] : 0;
    }
}
