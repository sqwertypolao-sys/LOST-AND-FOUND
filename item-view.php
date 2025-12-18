<?php
require_once '../includes/config.php';

// Check authentication
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get item ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id == 0) {
    header("Location: items.php");
    exit();
}

// Fetch item data with category and location names
$sql = "SELECT i.*, c.name AS category_name, 
               i.location_found AS location_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item) {
    header("Location: items.php?error=Item not found");
    exit();
}

// Initialize missing fields with default values
$item['date_lost'] = $item['date_lost'] ?? null;
$item['updated_at'] = $item['updated_at'] ?? null;
$item['user_id'] = $item['user_id'] ?? ($item['created_by'] ?? null);
$item['exact_location'] = $item['exact_location'] ?? null;
$item['admin_notes'] = $item['admin_notes'] ?? null;
$item['finder_phone'] = $item['finder_phone'] ?? null;

// Format dates
$date_found_formatted = $item['date_found'] ? date('F j, Y', strtotime($item['date_found'])) : 'Not specified';
$date_lost_formatted = $item['date_lost'] ? date('F j, Y', strtotime($item['date_lost'])) : 'Not specified';
$created_at_formatted = date('F j, Y \a\t g:i A', strtotime($item['created_at']));
$updated_at_formatted = $item['updated_at'] ? date('F j, Y \a\t g:i A', strtotime($item['updated_at'])) : 'Never';

// Get user who added the item
$added_by = 'Unknown';
if($item['user_id']) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$item['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user) {
        $added_by = $user['username'];
    }
}

// Get claim requests for this item
$claims_sql = "SELECT c.*, u.username AS claimant_name, u.email AS claimant_email
               FROM claims c
               LEFT JOIN users u ON c.user_id = u.id
               WHERE c.item_id = ?
               ORDER BY c.created_at DESC";
