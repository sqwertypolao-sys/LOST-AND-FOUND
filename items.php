<?php
require_once '../includes/config.php';

// Check authentication
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle item actions FIRST
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Handle different actions BEFORE any other code
switch($action) {
    case 'add':
        // Include add form
        include 'item-add.php';
        exit();
        
    case 'edit':
        // Include edit form
        include 'item-edit.php';
        exit();
        
    case 'view':
        // Include view page
        include 'item-view.php';
        exit();
        
    case 'delete':
        // Handle delete
        if(isAdmin() && $id > 0) {
            // Get item to delete image
            $stmt = $conn->prepare("SELECT image_url FROM items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            
            if($item && $item['image_url']) {
                // Delete image file
                $image_path = '../uploads/items/' . $item['image_url'];
                if(file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
            if($stmt->execute([$id])) {
                header("Location: items.php?success=Item deleted successfully");
            } else {
                header("Location: items.php?error=Failed to delete item");
            }
            exit();
        }
        break;
        
    case 'status':
        // Update status
        if($id > 0 && in_array($status, ['pending', 'published', 'claimed'])) {
            $stmt = $conn->prepare("UPDATE items SET status = ? WHERE id = ?");
            if($stmt->execute([$status, $id])) {
                header("Location: items.php?success=Status updated successfully");
            } else {
                header("Location: items.php?error=Failed to update status");
            }
            exit();
        }
        break;
        
    case 'publish':
        // Quick publish action
        if($id > 0) {
            $stmt = $conn->prepare("UPDATE items SET status = 'published' WHERE id = ?");
            if($stmt->execute([$id])) {
                header("Location: items.php?success=Item published successfully");
            } else {
                header("Location: items.php?error=Failed to publish item");
            }
            exit();
        }
        break;
        
    case 'unpublish':
        // Quick unpublish action
        if($id > 0) {
            $stmt = $conn->prepare("UPDATE items SET status = 'pending' WHERE id = ?");
            if($stmt->execute([$id])) {
                header("Location: items.php?success=Item unpublished successfully");
            } else {
                header("Location: items.php?error=Failed to unpublish item");
            }
            exit();
        }
        break;
}

// AFTER handling actions, continue with normal listing
// This code only runs if no action was performed or action was handled
// Get filter parameters
$category_id    = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status_filter  = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search         = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page           = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build WHERE clause
$where = "1=1";
$params = [];

if ($category_id > 0) {
    $where .= " AND i.category_id = ?";
    $params[] = $category_id;
}

if (in_array($status_filter, ['pending', 'published', 'claimed'])) {
    $where .= " AND i.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.finder_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Count total items
$count_sql = "SELECT COUNT(*) AS total FROM items i WHERE $where";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get items (FIXED: LIMIT & OFFSET ARE NOT PARAMETERS)
$sql = "SELECT i.*, c.name AS category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE $where
        ORDER BY i.created_at DESC
        LIMIT $items_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header/sidebar
include __DIR__ . '/../includes/admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    
    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <span class="navbar-brand">Manage Items</span>
                <a href="items.php?action=add" class="btn btn-sm btn-primary">Add New</a>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <!-- Success/Error Messages -->
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="claimed" <?php echo $status_filter == 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Showing <?php echo count($items); ?> of <?php echo $total_items; ?> items</p>
                <?php if($total_items > 0): ?>
                  
                <?php endif; ?>
            </div>
            
            <!-- Items Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Found Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($items) > 0): ?>
                                    <?php foreach($items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td>
                                                <?php if($item['image_url']): ?>
                                                    <img src="../uploads/items/<?php echo $item['image_url']; ?>" alt="Item" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['finder_name']); ?></small>
                                            </td>
                                            <td><?php echo $item['category_name']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $item['item_type'] == 'found' ? 'primary' : 'warning'; ?>">
                                                    <?php echo ucfirst($item['item_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($item['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif($item['status'] == 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Claimed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($item['date_found'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="items.php?action=view&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="items.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    
                                                    <!-- Quick Publish/Unpublish -->
                                                    <?php if($item['status'] == 'pending'): ?>
                                                        <a href="items.php?action=publish&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success"
                                                           onclick="return confirm('Publish this item?')">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php elseif($item['status'] == 'published'): ?>
                                                        <a href="items.php?action=unpublish&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning"
                                                           onclick="return confirm('Unpublish this item?')">
                                                            <i class="bi bi-x-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Quick Claim -->
                                                    <?php if($item['status'] != 'claimed'): ?>
                                                        <a href="items.php?action=status&id=<?php echo $item['id']; ?>&status=claimed" 
                                                           class="btn btn-sm btn-outline-info"
                                                           onclick="return confirm('Mark this item as claimed?')">
                                                            <i class="bi bi-check-square"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(isAdmin()): ?>
                                                        <a href="items.php?action=delete&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0">No items found</p>
                                            <a href="items.php?action=add" class="btn btn-sm btn-primary mt-2">Add New Item</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>