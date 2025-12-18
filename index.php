<?php
require_once 'includes/config.php';

// Get featured items (published items)
$stmt = $conn->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.status = 'published' 
    ORDER BY i.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$featured_items = $stmt->fetchAll();

// Get active categories
$stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll();

// Get homepage content
$home_content = getPageContent('home', $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: <?php echo PRIMARY_COLOR; ?>;
            --primary-dark: #E65100;
            --dark-gray: <?php echo DARK_GRAY; ?>;
            --light-gray: <?php echo LIGHT_GRAY; ?>;
            --white: <?php echo WHITE; ?>;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            padding-top: 56px;
        }
        
        /* Navigation */
        .navbar {
            background-color: var(--dark-gray) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        .nav-link.active {
            color: var(--primary) !important;
            border-bottom: 2px solid var(--primary);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(46, 46, 46, 0.9), rgba(46, 46, 46, 0.9)), url('https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 80px 0;
            margin-bottom: 40px;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero .lead {
            font-size: 1.25rem;
            opacity: 0.9;
        }
        
        /* Cards */
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
        
        .card-title {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .card-text {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        /* Buttons */
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
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .bg-primary {
            background-color: var(--primary) !important;
        }
        
        /* Footer */
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
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 30px;
        }
        
        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .feature {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        /* Category List */
        .category-list {
            list-style: none;
            padding: 0;
        }
        
        .category-list li {
            margin-bottom: 10px;
        }
        
        .category-list a {
            display: block;
            padding: 10px 15px;
            background: var(--white);
            border-radius: 5px;
            text-decoration: none;
            color: var(--dark-gray);
            transition: all 0.3s;
        }
        
        .category-list a:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateX(5px);
        }
        
        /* Statistics */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .stat-box {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero .lead {
                font-size: 1rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-search-heart"></i> LFIS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lost-and-found.php">Lost & Found</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post-item.php">Post an Item</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact Us</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Lost & Found Information System</h1>
                    <p class="lead">Helping reunite lost items with their rightful owners. Find, report, and claim lost items easily.</p>
                    <div class="mt-4">
                        <a href="post-item.php" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-plus-circle"></i> Report Found Item
                        </a>
                        <a href="lost-and-found.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search"></i> Browse Items
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3062/3062634.png" alt="Lost and Found" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <?php if($home_content): ?>
            <div class="content-section mb-5">
                <?php echo $home_content['content']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Features Section -->
        <div class="features mb-5">
            <div class="feature">
                <div class="feature-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Search Lost Items</h3>
                <p>Browse through our database of found items. Filter by category, date, and location.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <h3>Report Found Items</h3>
                <p>Found something? Report it here to help reunite it with its owner.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3>Secure Claim Process</h3>
                <p>Verified claim process to ensure items go to their rightful owners.</p>
            </div>
        </div>
        
        <!-- Recent Items Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Recently Found Items</h2>
                    <a href="lost-and-found.php" class="btn btn-outline-primary">View All Items</a>
                </div>
                
                <div class="row">
                    <?php if(count($featured_items) > 0): ?>
                        <?php foreach($featured_items as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <?php if($item['image_url']): ?>
                                        <img src="uploads/items/<?php echo $item['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($item['title']); ?></h5>
                                            <?php echo getStatusBadge($item['status']); ?>
                                        </div>
                                        <p class="card-text text-muted small">
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($item['location_found']); ?>
                                        </p>
                                        <p class="card-text"><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php echo getCategoryBadge($item['category_id'], $conn); ?>
                                                <span class="badge bg-secondary"><?php echo ucfirst($item['item_type']); ?></span>
                                            </div>
                                            <a href="item-details.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> Found on: <?php echo formatDate($item['date_found']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No items found. Check back later or post a found item.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Categories & Quick Links -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Browse by Category</h4>
                        <ul class="category-list">
                            <?php foreach($categories as $category): ?>
                                <li>
                                    <a href="lost-and-found.php?category=<?php echo $category['id']; ?>">
                                        <i class="bi bi-folder me-2"></i> <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Quick Actions</h4>
                        <div class="d-grid gap-3">
                            <a href="post-item.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i> Report Found Item
                            </a>
                            <a href="lost-and-found.php?type=lost" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-search me-2"></i> Search Lost Items
                            </a>
                            <a href="contact.php" class="btn btn-outline-dark btn-lg">
                                <i class="bi bi-envelope me-2"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>Lost & Found Information System</h5>
                    <p class="mt-3">Helping reunite lost items with their rightful owners since 2025.</p>
                    <div class="mt-4">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook" style="font-size: 1.5rem;"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter" style="font-size: 1.5rem;"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-instagram" style="font-size: 1.5rem;"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php">Home</a></li>
                        <li class="mb-2"><a href="lost-and-found.php">Lost & Found Items</a></li>
                        <li class="mb-2"><a href="post-item.php">Post Found Item</a></li>
                        <li class="mb-2"><a href="about.php">About Us</a></li>
                        <li class="mb-2"><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contact Information</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Cotabato State University, CETC Building</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> LFIS@gmail.com</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> 903-436-9357</li>
                        <li class="mb-2"><i class="bi bi-phone me-2"></i> 0900-000-0000</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0">© Copyright Admin. All Rights Reserved<br>Template Designed by BootstrapMade</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>