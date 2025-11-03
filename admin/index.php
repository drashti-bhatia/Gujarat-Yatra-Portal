<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>