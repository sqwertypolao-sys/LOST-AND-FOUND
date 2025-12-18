<?php
require_once '../includes/config.php';
include __DIR__ . '/../includes/admin-header.php';
if(!isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    redirect('messages.php');
}

// Get message details
$stmt = $conn->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch();

if(!$message) {
    redirect('messages.php');
}

// Mark as read
$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
$stmt->execute([$id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <span class="navbar-brand">View Message</span>
                <a href="messages.php" class="btn btn-sm btn-outline-primary">Back to Messages</a>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Message Details</h5>
                                <small class="text-muted"><?php echo formatDate($message['created_at']); ?></small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Sender Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($message['fullname']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?></p>
                                    <?php if($message['contact_no']): ?>
                                        <p class="mb-0"><strong>Contact:</strong> <?php echo htmlspecialchars($message['contact_no']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h6>Message Status</h6>
                                    <?php if($message['is_read']): ?>
                                        <span class="badge bg-success">Read</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Unread</span>
                                    <?php endif; ?>
                                    
                                    <?php if($message['replied']): ?>
                                        <span class="badge bg-info ms-2">Replied</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Message</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                        <i class="bi bi-reply"></i> Reply via Email
                                    </a>
                                    <?php if(!$message['replied']): ?>
                                        <a href="?mark_replied=<?php echo $message['id']; ?>" class="btn btn-outline-success ms-2">
                                            <i class="bi bi-check"></i> Mark as Replied
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <a href="messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-outline-danger confirm-delete">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>