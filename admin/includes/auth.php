<?php
session_start();

// Include database connection
require_once('db_connect.php');

// Check if functions are already declared
if (!function_exists('checkAdminAuth')) {
    function checkAdminAuth() {
        if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('adminLogin')) {
    function adminLogin($username, $password) {
        global $conn;
        
        try {
            $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ? AND is_admin = 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['admin_loggedin'] = true;
                    $_SESSION['admin_id'] = $user['user_id'];
                    $_SESSION['admin_username'] = $user['username'];
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
}
?>