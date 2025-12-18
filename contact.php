<?php
require_once 'includes/config.php';

// Get contact information from system settings
$contact_info = [];
$stmt = $conn->query("SELECT setting_key, setting_value FROM system_settings 
                     WHERE setting_key IN ('contact_address', 'contact_email', 'contact_phone', 'contact_mobile', 'office_hours', 'facebook_url', 'twitter_url', 'instagram_url')");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $contact_info[$row['setting_key']] = $row['setting_value'];
}

// Handle contact form submission
$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $contact_no = sanitize($_POST['contact_no']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validation
    if(empty($fullname) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Insert message into database
        $sql = "INSERT INTO messages (fullname, email, contact_no, subject, message) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$fullname, $email, $contact_no, $subject, $message])) {
            $success = "Thank you for your message! We will get back to you soon.";
            
            // Clear form
            $_POST = [];
        } else {
            $error = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}

// Get page content from database
$page_content = getPageContent('contact', $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Lost and Found Information System</title>
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
        
        /* Contact Cards */
        .contact-card {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .contact-card h3 {
            color: var(--dark-gray);
            margin-bottom: 15px;
        }
        
        /* Form Styles */
        .contact-form {
            background: var(--white);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(245, 124, 0, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-gray);
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        /* Map Container */
        .map-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 400px;
        }
        
        /* Social Links */
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-link:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
        }
        
        /* Contact Info List */
        .contact-info-list {
            list-style: none;
            padding: 0;
        }
        
        .contact-info-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: flex-start;
        }
        
        .contact-info-list li:last-child {
            border-bottom: none;
        }
        
        .contact-info-list i {
            color: var(--primary);
            margin-right: 15px;
            margin-top: 5px;
            width: 20px;
        }
        
        /* Office Hours */
        .office-hours {
            background: rgba(245, 124, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
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
            
            .contact-form {
                padding: 20px;
            }
            
            .contact-card {
                margin-bottom: 20px;
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact Us</a>
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
            <h1>Contact Us</h1>
            <p>Get in touch with us for any questions, concerns, or assistance with lost and found items</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Contact Information -->
        <div class="row mb-5">
            <div class="col-lg-4 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3>Our Location</h3>
                    <p><?php echo isset($contact_info['contact_address']) ? nl2br(htmlspecialchars($contact_info['contact_address'])) : 'Cotabato State University, CETC Building'; ?></p>
                    
                    <!-- Google Maps Embed -->
                    <div class="map-container mt-4">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1399.4535178706053!2d124.24493918781951!3d7.21203465357363!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32563a2b304c4815%3A0xfc02cb94d67488a2!2sCotabato%20State%20University!5e0!3m2!1sen!2sph!4v1765631856765!5m2!1sen!2sph" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            width="100%" 
                            height="200" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <h3>Contact Information</h3>
                    <ul class="contact-info-list">
                        <li>
                            <i class="bi bi-envelope"></i>
                            <div>
                                <strong>Email</strong><br>
                                <?php echo isset($contact_info['contact_email']) ? htmlspecialchars($contact_info['contact_email']) : 'LFIS@gmail.com'; ?>
                            </div>
                        </li>
                        <li>
                            <i class="bi bi-telephone-outbound"></i>
                            <div>
                                <strong>Telephone</strong><br>
                                <?php echo isset($contact_info['contact_phone']) ? htmlspecialchars($contact_info['contact_phone']) : '903-436-9357'; ?>
                            </div>
                        </li>
                        <li>
                            <i class="bi bi-phone"></i>
                            <div>
                                <strong>Mobile</strong><br>
                                <?php echo isset($contact_info['contact_mobile']) ? htmlspecialchars($contact_info['contact_mobile']) : '0900-000-0000'; ?>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="office-hours">
                        <h5><i class="bi bi-clock"></i> Office Hours</h5>
                        <?php if(isset($contact_info['office_hours'])): ?>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($contact_info['office_hours'])); ?></p>
                        <?php else: ?>
                            <p class="mb-0">Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="bi bi-share"></i>
                    </div>
                    <h3>Connect With Us</h3>
                    <p>Follow us on social media for updates and announcements:</p>
                    
                    <div class="social-links">
                        <?php if(isset($contact_info['facebook_url']) && !empty($contact_info['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($contact_info['facebook_url']); ?>" target="_blank" class="social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(isset($contact_info['twitter_url']) && !empty($contact_info['twitter_url'])): ?>
                            <a href="<?php echo htmlspecialchars($contact_info['twitter_url']); ?>" target="_blank" class="social-link">
                                <i class="bi bi-twitter"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(isset($contact_info['instagram_url']) && !empty($contact_info['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($contact_info['instagram_url']); ?>" target="_blank" class="social-link">
                                <i class="bi bi-instagram"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Quick Support</h5>
                        <p>For urgent matters, please call our hotline number above during office hours.</p>
                        <a href="lost-and-found.php" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Browse Lost Items
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="contact-form">
                    <h2 class="text-center mb-4">Send us a Message</h2>
                    <p class="text-center text-muted mb-4">Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Full Name</label>
                                <input type="text" name="fullname" class="form-control" 
                                       value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_no" class="form-control" 
                                       value="<?php echo isset($_POST['contact_no']) ? htmlspecialchars($_POST['contact_no']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" 
                                       value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>"
                                       placeholder="e.g., Lost Item Inquiry, General Question">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label required">Message</label>
                            <textarea name="message" class="form-control" rows="6" required
                                      placeholder="Please provide details about your inquiry..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i> Send Message
                            </button>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                We typically respond within 24-48 hours during business days.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-question-circle"></i> Frequently Asked Questions</h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        How long does it take to process a found item report?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Found items are typically reviewed and published within 24-48 hours during business days. Urgent items may be processed faster.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        What information do I need to claim a lost item?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You'll need to provide proof of ownership, which may include purchase receipts, photos, serial numbers, or detailed descriptions of unique features.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Is there a fee for using your lost and found service?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        No, our service is completely free for both reporting found items and claiming lost items. We operate as a community service.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        How long do you keep unclaimed items?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        We keep items for 90 days. If unclaimed after this period, items may be donated to charity or disposed of according to our policies.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Department -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Contact Specific Departments</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="bi bi-box-seam display-6 text-primary mb-3"></i>
                                    <h5>Lost & Found Department</h5>
                                    <p class="mb-1">For item inquiries and claims</p>
                                    <a href="mailto:lostfound@lfis.com" class="text-decoration-none">lostfound@lfis.com</a>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="bi bi-headset display-6 text-primary mb-3"></i>
                                    <h5>Technical Support</h5>
                                    <p class="mb-1">Website issues and technical help</p>
                                    <a href="mailto:support@lfis.com" class="text-decoration-none">support@lfis.com</a>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="bi bi-people display-6 text-primary mb-3"></i>
                                    <h5>Partnership & Sponsorship</h5>
                                    <p class="mb-1">For organizations and partnerships</p>
                                    <a href="mailto:partnership@lfis.com" class="text-decoration-none">partnership@lfis.com</a>
                                </div>
                            </div>
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
                    <p class="mt-3">Helping reunite lost items with their rightful owners since 2024.</p>
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
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> 
                            <?php echo isset($contact_info['contact_address']) ? htmlspecialchars($contact_info['contact_address']) : 'Cotabato State University, CETC Building'; ?>
                        </li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> 
                            <?php echo isset($contact_info['contact_email']) ? htmlspecialchars($contact_info['contact_email']) : 'LFIS@gmail.com'; ?>
                        </li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> 
                            <?php echo isset($contact_info['contact_phone']) ? htmlspecialchars($contact_info['contact_phone']) : '903-436-9357'; ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0">© Copyright 2024 Lost and Found Information System. All Rights Reserved<br>Template Designed by BootstrapMade</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('[required]');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
            
            // Remove invalid class when user starts typing
            requiredFields.forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>