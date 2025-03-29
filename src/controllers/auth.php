<?php
session_start();
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($user->login($username, $password)) {
                header('Location: /index.php');
                exit;
            } else {
                $_SESSION['error'] = 'Invalid username or password';
                header('Location: /index.php?action=login');
                exit;
            }
        }
    }
}

header('Location: /index.php?action=login');
exit;
