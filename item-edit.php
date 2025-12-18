<?php
require_once '../includes/config.php';

// Check authentication
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check permissions
if(!isAdmin() && !isStaff()) {
    header("Location: dashboard.php");
    exit();
}

// Get item ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id == 0) {
    header("Location: items.php");
    exit();
}

// Fetch item data
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item) {
    header("Location: items.php?error=Item not found");
    exit();
}

// Initialize missing fields with default values
$item['user_id'] = $item['user_id'] ?? ($item['created_by'] ?? null);
$item['date_lost'] = $item['date_lost'] ?? null;
$item['exact_location'] = $item['exact_location'] ?? null;
$item['finder_phone'] = $item['finder_phone'] ?? null;
$item['admin_notes'] = $item['admin_notes'] ?? null;
$item['updated_at'] = $item['updated_at'] ?? null;
$item['location_id'] = $item['location_id'] ?? null;

// Check if user has permission to edit
if(!isAdmin() && $item['user_id'] != $_SESSION['user_id']) {
    header("Location: items.php?error=You do not have permission to edit this item");
    exit();
}

// Get categories for dropdown
$stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get locations for dropdown
$stmt = $conn->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $title = sanitize($_POST['title']);
    $category_id = intval($_POST['category_id']);
    $item_type = sanitize($_POST['item_type']);
    $description = sanitize($_POST['description']);
    $finder_name = sanitize($_POST['finder_name']);
    $finder_email = sanitize($_POST['finder_email']);
    $finder_phone = sanitize($_POST['finder_phone']);
    $location_id = intval($_POST['location_id']);
    $date_found = sanitize($_POST['date_found']);
    $date_lost = sanitize($_POST['date_lost']);
    $exact_location = sanitize($_POST['exact_location']);
    $status = sanitize($_POST['status']);
    $admin_notes = sanitize($_POST['admin_notes']);
    
    // Handle image upload
    $image_url = $item['image_url'];
    $delete_old_image = false;
    
    if(isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        // Delete existing image
        if($image_url) {
            $old_image = '../uploads/items/' . $image_url;
            $old_thumb = '../uploads/items/thumbs/' . $image_url;
            if(file_exists($old_image)) unlink($old_image);
            if(file_exists($old_thumb)) unlink($old_thumb);
            $image_url = '';
            $delete_old_image = true;
        }
    }
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            // Delete old image if exists
            if($image_url && !$delete_old_image) {
                $old_image = '../uploads/items/' . $image_url;
                $old_thumb = '../uploads/items/thumbs/' . $image_url;
                if(file_exists($old_image)) unlink($old_image);
                if(file_exists($old_thumb)) unlink($old_thumb);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
            $upload_path = '../uploads/items/' . $filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = $filename;
                
                // Create thumbnail
                if(function_exists('createThumbnail')) {
                    createThumbnail($upload_path, '../uploads/items/thumbs/' . $filename, 300, 300);
                }
            }
        }
    }
    
    // Check which columns exist in the database
    $check_columns = $conn->query("SHOW COLUMNS FROM items")->fetchAll(PDO::FETCH_COLUMN);
    
    // Build dynamic SQL update based on available columns
    $update_fields = [];
    $params = [];
    
    // Always update these basic fields
    $update_fields[] = "title = ?";
    $params[] = $title;
    
    $update_fields[] = "category_id = ?";
    $params[] = $category_id;
    
    $update_fields[] = "item_type = ?";
    $params[] = $item_type;
    
    $update_fields[] = "description = ?";
    $params[] = $description;
    
    $update_fields[] = "finder_name = ?";
    $params[] = $finder_name;
    
    $update_fields[] = "finder_email = ?";
    $params[] = $finder_email;
    
    // Conditional fields
    if(in_array('finder_phone', $check_columns)) {
        $update_fields[] = "finder_phone = ?";
        $params[] = $finder_phone;
    }
    
    if(in_array('location_id', $check_columns)) {
        $update_fields[] = "location_id = ?";
        $params[] = $location_id;
    }
    
    if(in_array('exact_location', $check_columns)) {
        $update_fields[] = "exact_location = ?";
        $params[] = $exact_location;
    }
    
    $update_fields[] = "image_url = ?";
    $params[] = $image_url;
    
    $update_fields[] = "date_found = ?";
    $params[] = $date_found;
    
    if(in_array('date_lost', $check_columns)) {
        $update_fields[] = "date_lost = ?";
        $params[] = $date_lost;
    }
    
    $update_fields[] = "status = ?";
    $params[] = $status;
    
    if(in_array('admin_notes', $check_columns)) {
        $update_fields[] = "admin_notes = ?";
        $params[] = $admin_notes;
    }
    
    if(in_array('updated_at', $check_columns)) {
        $update_fields[] = "updated_at = NOW()";
    }
    
    // Add WHERE condition
    $params[] = $id;
    
    // Build final SQL
    $sql = "UPDATE items SET " . implode(", ", $update_fields) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute($params);
    
    if($success) {
        // Log the action
        if(function_exists('logAction')) {
            logAction("Item Updated", "Item updated: $title (ID: $id)");
        }
        
        // Redirect based on button clicked
        if(isset($_POST['save_and_view'])) {
            header("Location: items.php?action=view&id=$id&success=Item updated successfully");
        } else {
            header("Location: items.php?success=Item updated successfully");
        }
        exit();
    } else {
        $error = "Failed to update item. Please try again.";
    }
}

