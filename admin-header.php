<?php
// admin-header.php

ini_set('memory_limit', '1024M'); // temporary increase

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Only fetch minimal user info (avoid storing entire session large objects)
$stmt = $conn->prepare("SELECT id, username, fullname, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch stats directly without storing in session
$stats = [
    'pending_items' => $conn->query("SELECT COUNT(*) FROM items WHERE status='pending'")->fetchColumn(),
    'unread_messages' => $conn->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn(),
    'total_categories' => $conn->query("SELECT COUNT(*) FROM categories WHERE status='active'")->fetchColumn(),
    'total_items' => $conn->query("SELECT COUNT(*) FROM items")->fetchColumn(),
    'total_users' => $_SESSION['user_role'] === 'admin' ? $conn->query("SELECT COUNT(*) FROM users")->fetchColumn() : 0
];

$pending_items = $stats['pending_items'];
$unread_messages = $stats['unread_messages'];
$total_categories = $stats['total_categories'];
$total_items = $stats['total_items'];
$total_users = $stats['total_users'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - LFIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary: #F57C00;
            --primary-dark: #E65100;
            --dark-gray: #2E2E2E;
            --light-gray: #EEEEEE;
            --white: #FFFFFF;
        }
        
        /* Add some memory optimization styles */
        * {
            box-sizing: border-box;
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="sidebar-header">
                <h4><i class="bi bi-search-heart"></i> LFIS Admin</h4>
                <p class="text-muted">Management Panel</p>
            </div>
            
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                
                <div class="sidebar-section">
                    <small class="text-muted">MANAGEMENT</small>
                </div>
                
                <a href="categories.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i> Categories
                    <span class="badge bg-primary float-end"><?php echo $total_categories; ?></span>
                </a>
                
                <a href="items.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'items.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box"></i> Items
                    <span class="badge bg-primary float-end"><?php echo $total_items; ?></span>
                </a>
                
                <a href="messages.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                    <i class="bi bi-envelope"></i> Messages
                    <?php if($unread_messages > 0): ?>
                        <span class="badge bg-danger float-end"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <div class="sidebar-section mt-3">
                        <small class="text-muted">ADMINISTRATION</small>
                    </div>
                    
                    <a href="users.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i> Users
                        <span class="badge bg-primary float-end"><?php echo $total_users; ?></span>
                    </a>
                    
                    <a href="pages.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'pages.php' ? 'active' : ''; ?>">
                        <i class="bi bi-file-text"></i> Pages
                    </a>
                    
                    <a href="system.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'system.php' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i> System
                    </a>
                <?php endif; ?>
                
                <div class="sidebar-section mt-3">
                    <small class="text-muted">ACCOUNT</small>
                </div>
                
                <a href="profile.php" class="nav-link <?php echo isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
                
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </nav>
            
            <div class="sidebar-footer mt-auto">
                <div class="user-info">
                    <div class="d-flex align-items-center">
                        <div>
                            <strong><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'User'; ?></strong>
                            <small class="d-block text-muted"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'User'; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Top Navigation Bar will be included in individual pages -->