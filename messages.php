<?php
require_once '../includes/config.php';
include __DIR__ . '/../includes/admin-header.php';

if(!isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 15;

// Build query
$where = "1=1";
$params = [];

if($status_filter == 'unread') {
    $where .= " AND is_read = 0";
} elseif($status_filter == 'read') {
    $where .= " AND is_read = 1";
}

if(!empty($search)) {
    $where .= " AND (fullname LIKE ? OR email LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Get total messages
$count_sql = "SELECT COUNT(*) as total FROM messages WHERE $where";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_messages = $stmt->fetch()['total'];
$total_pages = ceil($total_messages / $items_per_page);

// Calculate offset
$offset = ($page - 1) * $items_per_page;

// ✅ FIXED QUERY (NO PARAMS FOR LIMIT & OFFSET)
$sql = "SELECT * FROM messages 
        WHERE $where 
        ORDER BY created_at DESC 
        LIMIT $items_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Mark message as read
if(isset($_GET['mark_read'])) {
    $msg_id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$msg_id]);
    header("Location: messages.php");
    exit();
}

// Mark message as unread
if(isset($_GET['mark_unread'])) {
    $msg_id = intval($_GET['mark_unread']);
    $stmt = $conn->prepare("UPDATE messages SET is_read = 0 WHERE id = ?");
    $stmt->execute([$msg_id]);
    header("Location: messages.php");
    exit();
}

// Delete message
if(isset($_GET['delete'])) {
    $msg_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$msg_id]);
    header("Location: messages.php?success=Message deleted successfully");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - LFIS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <span class="navbar-brand">Manage Messages</span>
            </div>
        </nav>

        <div class="container-fluid mt-4">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search messages..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Messages</option>
                                <option value="unread" <?php echo $status_filter == 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                                <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php 
            $stmt = $conn->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
            $unread_count = $stmt->fetchColumn();
            ?>

            <p>
                Showing <?php echo count($messages); ?> of <?php echo $total_messages; ?> messages
                <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?> unread</span>
                <?php endif; ?>
            </p>

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sender</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($messages) > 0): ?>
                            <?php foreach($messages as $msg): ?>
                                <tr class="<?php echo !$msg['is_read'] ? 'table-warning' : ''; ?>">
                                    <td><?php echo $msg['id']; ?></td>
                                    <td><?php echo htmlspecialchars($msg['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...</td>
                                    <td><?php echo formatDate($msg['created_at']); ?></td>
                                    <td>
                                        <?php echo $msg['is_read']
                                            ? '<span class="badge bg-success">Read</span>'
                                            : '<span class="badge bg-warning">Unread</span>'; ?>
                                    </td>
                                    <td>
                                        <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-success">Read</a>
                                        <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No messages found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
