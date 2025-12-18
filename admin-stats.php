<?php
// Centralized admin stats - include this before rendering header/sidebar
// Path: includes/admin-stats.php
// Ensure config is loaded (connects $conn and starts session)
if (!defined('ADMIN_STATS_INCLUDED')) {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    } else {
        // adjust path if config is elsewhere
        require_once __DIR__ . '/../includes/config.php';
    }
    define('ADMIN_STATS_INCLUDED', true);
}

// Make sure session is active (config.php often does this already)
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// If not logged in we don't redirect here — let pages handle auth if needed

// Build stats array (cast to int to avoid string mismatch)
$stats = [];

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM items");
    $stats['total_items'] = (int)$stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'pending'");
    $stats['pending_items'] = (int)$stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'published'");
    $stats['published_items'] = (int)$stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'claimed'");
    $stats['claimed_items'] = (int)$stmt->fetchColumn();

    // Count only active categories (matches original intent)
    $stmt = $conn->query("SELECT COUNT(*) FROM categories WHERE status = 'active'");
    $stats['total_categories'] = (int)$stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
    $stats['unread_messages'] = (int)$stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    // Fail-safe values if DB queries error
    $stats = array_merge([
        'total_items' => 0,
        'pending_items' => 0,
        'published_items' => 0,
        'claimed_items' => 0,
        'total_categories' => 0,
        'unread_messages' => 0,
        'total_users' => 0,
    ], $stats ?? []);
}

// Make available for includes that render the sidebar/header
$GLOBALS['stats'] = $stats;
?>