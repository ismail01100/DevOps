<?php

class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "db";
    
    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=$this->servername;dbname=$this->dbname",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }
    
    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }
    
    // Get the connection
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning of the instance
    public function __clone() {
        throw new Exception("Cannot clone singleton");
    }
    
    // Prevent unserialization of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

?>