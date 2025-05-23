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
    
    public function getById($id) {
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
        return $this->getById($this->table, $id);
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
}