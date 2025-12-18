<?php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    header("Location: lost-and-found.php");
    exit();
}

// Get item details
$stmt = $conn->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ? AND i.status = 'published'
");
$stmt->execute([$id]);
$item = $stmt->fetch();

if(!$item) {
    header("Location: lost-and-found.php");
    exit();
}

// Get related items
$stmt = $conn->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.category_id = ? AND i.id != ? AND i.status = 'published'
    ORDER BY i.created_at DESC 
    LIMIT 3
");
$stmt->execute([$item['category_id'], $id]);
$related_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - LFIS</title>
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
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 80px 0 40px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Content Section */
        .content-section {
            background: var(--white);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .content-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .content-section p {
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        
        /* Mission & Vision */
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .mission-box, .vision-box {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .mission-icon, .vision-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        /* Team Section */
        .team-section {
            margin: 60px 0;
        }
        
        .team-section h2 {
            text-align: center;
            color: var(--dark-gray);
            margin-bottom: 40px;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .team-member {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .team-member:hover {
            transform: translateY(-5px);
        }
        
        .team-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .team-info {
            padding: 20px;
        }
        
        .team-info h4 {
            color: var(--dark-gray);
            margin-bottom: 5px;
        }
        
        .team-info p {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--dark-gray), #1a1a1a);
            color: white;
            padding: 60px 0;
            margin: 60px 0;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 40px auto;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary);
            transform: translateX(-50%);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 40px;
        }
        
        .timeline-content {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 45%;
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: 55%;
        }
        
        .timeline-item:nth-child(even) .timeline-content {
            margin-right: 55%;
        }
        
        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 20px;
            width: 20px;
            height: 20px;
            background: var(--primary);
            border-radius: 50%;
            transform: translateX(-50%);
            z-index: 1;
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
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .timeline::before {
                left: 20px;
            }
            
            .timeline-content {
                width: calc(100% - 50px);
                margin-left: 50px !important;
            }
            
            .timeline-dot {
                left: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container my-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">            
               <li class="breadcrumb-item active">Item Details</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if($item['image_url']): ?>
                                    <img src="uploads/items/<?php echo $item['image_url']; ?>" class="img-fluid rounded item-image w-100" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h2 class="mb-3"><?php echo htmlspecialchars($item['title']); ?></h2>
                                
                                <div class="mb-3">
                                    <span class="badge bg-primary me-2"><?php echo $item['category_name']; ?></span>
                                    <span class="badge bg-secondary"><?php echo ucfirst($item['item_type']); ?> Item</span>
                                    <span class="badge bg-success"><?php echo ucfirst($item['status']); ?></span>
                                </div>
                                
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p class="lead"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <h6><i class="bi bi-calendar text-primary"></i> Date</h6>
                                        <p><?php echo formatDate($item['date_found']); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <h6><i class="bi bi-geo-alt text-primary"></i> Location</h6>
                                        <p><?php echo htmlspecialchars($item['location_found']); ?></p>
                                    </div>
                                </div>
                                
                                <?php if($item['item_type'] == 'found'): ?>
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Found Item Information</h6>
                                        <p class="mb-1"><strong>Found by:</strong> <?php echo htmlspecialchars($item['finder_name']); ?></p>
                                        <?php if($item['finder_contact']): ?>
                                            <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($item['finder_contact']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="text-center">
                                        <a href="claim-item.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-lg">
                                            <i class="bi bi-check-circle"></i> Claim This Item
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Related Items -->
                <?php if(count($related_items) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Related Items</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach($related_items as $related): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <h6><a href="item-details.php?id=<?php echo $related['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($related['title']); ?></a></h6>
                                    <small class="text-muted"><?php echo formatDate($related['date_found']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="post-item.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Report Found Item
                            </a>
                            <a href="lost-and-found.php" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Browse All Items
                            </a>
                            <a href="contact.php" class="btn btn-outline-dark">
                                <i class="bi bi-envelope"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>