$claims_stmt = $conn->prepare($claims_sql);
$claims_stmt->execute([$id]);
$claims = $claims_stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Item - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .item-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .item-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .info-card .card-header {
            background: #f8f9fa;
            border-bottom: 2px solid #007bff;
            font-weight: 600;
        }
        .badge-status {
            font-size: 0.9em;
            padding: 5px 15px;
            border-radius: 20px;
        }
        .action-buttons .btn {
            min-width: 100px;
        }
        .contact-info {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 15px;
        }
        .claim-card {
            border-left: 4px solid #28a745;
        }
        .claim-card.pending {
            border-left-color: #ffc107;
        }
        .claim-card.rejected {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <!-- Header -->
        <div class="item-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="items.php" class="text-white">Items</a></li>
                                <li class="breadcrumb-item active text-white" aria-current="page">View Item</li>
                            </ol>
                        </nav>
                        <h1 class="display-6 mb-2"><?php echo htmlspecialchars($item['title']); ?></h1>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-<?php echo $item['item_type'] == 'found' ? 'primary' : 'warning'; ?> badge-status">
                                <?php echo strtoupper($item['item_type']); ?> ITEM
                            </span>
                            <?php if($item['status'] == 'pending'): ?>
                                <span class="badge bg-warning badge-status">PENDING</span>
                            <?php elseif($item['status'] == 'published'): ?>
                                <span class="badge bg-success badge-status">PUBLISHED</span>
                            <?php elseif($item['status'] == 'claimed'): ?>
                                <span class="badge bg-info badge-status">CLAIMED</span>
                            <?php endif; ?>
                            <span class="text-white-50">Item ID: #<?php echo $item['id']; ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end action-buttons">
                        <a href="items.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="items.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Left Column: Image and Basic Info -->
                <div class="col-lg-4">
                    <!-- Image Card -->
                    <div class="card info-card mb-4">
                        <div class="card-header">
                            <i class="bi bi-image"></i> Item Image
                        </div>
                        <div class="card-body text-center">
                            <?php if(!empty($item['image_url'])): ?>
                                <img src="../uploads/items/<?php echo $item['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="item-image mb-3">
                                <div class="mt-2">
                                    <a href="../uploads/items/<?php echo $item['image_url']; ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrows-fullscreen"></i> View Full Size
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="py-5 text-center">
                                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                    <p class="mt-3 text-muted">No image available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="card info-card mb-4">
                        <div class="card-header">
                            <i class="bi bi-lightning"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if($item['status'] == 'pending'): ?>
                                    <a href="items.php?action=publish&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Publish this item?')">
                                        <i class="bi bi-check-circle"></i> Publish Item
                                    </a>
                                <?php elseif($item['status'] == 'published'): ?>
                                    <a href="items.php?action=unpublish&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-warning btn-sm"
                                       onclick="return confirm('Unpublish this item?')">
                                        <i class="bi bi-x-circle"></i> Unpublish
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($item['status'] != 'claimed'): ?>
                                    <a href="items.php?action=status&id=<?php echo $item['id']; ?>&status=claimed" 
                                       class="btn btn-info btn-sm"
                                       onclick="return confirm('Mark this item as claimed?')">
                                        <i class="bi bi-check-square"></i> Mark as Claimed
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(isAdmin()): ?>
                                    <a href="items.php?action=delete&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="bi bi-trash"></i> Delete Item
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="card info-card">
                        <div class="card-header">
                            <i class="bi bi-person-circle"></i> Contact Information
                        </div>
                        <div class="card-body contact-info">
                            <h6 class="mb-3"><?php echo htmlspecialchars($item['finder_name']); ?></h6>
                            
                            <?php if(!empty($item['finder_email'])): ?>
                                <div class="mb-2">
                                    <i class="bi bi-envelope me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($item['finder_email']); ?>">
                                        <?php echo htmlspecialchars($item['finder_email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($item['finder_phone'])): ?>
                                <div class="mb-2">
                                    <i class="bi bi-telephone me-2"></i>
                                    <a href="tel:<?php echo htmlspecialchars($item['finder_phone']); ?>">
                                        <?php echo htmlspecialchars($item['finder_phone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                This information is only visible to admin/staff
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Details -->
                <div class="col-lg-8">
                    <!-- Description Card -->
                    <div class="card info-card mb-4">
                        <div class="card-header">
                            <i class="bi bi-card-text"></i> Description
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                            
                            <?php if(!empty($item['exact_location'])): ?>
                                <hr>
                                <h6><i class="bi bi-geo-alt"></i> Location Details</h6>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($item['exact_location'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Details Grid -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card info-card h-100">
                                <div class="card-header">
                                    <i class="bi bi-grid"></i> Item Details
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Category:</th>
                                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td><?php echo htmlspecialchars($item['location_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Item Type:</th>
                                            <td>
                                                <span class="badge bg-<?php echo $item['item_type'] == 'found' ? 'primary' : 'warning'; ?>">
                                                    <?php echo ucfirst($item['item_type']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <?php if($item['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif($item['status'] == 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Claimed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card info-card h-100">
                                <div class="card-header">
                                    <i class="bi bi-calendar"></i> Date Information
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="50%">
                                                <?php echo $item['item_type'] == 'found' ? 'Date Found:' : 'Date Lost:'; ?>
                                            </th>
                                            <td>
                                                <?php echo $item['item_type'] == 'found' ? $date_found_formatted : $date_lost_formatted; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Added to System:</th>
                                            <td><?php echo $created_at_formatted; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td><?php echo $updated_at_formatted; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Added By:</th>
                                            <td><?php echo htmlspecialchars($added_by); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Notes -->
                    <?php if(!empty($item['admin_notes'])): ?>
                        <div class="card info-card mt-4">
                            <div class="card-header">
                                <i class="bi bi-shield-check"></i> Admin Notes
                            </div>
                            <div class="card-body">
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($item['admin_notes'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Claim Requests -->
                    <?php if(count($claims) > 0): ?>
                        <div class="card info-card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clipboard-check"></i> Claim Requests
                                    <span class="badge bg-secondary ms-2"><?php echo count($claims); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach($claims as $claim): ?>
                                        <div class="list-group-item list-group-item-action claim-card 
                                            <?php echo $claim['status'] == 'pending' ? 'pending' : ($claim['status'] == 'approved' ? 'approved' : 'rejected'); ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($claim['claimant_name']); ?>
                                                    <span class="badge bg-<?php 
                                                        echo $claim['status'] == 'pending' ? 'warning' : 
                                                            ($claim['status'] == 'approved' ? 'success' : 'danger'); 
                                                    ?> ms-2">
                                                        <?php echo ucfirst($claim['status']); ?>
                                                    </span>
                                                </h6>
                                                <small><?php echo date('M d, Y', strtotime($claim['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($claim['description'])); ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope"></i> 
                                                <?php echo htmlspecialchars($claim['claimant_email']); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Print functionality
        function printItem() {
            window.print();
        }
        
        // Copy item URL
        function copyItemUrl() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('Item URL copied to clipboard!');
            });
        }
    </script>
</body>
</html>