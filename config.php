<?php
session_start();
ob_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lfis_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('BASE_URL', 'http://localhost/lost-and-found');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Color Scheme
define('PRIMARY_COLOR', '#F57C00');
define('DARK_GRAY', '#2E2E2E');
define('LIGHT_GRAY', '#EEEEEE');
define('WHITE', '#FFFFFF');

// Database Connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include Functions
require_once 'functions.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Check if user is staff
function isStaff() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'staff' || $_SESSION['user_role'] == 'admin');
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get system setting
function getSetting($key, $conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result ? $result : '';
}

// Get page content
function getPageContent($page_name, $conn) {
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_name = ?");
    $stmt->execute([$page_name]);
    return $stmt->fetch();
}

// Check and create upload directories
if (!file_exists(UPLOAD_PATH . 'items/')) {
    mkdir(UPLOAD_PATH . 'items/', 0777, true);
}
if (!file_exists(UPLOAD_PATH . 'temp/')) {
    mkdir(UPLOAD_PATH . 'temp/', 0777, true);
}
?>