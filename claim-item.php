<?php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

if($id <= 0) {
    header("Location: lost-and-found.php");
    exit();
}

// Get item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND status = 'published' AND item_type = 'found'");
$stmt->execute([$id]);
$item = $stmt->fetch();

if(!$item) {
    header("Location: lost-and-found.php");
    exit();
}

// Handle claim submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $claimant_name = sanitize($_POST['claimant_name']);
    $claimant_contact = sanitize($_POST['claimant_contact']);
    $claimant_email = sanitize($_POST['claimant_email']);
    $claim_details = sanitize($_POST['claim_details']);
    $proof_of_ownership = sanitize($_POST['proof_of_ownership']);
    
    if(empty($claimant_name) || empty($claimant_email)) {
        $error = "Please fill in all required fields.";
    } else {
        // Update item status and claim information
        $sql = "UPDATE items SET 
                claimant_name = ?, 
                claimant_contact = ?, 
                claimant_email = ?, 
                claim_details = ?, 
                proof_of_ownership = ?,
                status = 'claimed',
                claim_date = CURDATE()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$claimant_name, $claimant_contact, $claimant_email, $claim_details, $proof_of_ownership, $id])) {
            $success = "Claim submitted successfully! Our staff will contact you to verify your claim.";
        } else {
            $error = "Failed to submit claim. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item - LFIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #F57C00;
            --dark-gray: #2E2E2E;
            --light-gray: #EEEEEE;
        }
        body { font-family: Arial, sans-serif; background: var(--light-gray); padding-top: 56px; }
        .navbar { background: var(--dark-gray); }
        .footer { background: var(--dark-gray); color: var(--light-gray); }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-check-circle me-2"></i> Claim Item</h4>
                    </div>
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                            <div class="text-center">
                                <a href="item-details.php?id=<?php echo $id; ?>" class="btn btn-primary me-2">Back to Item</a>
                                <a href="lost-and-found.php" class="btn btn-outline-primary">Browse More Items</a>
                            </div>
                        <?php else: ?>
                            <!-- Item Info -->
                            <div class="alert alert-info mb-4">
                                <h5>Claiming: <?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="mb-1"><strong>Description:</strong> <?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                                <p class="mb-0"><strong>Found on:</strong> <?php echo formatDate($item['date_found']); ?> at <?php echo htmlspecialchars($item['location_found']); ?></p>
                            </div>
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Your Full Name *</label>
                                    <input type="text" name="claimant_name" class="form-control" required>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="text" name="claimant_contact" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" name="claimant_email" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Proof of Ownership *</label>
                                    <textarea name="proof_of_ownership" class="form-control" rows="3" required 
                                              placeholder="Describe how you can prove this item belongs to you (e.g., serial number, unique features, etc.)"></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Additional Details</label>
                                    <textarea name="claim_details" class="form-control" rows="3" 
                                              placeholder="Any additional information about your claim"></textarea>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="bi bi-exclamation-triangle"></i> Important Notice</h6>
                                    <p class="mb-0">All claims will be verified by our staff. You may be required to provide additional proof of ownership. False claims may result in penalties.</p>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i> Submit Claim
                                    </button>
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