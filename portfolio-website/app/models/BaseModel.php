<?php
/**
 * Base Model
 * Core database operations for all models
 */

class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all records from table
     * @param array $options Additional options (orderBy, limit, etc.)
     * @return array All records
     */
    public function getAll($options = []) {
        // Build query
        $sql = "SELECT * FROM {$this->table}";
        
        // Add order by clause if specified
        if (isset($options['orderBy'])) {
            $sql .= " ORDER BY {$options['orderBy']}";
            
            // Add direction if specified
            if (isset($options['direction'])) {
                $sql .= " {$options['direction']}";
            }
        }
        
        // Add limit clause if specified
        if (isset($options['limit'])) {
            $sql .= " LIMIT {$options['limit']}";
            
            // Add offset if specified
            if (isset($options['offset'])) {
                $sql .= " OFFSET {$options['offset']}";
            }
        }
        
        // Prepare and execute query
        $this->db->query($sql);
        return $this->db->resultSet();
    }
    
    /**
     * Get record by ID
     * @param int $id The record ID
     * @return array The record
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Get records by condition
     * @param string $column Column name
     * @param mixed $value Column value
     * @param string $operator Comparison operator
     * @return array Matching records
     */
    public function getBy($column, $value, $operator = '=') {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$column} {$operator} :value");
        $this->db->bind(':value', $value);
        return $this->db->resultSet();
    }
    
    /**
     * Get single record by condition
     * @param string $column Column name
     * @param mixed $value Column value
     * @param string $operator Comparison operator
     * @return array|false The record or false if not found
     */
    public function getSingleBy($column, $value, $operator = '=') {
        $this->db->query("SELECT * FROM {$this->table} WHERE {$column} {$operator} :value");
        $this->db->bind(':value', $value);
        return $this->db->single();
    }
    
    /**
     * Create new record
     * @param array $data Record data
     * @return int|bool Last insert ID or false
     */
    public function create($data) {
        // Filter data to only include fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Build query
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $this->db->query("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        
        // Bind values
        foreach ($filteredData as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update record
     * @param int $id Record ID
     * @param array $data Record data
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Filter data to only include fillable fields
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Build SET clause
        $setClause = '';
        foreach (array_keys($filteredData) as $key) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ', ');
        
        // Build query
        $this->db->query("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id");
        
        // Bind values
        $this->db->bind(':id', $id);
        foreach ($filteredData as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        // Execute
        return $this->db->execute();
    }
    
    /**
     * Delete record
     * @param int $id Record ID
     * @return bool Success or failure
     */
    public function delete($id) {
        $this->db->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    /**
     * Count records
     * @param string $column Column for condition (optional)
     * @param mixed $value Value for condition (optional)
     * @return int Number of records
     */
    public function count($column = null, $value = null) {
        if ($column === null) {
            $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        } else {
            $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE {$column} = :value");
            $this->db->bind(':value', $value);
        }
        
        $result = $this->db->single();
        return $result['count'];
    }
    
    /**
     * Check if record exists
     * @param string $column Column for condition
     * @param mixed $value Value for condition
     * @return bool Whether record exists
     */
    public function exists($column, $value) {
        return $this->count($column, $value) > 0;
    }
    
    /**
     * Run custom SQL query
     * @param string $sql SQL query
     * @param array $params Parameters for binding
     * @param bool $single Whether to return single record
     * @return array|mixed Query results
     */
    public function query($sql, $params = [], $single = false) {
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        // Execute and return
        if ($single) {
            return $this->db->single();
        } else {
            return $this->db->resultSet();
        }
    }
}