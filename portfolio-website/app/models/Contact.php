<?php

namespace App\Models;

use PDO;
use Exception;

class Contact extends BaseModel {
    private $table = 'contact_submissions';
    
    public function saveSubmission($data) {
        // Check if we're using dummy data (no database connection)
        if (!$this->isConnected()) {
            // In a real scenario, you might log this to a file
            error_log('Contact form submission (no DB): ' . json_encode($data));
            return true; // Simulate success
        }
        
        try {
            $query = "INSERT INTO {$this->table} (name, email, subject, message, ip_address, created_at) 
                      VALUES (:name, :email, :subject, :message, :ip_address, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'ip_address' => $data['ip_address']
            ]);
            
            return $result;
        } catch (Exception $e) {
            error_log('Contact form submission error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getAllSubmissions($limit = 50, $offset = 0) {
        // Check if we're using dummy data
        if (!$this->isConnected()) {
            return []; // Return empty array if no database
        }
        
        $query = "SELECT * FROM {$this->table} 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        return $this->query($query, ['limit' => $limit, 'offset' => $offset]);
    }
    
    public function getSubmissionById($id) {
        // Check if we're using dummy data
        if (!$this->isConnected()) {
            return null;
        }
        
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->query($query, ['id' => $id], false);
    }
    
    public function markAsRead($id) {
        // Check if we're using dummy data
        if (!$this->isConnected()) {
            return true;
        }
        
        $query = "UPDATE {$this->table} SET is_read = 1 WHERE id = :id";
        return $this->query($query, ['id' => $id], false);
    }
    
    public function getUnreadCount() {
        // Check if we're using dummy data
        if (!$this->isConnected()) {
            return 0;
        }
        
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_read = 0";
        $result = $this->query($query, [], false);
        return $result ? $result->count : 0;
    }
    
    public function deleteSubmission($id) {
        // Check if we're using dummy data
        if (!$this->isConnected()) {
            return true;
        }
        
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->query($query, ['id' => $id], false);
    }
}