<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/admin-stats.php';

// Check if user is logged in (admin-stats already does this, but keep explicit check if needed)
if(!isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Recent items
$stmt = $conn->query("SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.created_at DESC LIMIT 5");
$recent_items = $stmt->fetchAll();

// Recent messages
$stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
$recent_messages = $stmt->fetchAll();

// Recent users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Use $stats from includes/admin-stats.php
$stats = $GLOBALS['stats'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    
                    <div class="sidebar-section">
                        <small class="text-muted">MANAGEMENT</small>
                    </div>
                    
                    <a href="categories.php" class="nav-link">
                        <i class="bi bi-tags"></i> Categories
                        <span class="badge bg-primary float-end"><?php echo $stats['total_categories']; ?></span>
                    </a>
                    
                    <a href="items.php" class="nav-link">
                        <i class="bi bi-box"></i> Items
                        <span class="badge bg-primary float-end"><?php echo $stats['total_items']; ?></span>
                    </a>
                    
                    <a href="messages.php" class="nav-link">
                        <i class="bi bi-envelope"></i> Messages
                        <?php if($stats['unread_messages'] > 0): ?>
                            <span class="badge bg-danger float-end"><?php echo $stats['unread_messages']; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if(isAdmin()): ?>
                        <div class="sidebar-section mt-3">
                            <small class="text-muted">ADMINISTRATION</small>
                        </div>
                        
                        <a href="users.php" class="nav-link">
                            <i class="bi bi-people"></i> Users
                            <span class="badge bg-primary float-end"><?php echo $stats['total_users']; ?></span>
                        </a>
                        
                        <a href="pages.php" class="nav-link">
                            <i class="bi bi-file-text"></i> Pages
                        </a>
                        
                        <a href="system.php" class="nav-link">
                            <i class="bi bi-gear"></i> System
                        </a>
                    <?php endif; ?>
                    
                    <div class="sidebar-section mt-3">
                        <small class="text-muted">ACCOUNT</small>
                    </div>
                    
                    <a href="profile.php" class="nav-link">
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
                                <strong><?php echo $_SESSION['fullname']; ?></strong>
                                <small class="d-block text-muted"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Bar -->
                <nav class="navbar navbar-light bg-white border-bottom sticky-top">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Dashboard</span>
                        <div class="d-flex align-items-center">
                            <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-eye"></i> View Site
                            </a>
                            <span class="text-muted me-3">Welcome, <?php echo $_SESSION['fullname']; ?></span>
                        </div>
                    </div>
                </nav>
                
                <!-- Main Content -->
                <div class="container-fluid mt-4">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Total Items</h6>
                                            <h2 class="card-title"><?php echo $stats['total_items']; ?></h2>
                                        </div>
                                        <i class="bi bi-box stat-icon"></i>
                                    </div>
                                    <div class="mt-2">
                                        <small>Published: <?php echo $stats['published_items']; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Pending Items</h6>
                                            <h2 class="card-title"><?php echo $stats['pending_items']; ?></h2>
                                        </div>
                                        <i class="bi bi-clock stat-icon"></i>
                                    </div>
                                    <div class="mt-2">
                                        <small>Awaiting approval</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Claimed Items</h6>
                                            <h2 class="card-title"><?php echo $stats['claimed_items']; ?></h2>
                                        </div>
                                        <i class="bi bi-check-circle stat-icon"></i>
                                    </div>
                                    <div class="mt-2">
                                        <small>Reunited with owners</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2">Unread Messages</h6>
                                            <h2 class="card-title"><?php echo $stats['unread_messages']; ?></h2>
                                        </div>
                                        <i class="bi bi-envelope stat-icon"></i>
                                    </div>
                                    <div class="mt-2">
                                        <small>Need attention</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity (same as your original content) -->
                    <div class="row">
                        <!-- Recent Items -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Items</h5>
                                    <a href="items.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <a href="items.php?action=view&id=<?php echo $item['id']; ?>" class="text-decoration-none">
                                                            <?php echo substr($item['title'], 0, 30); ?>...
                                                        </a>
                                                    </td>
                                                    <td><?php echo $item['category_name']; ?></td>
                                                    <td><?php echo getStatusBadge($item['status']); ?></td>
                                                    <td><?php echo formatDate($item['created_at']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Messages -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Messages</h5>
                                    <a href="messages.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_messages as $msg): ?>
                                                <tr class="<?php echo !$msg['is_read'] ? 'table-warning' : ''; ?>">
                                                    <td><?php echo $msg['fullname']; ?></td>
                                                    <td><?php echo $msg['email']; ?></td>
                                                    <td><?php echo formatDate($msg['created_at']); ?></td>
                                                    <td>
                                                        <?php if($msg['is_read']): ?>
                                                            <span class="badge bg-success">Read</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Unread</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isAdmin()): ?>
                    <!-- Recent Users (Admin only) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Users</h5>
                                    <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Full Name</th>
                                                    <th>Role</th>
                                                    <th>Email</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_users as $user): ?>
                                                <tr>
                                                    <td><?php echo $user['username']; ?></td>
                                                    <td><?php echo $user['fullname']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                            <?php echo ucfirst($user['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $user['email']; ?></td>
                                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>