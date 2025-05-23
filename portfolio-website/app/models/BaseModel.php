<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Base Model
 * Contains common database operations for all models
 */
abstract class BaseModel {
    /**
     * Database connection instance
     * @var PDO
     */
    protected $db;
    
    /**
     * Flag indicating if database is connected
     * @var bool
     */
    protected $dbConnected = false;

    /**
     * Constructor
     * Initializes the database connection
     */
    public function __construct() {
        try {
            $this->db = \App\Core\Database::connect();
            $this->dbConnected = true;
        } catch (PDOException $e) {
            // Handle database connection error
            $this->dbConnected = false;
            error_log('Database connection error: ' . $e->getMessage());
            
            // We don't rethrow the exception here to allow the application to continue
            // Models should check $this->dbConnected before performing database operations
        }
    }
    
    /**
     * Check if database is connected
     * @return bool True if connected, false otherwise
     */
    protected function isConnected() {
        return $this->dbConnected;
    }

    /**
     * Get all records from a table
     * @param string $table Table name
     * @param string $orderBy Column to order by
     * @param string $order Order direction (ASC or DESC)
     * @return array Array of records
     */
    protected function getAll($table, $orderBy = 'id', $order = 'ASC') {
        if (!$this->dbConnected) {
            return [];
        }
        
        $query = "SELECT * FROM {$table} ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single record by ID
     * @param string $table Table name
     * @param int $id Record ID
     * @return array|bool Record array or false if not found
     */
    protected function getById($table, $id) {
        if (!$this->dbConnected) {
            return false;
        }
        
        $query = "SELECT * FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get records by a specific field value
     * @param string $table Table name
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $orderBy Column to order by
     * @param string $order Order direction (ASC or DESC)
     * @return array Array of records
     */
    protected function getByField($table, $field, $value, $orderBy = 'id', $order = 'ASC') {
        if (!$this->dbConnected) {
            return [];
        }
        
        $query = "SELECT * FROM {$table} WHERE {$field} = :value ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':value', $value);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single record by a specific field value
     * @param string $table Table name
     * @param string $field Field name
     * @param mixed $value Field value
     * @return array|bool Record array or false if not found
     */
    protected function getSingleByField($table, $field, $value) {
        if (!$this->dbConnected) {
            return false;
        }
        
        $query = "SELECT * FROM {$table} WHERE {$field} = :value LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':value', $value);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new record
     * @param string $table Table name
     * @param array $data Associative array of field => value
     * @return int|bool Last insert ID or false on failure
     */
    protected function create($table, $data) {
        if (!$this->dbConnected) {
            return false;
        }
        
        // Get field names and placeholders
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        // Build the query
        $fieldString = implode(', ', $fields);
        $placeholderString = implode(', ', $placeholders);
        $query = "INSERT INTO {$table} ({$fieldString}) VALUES ({$placeholderString})";
        
        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update an existing record
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $data Associative array of field => value
     * @return bool True on success, false on failure
     */
    protected function update($table, $id, $data) {
        if (!$this->dbConnected) {
            return false;
        }
        
        // Get field names and placeholders
        $setStatements = array_map(function($field) {
            return "{$field} = :{$field}";
        }, array_keys($data));
        
        // Build the query
        $setString = implode(', ', $setStatements);
        $query = "UPDATE {$table} SET {$setString} WHERE id = :id";
        
        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Delete a record
     * @param string $table Table name
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    protected function delete($table, $id) {
        if (!$this->dbConnected) {
            return false;
        }
        
        $query = "DELETE FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Count the number of records in a table
     * @param string $table Table name
     * @param string $whereClause Optional WHERE clause
     * @param array $params Optional parameters for WHERE clause
     * @return int Number of records
     */
    protected function count($table, $whereClause = '', $params = []) {
        if (!$this->dbConnected) {
            return 0;
        }
        
        $query = "SELECT COUNT(*) as count FROM {$table}";
        
        if (!empty($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }

    /**
     * Execute a custom query
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @param bool $fetchAll Whether to fetch all results or just one
     * @return mixed Query results
     */
    protected function query($query, $params = [], $fetchAll = true) {
        if (!$this->dbConnected) {
            return $fetchAll ? [] : false;
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value);
        }
        
        $stmt->execute();
        
        if ($fetchAll) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Begin a transaction
     * @return bool True on success, false on failure
     */
    protected function beginTransaction() {
        if (!$this->dbConnected) {
            return false;
        }
        
        return $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     * @return bool True on success, false on failure
     */
    protected function commit() {
        if (!$this->dbConnected) {
            return false;
        }
        
        return $this->db->commit();
    }

    /**
     * Roll back a transaction
     * @return bool True on success, false on failure
     */
    protected function rollBack() {
        if (!$this->dbConnected) {
            return false;
        }
        
        return $this->db->rollBack();
    }

    /**
     * Get the last inserted ID
     * @return string The last inserted ID
     */
    protected function lastInsertId() {
        if (!$this->dbConnected) {
            return null;
        }
        
        return $this->db->lastInsertId();
    }

    /**
     * Get database error info
     * @return array Database error info
     */
    protected function errorInfo() {
        if (!$this->dbConnected) {
            return ['Database not connected'];
        }
        
        return $this->db->errorInfo();
    }
}