<?php
/**
 * Database Class
 * PDO database connection and operations
 */

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;
    private $options = DB_OPTIONS;
    
    private $pdo;
    private $stmt;
    private $error;
    
    /**
     * Constructor - Creates a PDO connection when the class is instantiated
     */
    public function __construct() {
        // Set DSN
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        // Create PDO instance
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $this->options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            die("Database connection failed: " . $this->error);
        }
    }
    
    /**
     * Prepare statement with query
     * @param string $sql The SQL query to prepare
     * @return void
     */
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }
    
    /**
     * Bind values to prepared statement using named parameters
     * @param string $param Parameter name
     * @param mixed $value Parameter value
     * @param mixed $type Parameter type if explicitly defined
     * @return void
     */
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * Execute the prepared statement
     * @return bool
     */
    public function execute() {
        return $this->stmt->execute();
    }
    
    /**
     * Get result set as array of objects
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    /**
     * Get single record as object
     * @return object
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * Get row count
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get the last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * End a transaction and commit
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Cancel a transaction (rollback)
     * @return bool
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Debug - dump the parameters
     * @return array
     */
    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }
}