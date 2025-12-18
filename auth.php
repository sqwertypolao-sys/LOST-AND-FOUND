<?php
// Authentication functions

function requireLogin() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if($_SESSION['user_role'] != 'admin') {
        header("Location: dashboard.php");
        exit();
    }
}

function requireStaff() {
    requireLogin();
    if(!in_array($_SESSION['user_role'], ['admin', 'staff'])) {
        header("Location: index.php");
        exit();
    }
}

function checkPermission($required_role) {
    if(!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if($required_role == 'admin') {
        return $_SESSION['user_role'] == 'admin';
    } elseif($required_role == 'staff') {
        return in_array($_SESSION['user_role'], ['admin', 'staff']);
    }
    
    return false;
}

function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
?>