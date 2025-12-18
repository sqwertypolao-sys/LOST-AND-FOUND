<?php
require_once '../includes/config.php';
include __DIR__ . '/../includes/admin-header.php';
if(!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('index.php');
}

// Get all pages
$stmt = $conn->query("SELECT * FROM pages ORDER BY page_name");
$pages = $stmt->fetchAll();

// Handle page edit
if(isset($_GET['edit'])) {
    $page_name = sanitize($_GET['edit']);
    
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = sanitize($_POST['title']);
        $content = $_POST['content']; // Don't sanitize HTML content
        $meta_description = sanitize($_POST['meta_description']);
        
        $stmt = $conn->prepare("UPDATE pages SET title = ?, content = ?, meta_description = ? WHERE page_name = ?");
        if($stmt->execute([$title, $content, $meta_description, $page_name])) {
            $success = "Page updated successfully!";
        } else {
            $error = "Failed to update page.";
        }
    }
    
    // Get page details
    $stmt = $conn->prepare("SELECT * FROM pages WHERE page_name = ?");
    $stmt->execute([$page_name]);
    $page = $stmt->fetch();
    
    if(!$page) {
        redirect('pages.php');
    }
    
    include 'page-edit.php';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <span class="navbar-brand">Manage Pages</span>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Title</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pages as $page): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo ucfirst($page['page_name']); ?> Page</strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($page['title']); ?></td>
                                        <td><?php echo formatDate($page['updated_at']); ?></td>
                                        <td>
                                            <a href="pages.php?edit=<?php echo $page['page_name']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="../<?php echo $page['page_name']; ?>.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>