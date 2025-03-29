<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($username, $password, $isAdmin = false) {
        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = :username";
        $params = [':username' => $username];
        $result = $this->db->query($query, $params)->fetch();
        
        if ($result) {
            return false; // Username already exists
        }

        // Hash password and create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, is_admin) VALUES (:username, :password, :is_admin)";
        $params = [
            ':username' => $username,
            ':password' => $hashedPassword,
            ':is_admin' => $isAdmin ? 1 : 0
        ];
        
        return $this->db->query($query, $params) !== false;
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username";
        $params = [':username' => $username];
        $user = $this->db->query($query, $params)->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            return true;
        }
        return false;
    }

    public function getAllUsers() {
        $query = "SELECT id, username, is_admin, created_at FROM users ORDER BY username";
        return $this->db->query($query)->fetchAll();
    }

    public function toggleAdmin($userId) {
        $query = "UPDATE users SET is_admin = NOT is_admin WHERE id = :id";
        $params = [':id' => $userId];
        return $this->db->query($query, $params);
    }
}
