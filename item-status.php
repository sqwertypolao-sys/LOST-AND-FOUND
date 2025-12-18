<?php
require_once '../includes/config.php';
include __DIR__ . '/../includes/admin-header.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if($id > 0 && in_array($status, ['pending', 'published', 'claimed'])) {
    $stmt = $conn->prepare("UPDATE items SET status = ? WHERE id = ?");
    if($stmt->execute([$status, $id])) {
        header("Location: items.php?success=Status updated successfully");
    } else {
        header("Location: items.php?error=Failed to update status");
    }
} else {
    header("Location: items.php");
}
exit();
?>