<?php

namespace App\Models;

use PDO;

class Resource extends BaseModel {
    private $table = 'resources';
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
                    'resources' => []
                ];
                error_log('Resource dummy data file not found: ' . $dummyDataPath);
            }
        }
    }
    
    public function getAllPublished() {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            return isset($this->dummyData['resources']) ? $this->dummyData['resources'] : [];
        }
        
        // Use database if available
        $query = "SELECT r.*, u.username as author_name
                  FROM {$this->table} r
                  LEFT JOIN users u ON r.author_id = u.id
                  WHERE r.status = 'published'
                  ORDER BY r.created_at DESC";
                  
        return $this->query($query);
    }
    
    public function findById($id) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            foreach ($this->dummyData['resources'] as $resource) {
                if ($resource['id'] == $id) {
                    return $resource;
                }
            }
            return null;
        }

        // Use database if available
        return parent::getById($this->table, $id);
    }
    
    public function incrementDownloads($id) {
        // Check if we're using dummy data
        if (isset($this->dummyData)) {
            foreach ($this->dummyData['resources'] as &$resource) {
                if ($resource['id'] == $id) {
                    $resource['download_count']++;
                    break;
                }
            }
            return true;
        }

        // Use database if available
        $query = "UPDATE {$this->table} SET download_count = download_count + 1 WHERE id = :id";
        return $this->query($query, ['id' => $id], false);
    }

    /**
     * Get all resources with optional filters and pagination (Admin)
     */
    public function getAllResources($status = null, $search = null, $limit = 20, $offset = 0)
    {
        if (!$this->isConnected()) {
            error_log("Database not connected in Resource::getAllResources");
            return [];
        }

        try {
            $sql = "SELECT r.*,
                    u.username as author_name
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.author_id = u.id
                    WHERE 1=1";

            $params = [];

            if ($status) {
                $sql .= " AND r.status = :status";
                $params[':status'] = $status;
            }

            if ($search) {
                $sql .= " AND (r.title LIKE :search OR r.description LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Resource::getAllResources - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of resources with filters
     */
    public function getResourceCount($status = null, $search = null)
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

            if ($search) {
                $sql .= " AND (title LIKE :search OR description LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error in Resource::getResourceCount - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get resource by ID (Admin - includes drafts)
     */
    public function getResourceById($id)
    {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $sql = "SELECT r.*,
                    u.username as author_name
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.author_id = u.id
                    WHERE r.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Resource::getResourceById - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get resource by slug
     */
    public function getResourceBySlug($slug)
    {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $sql = "SELECT r.*,
                    u.username as author_name
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.author_id = u.id
                    WHERE r.slug = :slug AND r.status = 'published'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':slug' => $slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Resource::getResourceBySlug - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new resource
     */
    public function createResource($data)
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

            $sql = "INSERT INTO {$this->table}
                    (title, slug, description, file_path, file_size, file_type,
                     thumbnail, download_count, requires_login, author_id, status, created_at, updated_at)
                    VALUES
                    (:title, :slug, :description, :file_path, :file_size, :file_type,
                     :thumbnail, 0, :requires_login, :author_id, :status, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':file_path' => $data['file_path'],
                ':file_size' => $data['file_size'] ?? 0,
                ':file_type' => $data['file_type'] ?? null,
                ':thumbnail' => $data['thumbnail'] ?? null,
                ':requires_login' => $data['requires_login'] ?? 0,
                ':author_id' => $data['author_id'],
                ':status' => $data['status'] ?? 'draft'
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (\PDOException $e) {
            error_log("Error in Resource::createResource - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing resource
     */
    public function updateResource($id, $data)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Get current resource
            $currentResource = $this->getResourceById($id);
            if (!$currentResource) {
                return false;
            }

            // Generate slug if changed
            if (!empty($data['slug']) && $data['slug'] !== $currentResource['slug']) {
                $data['slug'] = $this->generateSlug($data['slug'], $id);
            } elseif (!empty($data['title']) && $data['title'] !== $currentResource['title']) {
                $data['slug'] = $this->generateSlug($data['title'], $id);
            }

            $sql = "UPDATE {$this->table} SET
                    title = :title,
                    slug = :slug,
                    description = :description,
                    file_path = :file_path,
                    file_size = :file_size,
                    file_type = :file_type,
                    thumbnail = :thumbnail,
                    requires_login = :requires_login,
                    status = :status,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'] ?? $currentResource['title'],
                ':slug' => $data['slug'] ?? $currentResource['slug'],
                ':description' => $data['description'] ?? $currentResource['description'],
                ':file_path' => $data['file_path'] ?? $currentResource['file_path'],
                ':file_size' => $data['file_size'] ?? $currentResource['file_size'],
                ':file_type' => $data['file_type'] ?? $currentResource['file_type'],
                ':thumbnail' => $data['thumbnail'] ?? $currentResource['thumbnail'],
                ':requires_login' => $data['requires_login'] ?? $currentResource['requires_login'],
                ':status' => $data['status'] ?? $currentResource['status']
            ]);
        } catch (\PDOException $e) {
            error_log("Error in Resource::updateResource - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete resource
     */
    public function deleteResource($id)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error in Resource::deleteResource - " . $e->getMessage());
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
            error_log("Error in Resource::slugExists - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format file size for display
     */
    public function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Validate file type
     */
    public function validateFileType($mimeType)
    {
        $allowedTypes = [
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',

            // Archives
            'application/zip',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/gzip',

            // Code/Data
            'application/json',
            'text/csv',
            'application/sql',
            'text/x-php',
            'text/javascript',
            'text/html',
            'text/css',

            // Images (for thumbnails/resources)
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Get file icon based on type
     */
    public function getFileIcon($fileType)
    {
        $iconMap = [
            'application/pdf' => 'fa-file-pdf',
            'application/msword' => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            'application/zip' => 'fa-file-archive',
            'application/x-rar-compressed' => 'fa-file-archive',
            'application/x-tar' => 'fa-file-archive',
            'text/plain' => 'fa-file-alt',
            'application/json' => 'fa-file-code',
            'text/csv' => 'fa-file-csv',
            'image/jpeg' => 'fa-file-image',
            'image/png' => 'fa-file-image',
        ];

        return $iconMap[$fileType] ?? 'fa-file';
    }
}