// Check if admin-header.php exists in includes directory
if(file_exists(__DIR__ . '/../includes/admin-header.php')) {
    include __DIR__ . '/../includes/admin-header.php';
} else {
    // Fallback to relative path
    include 'includes/admin-header.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .image-preview {
            max-width: 300px;
            max-height: 300px;
            margin-top: 10px;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #007bff;
        }
        .current-image {
            position: relative;
            display: inline-block;
        }
        .delete-image-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
        }
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            padding: 0;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            margin-top: 4px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <a href="items.php" class="navbar-brand">
                    <i class="bi bi-arrow-left"></i> Back to Items
                </a>
                <span class="navbar-text">
                    Edit Item #<?php echo $item['id']; ?>
                </span>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-info-circle"></i> Basic Information</h5>
                        
                        <div class="mb-3">
                            <label for="item_type" class="form-label required-field">Item Type</label>
                            <select class="form-select" id="item_type" name="item_type" required>
                                <option value="found" <?php echo $item['item_type'] == 'found' ? 'selected' : ''; ?>>Found Item</option>
                                <option value="lost" <?php echo $item['item_type'] == 'lost' ? 'selected' : ''; ?>>Lost Item</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label required-field">Item Title/Name</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label required-field">Category</label>
                                    <div class="dropdown">
                                        <select class="form-control select2" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $item['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label required-field">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="published" <?php echo $item['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="claimed" <?php echo $item['status'] == 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label required-field">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <?php if(!empty($item['image_url'])): ?>
                                    <div class="current-image">
                                        <img src="../uploads/items/<?php echo $item['image_url']; ?>" 
                                             alt="Current Image" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px;">
                                        <button type="button" class="delete-image-btn" onclick="deleteImage()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="delete_image" id="delete_image" value="0">
                                    <div class="form-text">Click trash icon to delete current image</div>
                                <?php else: ?>
                                    <div class="alert alert-info">No image uploaded</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-3">
                                <label for="image" class="form-label">Upload New Image</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="text-muted">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</small>
                                <img id="imagePreview" class="img-thumbnail image-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-geo-alt"></i> Location Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_id" class="form-label">General Location</label>
                                    <div class="dropdown">
                                        <select class="form-control select2" id="location_id" name="location_id">
                                            <option value="">Select Location</option>
                                            <?php foreach($locations as $location): ?>
                                                <option value="<?php echo $location['id']; ?>" 
                                                    <?php echo (!empty($item['location_id']) && $item['location_id'] == $location['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($location['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="date_found_field" <?php echo $item['item_type'] == 'lost' ? 'style="display:none;"' : ''; ?>>
                                    <label for="date_found" class="form-label">Date Found</label>
                                    <input type="date" class="form-control" id="date_found" name="date_found" 
                                           value="<?php echo $item['date_found'] ?? ''; ?>">
                                </div>
                                <div class="mb-3" id="date_lost_field" <?php echo $item['item_type'] == 'found' ? 'style="display:none;"' : ''; ?>>
                                    <label for="date_lost" class="form-label">Date Lost</label>
                                    <input type="date" class="form-control" id="date_lost" name="date_lost" 
                                           value="<?php echo $item['date_lost'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="exact_location" class="form-label">Exact Location / Details</label>
                            <textarea class="form-control" id="exact_location" name="exact_location" rows="2"><?php echo htmlspecialchars($item['exact_location'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Person Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-person"></i> Person Information</h5>
                        
                        <div class="mb-3">
                            <label for="finder_name" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="finder_name" name="finder_name" required
                                   value="<?php echo htmlspecialchars($item['finder_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="finder_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="finder_email" name="finder_email"
                                           value="<?php echo htmlspecialchars($item['finder_email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="finder_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="finder_phone" name="finder_phone"
                                           value="<?php echo htmlspecialchars($item['finder_phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Admin Section -->
                    <div class="form-section">
                        <h5><i class="bi bi-shield-check"></i> Admin Information</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Added By</label>
                            <input type="text" class="form-control" 
                                   value="User ID: <?php echo $item['user_id'] ?? 'Unknown'; ?> (<?php echo date('M d, Y', strtotime($item['created_at'])); ?>)" 
                                   readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="12"><?php echo htmlspecialchars($item['admin_notes'] ?? ''); ?></textarea>
                            <small class="text-muted">These notes are only visible to admin/staff</small>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="bi bi-clock-history"></i> Timestamps
                        </div>
                        <div class="card-body">
                            <small class="d-block mb-1">
                                <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($item['created_at'])); ?>
                            </small>
                            <small class="d-block">
                                <strong>Last Updated:</strong> 
                                <?php echo !empty($item['updated_at']) ? date('M d, Y H:i', strtotime($item['updated_at'])) : 'Never'; ?>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Item
                                </button>
                                <button type="submit" name="save_and_view" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> Update & View
                                </button>
                                <a href="items.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for both dropdowns with search boxes
            $('#category_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a category',
                allowClear: true,
                width: '100%'
                centered: true
            });
            
            $('#location_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a location',
                allowClear: true,
                width: '100%'
            });
            
            // Handle item type change
            $('#item_type').change(function() {
                if($(this).val() === 'found') {
                    $('#date_found_field').show();
                    $('#date_lost_field').hide();
                } else {
                    $('#date_found_field').hide();
                    $('#date_lost_field').show();
                }
            });
            
            // Image preview for new upload
            $('#image').change(function() {
                const file = this.files[0];
                if(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Form validation
            $('form').submit(function() {
                const requiredFields = $(this).find('[required]');
                let isValid = true;
                
                requiredFields.each(function() {
                    if(!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if(!isValid) {
                    alert('Please fill in all required fields.');
                    return false;
                }
                
                return true;
            });
        });
        
        function deleteImage() {
            if(confirm('Are you sure you want to delete this image?')) {
                $('#delete_image').val('1');
                $('.current-image').fadeOut();
            }
        }
    </script>
</body>
</html>