<?php
require_once 'includes/config.php';

// Get about page content from database
$about_content = getPageContent('about', $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Lost and Found Information System</title>
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lost-and-found.php">Lost & Found</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post-item.php">Post an Item</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>About Us</h1>
            <p>Learn about our mission, vision, and the team behind the Lost and Found Information System</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- About Content -->
        <div class="content-section">
            <?php if($about_content && !empty($about_content['content'])): ?>
                <?php echo $about_content['content']; ?>
            <?php else: ?>
                <h2>Our Story</h2>
                <p>The Lost and Found Information System (LFIS) was established in 2024 with a simple yet powerful mission: to help people reunite with their lost belongings quickly and efficiently. What started as a small university project has grown into a comprehensive platform serving thousands of users.</p>
                
                <p>We understand the frustration and anxiety that comes with losing important items. Whether it's a mobile phone with precious memories, a wallet with essential documents, or even sentimental items, we believe that technology can play a crucial role in bringing lost items back to their rightful owners.</p>
            <?php endif; ?>
        </div>

        <!-- Mission & Vision -->
        <div class="mission-vision">
            <div class="mission-box">
                <div class="mission-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h3>Our Mission</h3>
                <p>To provide an efficient, user-friendly platform that helps reunite lost items with their owners through technology, community support, and innovative solutions.</p>
            </div>
            
            <div class="vision-box">
                <div class="vision-icon">
                    <i class="bi bi-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>To become the leading lost and found platform recognized for our commitment to community service, technological innovation, and making a positive impact on people's lives.</p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="container">
                <h2 class="mb-5">Our Impact in Numbers</h2>
                <div class="stats-grid">
                    <?php
                    // Get statistics
                    $stmt = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'published'");
                    $published_items = $stmt->fetchColumn();
                    
                    $stmt = $conn->query("SELECT COUNT(*) FROM items WHERE status = 'claimed'");
                    $claimed_items = $stmt->fetchColumn();
                    
                    $stmt = $conn->query("SELECT COUNT(DISTINCT finder_email) FROM items WHERE finder_email IS NOT NULL");
                    $contributors = $stmt->fetchColumn();
                    
                    $stmt = $conn->query("SELECT COUNT(*) FROM messages");
                    $messages = $stmt->fetchColumn();
                    ?>
                    <div class="stat-item">
                        <h3><?php echo $published_items; ?>+</h3>
                        <p>Items Found</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $claimed_items; ?>+</h3>
                        <p>Items Reunited</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $contributors; ?>+</h3>
                        <p>Helpful Contributors</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php echo $messages; ?>+</h3>
                        <p>Messages Handled</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="content-section">
            <h2>How Our System Works</h2>
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-plus-circle" style="font-size: 2rem;"></i>
                        </div>
                        <h4>1. Report Found Item</h4>
                        <p>Find an item? Report it through our easy-to-use form with details and photos.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-search" style="font-size: 2rem;"></i>
                        </div>
                        <h4>2. Search for Lost Items</h4>
                        <p>Lost something? Browse our database or use advanced search filters.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                        <h4>3. Verify & Claim</h4>
                        <p>Found your item? Submit a claim with proof of ownership for verification.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="content-section">
            <h2>Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>January 2025</h4>
                        <p>Project concept developed at Cotabato State University</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>March 2025</h4>
                        <p>First prototype developed and tested</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>June 2025</h4>
                        <p>Official launch of LFIS with 50 initial items</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>September 2025</h4>
                        <p>Reached 1000+ registered users and 500+ items</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>December 2025</h4>
                        <p>Successfully reunited 300+ items with owners</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="uploads/items/prof.png" 
                         alt="Team Member" class="team-img">
                    <div class="team-info">
                        <h4>Said Hosni Abbet Polao</h4>
                        <p>Project Lead & Developer</p>
                        <p class="text-muted">Lead the development and implementation of LFIS</p>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="uploads/items/charneil.png" 
                         alt="Team Member" class="team-img">
                    <div class="team-info">
                        <h4>Charneil Partoza Portilla</h4>
                        <p>System Administrator</p>
                        <p class="text-muted">Manages system operations and user support</p>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="uploads/items/ramil.png" 
                         alt="Team Member" class="team-img">
                    <div class="team-info">
                        <h4>Ramil Akas Sampayan</h4>
                        <p>Database Administrator</p>
                        <p class="text-muted">Ensures data integrity and system performance</p>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" 
                         alt="Team Member" class="team-img">
                    <div class="team-info">
                        <h4>Maria Garcia</h4>
                        <p>Support Specialist</p>
                        <p class="text-muted">Handles user inquiries and claim verifications</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Values -->
        <div class="content-section">
            <h2>Our Core Values</h2>
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-shield-check text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Integrity</h5>
                            <p>We operate with honesty and transparency in all our processes, ensuring trust between finders and owners.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Community</h5>
                            <p>We believe in the power of community to help each other and make our campuses and cities safer places.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-lightbulb text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Innovation</h5>
                            <p>We continuously improve our platform with new features and technologies to serve our users better.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-heart text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Compassion</h5>
                            <p>We understand the emotional value of lost items and handle each case with care and empathy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="content-section text-center" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white;">
            <h2 style="color: #FFFFFF;">Join Our Mission</h2>
            <p class="lead mb-4">Help us make a difference by reporting found items or volunteering with our team.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="post-item.php" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i> Report Found Item
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-envelope me-2"></i> Contact Us
                </a>
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