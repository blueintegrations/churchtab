<?php
require_once __DIR__ . '/../../config/database.php';

class Database {
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../../config/database.php';
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
            error_log("Attempting database connection with DSN: $dsn");
            error_log("Using username: {$this->config['username']}");
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            // Ensure utf8mb4 is set for the connection
            $this->pdo->exec("SET CHARACTER SET utf8mb4");
            $this->pdo->exec("SET NAMES utf8mb4");
            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Connection details: host={$this->config['host']}, dbname={$this->config['dbname']}, user={$this->config['username']}");
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($query, $params = []) {
        try {
            error_log("SQL Query: " . $query);
            error_log("Params keys: " . implode(", ", array_keys($params)));
            foreach ($params as $key => $value) {
                if ($key === ':content') {
                    error_log("Content param length: " . strlen($value));
                    error_log("Content param first 100 bytes: " . bin2hex(substr($value, 0, 100)));
                } else {
                    error_log("Param $key: " . $value);
                }
            }
            
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute($params);
            
            if (!$success) {
                error_log("Query failed: " . implode(" ", $stmt->errorInfo()));
                return false;
            }
            
            // Return the statement for SELECT queries
            if (stripos($query, 'SELECT') === 0) {
                return $stmt;
            }
            
            // Return true for successful INSERT/UPDATE/DELETE
            return true;
            
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
