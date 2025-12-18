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
    $user_id = $_SESSION['user_id'];
    
    // Handle image upload
    $image_url = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
            $upload_path = '../uploads/items/' . $filename;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = $filename;
                
                // Create thumbnail
                createThumbnail($upload_path, '../uploads/items/thumbs/' . $filename, 300, 300);
            }
        }
    }
    
    // Set appropriate date based on item type
    $date_field = ($item_type == 'found') ? $date_found : $date_lost;
    
    // Insert into database
    $sql = "INSERT INTO items (
        title, category_id, item_type, description, 
        finder_name, finder_email, finder_phone, 
        location_id, exact_location, image_url,
        date_found, date_lost, status, admin_notes, user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        $title, $category_id, $item_type, $description,
        $finder_name, $finder_email, $finder_phone,
        $location_id, $exact_location, $image_url,
        $date_found, $date_lost, $status, $admin_notes, $user_id
    ]);
    
    if($success) {
        $item_id = $conn->lastInsertId();
        
        // Log the action
        logAction("Item Added", "New item added: $title (ID: $item_id)");
        
        // Redirect to items list
        header("Location: items.php?success=Item added successfully");
        exit();
    } else {
        $error = "Failed to add item. Please try again.";
    }
}

include 'includes/admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .image-preview {
            max-width: 300px;
            max-height: 300px;
            display: none;
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
        .form-section h5 {
            margin-bottom: 20px;
            color: #495057;
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
                    Add New Item
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
                                <option value="">Select Type</option>
                                <option value="found">Found Item</option>
                                <option value="lost">Lost Item</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label required-field">Item Title/Name</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   placeholder="e.g., Black Wallet, iPhone 13, Silver Keys">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label required-field">Category</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label required-field">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="published">Published</option>
                                        <option value="claimed">Claimed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label required-field">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required 
                                      placeholder="Detailed description of the item including brand, color, unique features, contents, etc."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="text-muted">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</small>
                            <img id="imagePreview" class="img-thumbnail image-preview" alt="Preview">
                        </div>
                    </div>
                    
                    <!-- Location Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-geo-alt"></i> Location Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_id" class="form-label required-field">General Location</label>
                                    <select class="form-select" id="location_id" name="location_id" required>
                                        <option value="">Select Location</option>
                                        <?php foreach($locations as $location): ?>
                                            <option value="<?php echo $location['id']; ?>">
                                                <?php echo htmlspecialchars($location['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="date_found_field">
                                    <label for="date_found" class="form-label required-field">Date Found</label>
                                    <input type="date" class="form-control" id="date_found" name="date_found" 
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="mb-3" id="date_lost_field" style="display: none;">
                                    <label for="date_lost" class="form-label required-field">Date Lost</label>
                                    <input type="date" class="form-control" id="date_lost" name="date_lost" 
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="exact_location" class="form-label">Exact Location / Details</label>
                            <textarea class="form-control" id="exact_location" name="exact_location" rows="2"
                                      placeholder="e.g., Near the cafeteria, Room 201, Parking lot section B, etc."></textarea>
                        </div>
                    </div>
                    
                    <!-- Person Information -->
                    <div class="form-section">
                        <h5><i class="bi bi-person"></i> Person Information</h5>
                        
                        <div class="mb-3">
                            <label for="finder_name" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="finder_name" name="finder_name" required
                                   placeholder="Name of person who found/lost the item">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="finder_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="finder_email" name="finder_email"
                                           placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="finder_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="finder_phone" name="finder_phone"
                                           placeholder="(123) 456-7890">
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
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="8"
                                      placeholder="Internal notes, special instructions, verification details, etc."></textarea>
                            <small class="text-muted">These notes are only visible to admin/staff</small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Add Item
                                </button>
                                <a href="items.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                            
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="add_another" name="add_another">
                                    <label class="form-check-label" for="add_another">
                                        Add another item after this
                                    </label>
                                </div>
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
            // Initialize Select2
            $('#category_id, #location_id').select2({
                theme: 'bootstrap-5'
            });
            
            // Handle item type change
            $('#item_type').change(function() {
                if($(this).val() === 'found') {
                    $('#date_found_field').show();
                    $('#date_lost_field').hide();
                    $('#date_found').prop('required', true);
                    $('#date_lost').prop('required', false);
                } else {
                    $('#date_found_field').hide();
                    $('#date_lost_field').show();
                    $('#date_found').prop('required', false);
                    $('#date_lost').prop('required', true);
                }
            });
            
            // Image preview
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
                // Validate required fields
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
    </script>
</body>
</html>