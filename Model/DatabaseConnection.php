<?php
require_once __DIR__ . '/../Config/config.php';

class DatabaseConnection {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $config = DatabaseConfig::getConfig();
        try {
            $this->conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']}",
                $config['user'],
                $config['password']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
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