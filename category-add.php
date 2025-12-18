<?php
// categories.php - admin category management
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin-stats.php';

// Require login
if(!isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Use centralized stats
$stats = $GLOBALS['stats'];

// Include header/sidebar with proper path checking
$admin_header_path = __DIR__ . '/../includes/admin-header.php';
if(file_exists($admin_header_path)) {
    include $admin_header_path;
} else {
    // Try relative path
    include 'includes/admin-header.php';
}

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

switch($action) {
    case 'add':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $status = sanitize($_POST['status']);
            
            if(empty($name)) {
                $error = "Category name is required.";
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
                if($stmt->execute([$name, $description, $status])) {
                    $message = "Category added successfully!";
                    header("Location: categories.php?success=" . urlencode($message));
                    exit();
                } else {
                    $error = "Failed to add category.";
                }
            }
        }
        include 'category-add.php';
        break;
        
    case 'edit':
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        
        if(!$category) {
            header("Location: categories.php");
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $status = sanitize($_POST['status']);
            
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?");
            if($stmt->execute([$name, $description, $status, $id])) {
                $message = "Category updated successfully!";
                header("Location: categories.php?success=" . urlencode($message));
                exit();
            } else {
                $error = "Failed to update category.";
            }
        }
        include 'category-edit.php';
        break;

    case 'delete':
        if(isAdmin()) {
            // Get category info
            $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch();

            if(!$category) {
                header("Location: categories.php?error=" . urlencode("Category not found."));
                exit();
            }

            // Count items in this category
            $stmt = $conn->prepare("SELECT COUNT(*) FROM items WHERE category_id = ?");
            $stmt->execute([$id]);
            $item_count = (int)$stmt->fetchColumn();

            if($item_count > 0) {
                if(isset($_POST['action_type'])) {
                    $action_type = $_POST['action_type'];

                    if($action_type == 'reassign') {
                        $new_category_id = intval($_POST['new_category']);
                        $stmt = $conn->prepare("UPDATE items SET category_id = ? WHERE category_id = ?");
                        $stmt->execute([$new_category_id, $id]);

                        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                        if($stmt->execute([$id])) {
                            $message = "Category deleted and items reassigned successfully!";
                            header("Location: categories.php?success=" . urlencode($message));
                            exit();
                        }

                    } elseif($action_type == 'force') {
                        $stmt = $conn->prepare("DELETE FROM items WHERE category_id = ?");
                        $stmt->execute([$id]);

                        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                        if($stmt->execute([$id])) {
                            $message = "Category and its items deleted successfully!";
                            header("Location: categories.php?success=" . urlencode($message));
                            exit();
                        }
                    }
                } else {
                    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE id != ? ORDER BY name");
                    $stmt->execute([$id]);
                    $other_categories = $stmt->fetchAll();
                    include 'category-delete-options.php';
                    exit();
                }

            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                if($stmt->execute([$id])) {
                    $message = "Category deleted successfully!";
                    header("Location: categories.php?success=" . urlencode($message));
                    exit();
                } else {
                    $error = "Failed to delete category.";
                }
            }

        } else {
            $error = "You don't have permission to delete categories.";
        }

        header("Location: categories.php?" . ($error ? "error=" . urlencode($error) : "success=" . urlencode($message)));
        exit();
    break;

    default:
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        
        $where = "1=1";
        $params = [];
        
        if(!empty($search)) {
            $where .= " AND (name LIKE ? OR description LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if(in_array($status_filter, ['active', 'inactive'])) {
            $where .= " AND status = ?";
            $params[] = $status_filter;
        }
        
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM items WHERE category_id = c.id) as item_count
                FROM categories c 
                WHERE $where 
                ORDER BY name";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();
        
        include 'category-list.php';
        break;
}
?>