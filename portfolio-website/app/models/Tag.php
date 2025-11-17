<?php

namespace App\Models;

use Exception;

class Tag extends BaseModel {
    private $table = 'tags';

    /**
     * Get all tags
     * @return array
     */
    public function getAllTags() {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return $this->query($query);
    }

    /**
     * Get tag by ID
     * @param int $id
     * @return object|null
     */
    public function getTagById($id) {
        if (!$this->isConnected()) {
            return null;
        }

        $result = parent::getById($this->table, $id);
        return $result ? (object)$result : null;
    }

    /**
     * Get tag by slug
     * @param string $slug
     * @return object|null
     */
    public function getTagBySlug($slug) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $result = $this->query($query, ['slug' => $slug], false);
        return $result ? (object)$result : null;
    }

    /**
     * Get tag by name
     * @param string $name
     * @return object|null
     */
    public function getTagByName($name) {
        if (!$this->isConnected()) {
            return null;
        }

        $query = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        $result = $this->query($query, ['name' => $name], false);
        return $result ? (object)$result : null;
    }

    /**
     * Create new tag
     * @param string $name
     * @return int|false Tag ID or false on failure
     */
    public function createTag($name) {
        if (!$this->isConnected()) {
            error_log('Tag creation attempted without database connection');
            return false;
        }

        try {
            $slug = $this->generateSlug($name);

            $query = "INSERT INTO {$this->table} (name, slug, created_at)
                      VALUES (:name, :slug, NOW())";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'name' => trim($name),
                'slug' => $slug
            ]);

            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log('Tag creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find existing tags or create new ones
     * @param array $tagNames Array of tag names
     * @return array Array of tag IDs
     */
    public function findOrCreateTags($tagNames) {
        if (!$this->isConnected()) {
            return [];
        }

        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);

            if (empty($tagName)) {
                continue;
            }

            // Try to find existing tag
            $existingTag = $this->getTagByName($tagName);

            if ($existingTag) {
                $tagIds[] = $existingTag->id;
            } else {
                // Create new tag
                $newTagId = $this->createTag($tagName);
                if ($newTagId) {
                    $tagIds[] = $newTagId;
                }
            }
        }

        return $tagIds;
    }

    /**
     * Delete tag
     * @param int $id
     * @return bool
     */
    public function deleteTag($id) {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Note: blog_post_tags has ON DELETE CASCADE, so related entries will be deleted automatically
            return parent::delete($this->table, $id);
        } catch (Exception $e) {
            error_log('Tag deletion error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate URL-friendly slug from name
     * @param string $name
     * @param int|null $excludeId ID to exclude when checking uniqueness
     * @return string
     */
    private function generateSlug($name, $excludeId = null) {
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
     * Search tags by name (for autocomplete)
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function searchTags($searchTerm, $limit = 10) {
        if (!$this->isConnected()) {
            return [];
        }

        $query = "SELECT * FROM {$this->table}
                  WHERE name LIKE :search
                  ORDER BY name ASC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':search', '%' . $searchTerm . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get usage count for tag (how many posts use it)
     * @param int $id
     * @return int
     */
    public function getUsageCount($id) {
        if (!$this->isConnected()) {
            return 0;
        }

        $query = "SELECT COUNT(*) as count
                  FROM blog_post_tags
                  WHERE tag_id = :id";

        $result = $this->query($query, ['id' => $id], false);
        return $result ? (int)$result['count'] : 0;
    }
}
