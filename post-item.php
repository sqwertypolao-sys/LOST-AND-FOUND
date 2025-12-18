<?php
require_once 'includes/config.php';

$success = '';
$error = '';

// Get categories
$stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $item_type = sanitize($_POST['item_type']);
    $date_found = $_POST['date_found'];
    $location_found = sanitize($_POST['location_found']);
    $finder_name = sanitize($_POST['finder_name']);
    $finder_contact = sanitize($_POST['finder_contact']);
    $finder_email = sanitize($_POST['finder_email']);
    
    if(empty($title) || empty($description) || empty($category_id) || empty($date_found)) {
        $error = "Please fill in all required fields.";
    } else {
        $image_url = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['image'], 'items/');
            if($upload_result['success']) {
                $image_url = $upload_result['file_name'];
            } else {
                $error = $upload_result['error'];
            }
        }
        
        if(!$error) {
            $sql = "INSERT INTO items (title, description, category_id, item_type, date_found, location_found, 
                    image_url, finder_name, finder_contact, finder_email, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $params = [$title, $description, $category_id, $item_type, $date_found, $location_found, 
                      $image_url, $finder_name, $finder_contact, $finder_email];
            
            if($stmt->execute($params)) {
                $success = "Item submitted successfully! It will be reviewed by our staff before being published.";
                $_POST = [];
            } else {
                $error = "Failed to submit item. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Found Item - LFIS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

   <style>
        :root {
            --primary: #F57C00;
            --primary-dark: #E65100;
            --dark-gray: #2E2E2E;
            --light-gray: #EEEEEE;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            padding-top: 56px;
        }

        .navbar, .footer {
            background-color: var(--dark-gray) !important;
        }

        .navbar-brand {
            color: var(--primary) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--white) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary) !important;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 8px 24px;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .bg-primary {
            background-color: var(--primary) !important;
        }

        .bg-secondary {
            background-color: var(--dark-gray) !important;
            color: var(--white);
        }

        .footer {
            background-color: var(--dark-gray);
            color: var(--light-gray);
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer a {
            color: var(--light-gray);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Report Found Item</h4>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <a href="lost-and-found.php" class="btn btn-primary me-2">Browse Items</a>
                            <a href="post-item.php" class="btn btn-outline-primary">Report Another Item</a>
                        </div>
                    <?php else: ?>
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Item Title *</label>
                                    <input type="text" name="title" class="form-control" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id']; ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Item Type *</label>
                                    <select name="item_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="found" <?= isset($_POST['item_type']) && $_POST['item_type'] == 'found' ? 'selected' : ''; ?>>Found Item</option>
                                        <option value="lost" <?= isset($_POST['item_type']) && $_POST['item_type'] == 'lost' ? 'selected' : ''; ?>>Lost Item</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date Found/Lost *</label>
                                    <input type="date" name="date_found" class="form-control" value="<?= isset($_POST['date_found']) ? $_POST['date_found'] : date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location Found/Lost</label>
                                <input type="text" name="location_found" class="form-control" value="<?= isset($_POST['location_found']) ? htmlspecialchars($_POST['location_found']) : ''; ?>" placeholder="e.g., Library Entrance, Parking Lot">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Item Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Max size: 5MB. Allowed: JPG, PNG, GIF</small>
                            </div>

                            <h5 class="mt-4 mb-3">Your Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name *</label>
                                    <input type="text" name="finder_name" class="form-control" value="<?= isset($_POST['finder_name']) ? htmlspecialchars($_POST['finder_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="finder_contact" class="form-control" value="<?= isset($_POST['finder_contact']) ? htmlspecialchars($_POST['finder_contact']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="finder_email" class="form-control" value="<?= isset($_POST['finder_email']) ? htmlspecialchars($_POST['finder_email']) : ''; ?>" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i> Submit Report
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    * Required fields. Submitted items will be reviewed by staff before being published.
                                </small>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
