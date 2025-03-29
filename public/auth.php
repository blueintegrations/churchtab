<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log
error_log("Auth.php accessed");

require_once __DIR__ . '/../src/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    
    $user = new User();
    
    if (isset($_POST['action'])) {
        error_log("Action: " . $_POST['action']);
        
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            error_log("Login attempt - Username: " . $username);
            
            if ($user->login($username, $password)) {
                error_log("Login successful");
                header('Location: index.php');
                exit;
            } else {
                error_log("Login failed");
                $_SESSION['error'] = 'Invalid username or password';
                header('Location: index.php?action=login');
                exit;
            }
        } else if ($_POST['action'] === 'register') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            error_log("Register attempt - Username: " . $username);
            
            if ($password !== $confirm_password) {
                error_log("Registration failed - Passwords do not match");
                $_SESSION['error'] = 'Passwords do not match';
                header('Location: index.php?action=register');
                exit;
            }
            
            if ($user->register($username, $password)) {
                error_log("Registration successful");
                $_SESSION['success'] = 'Registration successful. Please login.';
                header('Location: index.php?action=login');
                exit;
            } else {
                error_log("Registration failed");
                $_SESSION['error'] = 'Registration failed. Username might be taken.';
                header('Location: index.php?action=register');
                exit;
            }
        }
    } else {
        error_log("No action specified in POST data");
    }
}

error_log("No valid POST request, redirecting to login");
header('Location: index.php?action=login');
exit;
