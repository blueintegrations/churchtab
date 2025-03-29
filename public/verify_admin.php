<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/models/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        // Create admin if doesn't exist
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $result = $stmt->execute(['admin', $hashedPassword, true]);
        
        if ($result) {
            echo "Admin user created successfully\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
        } else {
            echo "Failed to create admin user\n";
        }
    } else {
        // Update admin password
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
        $result = $stmt->execute([$hashedPassword, 'admin']);
        
        if ($result) {
            echo "Admin password updated successfully\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
        } else {
            echo "Failed to update admin password\n";
        }
    }
    
    // Verify the password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\nStored password hash: " . $user['password'] . "\n";
        $testPassword = 'admin123';
        $verifyResult = password_verify($testPassword, $user['password']);
        echo "Password verification test: " . ($verifyResult ? "SUCCESS" : "FAILED") . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
