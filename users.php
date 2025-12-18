<?php
require_once '../includes/config.php';
include __DIR__ . '/../includes/admin-header.php';
if(!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('index.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

switch($action) {
    case 'add':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $fullname = sanitize($_POST['fullname']);
            $email = sanitize($_POST['email']);
            $role = sanitize($_POST['role']);
            
            // Validate
            if(empty($username) || empty($password) || empty($fullname)) {
                $error = "Please fill in all required fields.";
            } elseif($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } else {
                // Check if username exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if($stmt->fetch()) {
                    $error = "Username already exists.";
                } else {
                    // Hash password and insert
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)");
                    if($stmt->execute([$username, $hashed_password, $fullname, $email, $role])) {
                        header("Location: users.php?success=User added successfully");
                        exit();
                    } else {
                        $error = "Failed to add user.";
                    }
                }
            }
        }
        include 'user-add.php';
        break;
        
    case 'edit':
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if(!$user) {
            header("Location: users.php");
            exit();
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = sanitize($_POST['username']);
            $fullname = sanitize($_POST['fullname']);
            $email = sanitize($_POST['email']);
            $role = sanitize($_POST['role']);
            $change_password = isset($_POST['change_password']) ? true : false;
            
            // Check if username changed and exists
            if($username != $user['username']) {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $id]);
                if($stmt->fetch()) {
                    $error = "Username already exists.";
                }
            }
            
            if(!isset($error)) {
                if($change_password && !empty($_POST['password'])) {
                    $password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];
                    
                    if($password !== $confirm_password) {
                        $error = "Passwords do not match.";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET username = ?, password = ?, fullname = ?, email = ?, role = ? WHERE id = ?";
                        $params = [$username, $hashed_password, $fullname, $email, $role, $id];
                    }
                } else {
                    $sql = "UPDATE users SET username = ?, fullname = ?, email = ?, role = ? WHERE id = ?";
                    $params = [$username, $fullname, $email, $role, $id];
                }
                
                if(!isset($error)) {
                    $stmt = $conn->prepare($sql);
                    if($stmt->execute($params)) {
                        header("Location: users.php?success=User updated successfully");
                        exit();
                    } else {
                        $error = "Failed to update user.";
                    }
                }
            }
        }
        include 'user-edit.php';
        break;
        
    case 'delete':
        if($id == $_SESSION['user_id']) {
            header("Location: users.php?error=Cannot delete your own account");
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if($stmt->execute([$id])) {
            header("Location: users.php?success=User deleted successfully");
        } else {
            header("Location: users.php?error=Failed to delete user");
        }
        exit();
        break;
        
    default:
        // List users
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
        
        $where = "1=1";
        $params = [];
        
        if(!empty($search)) {
            $where .= " AND (username LIKE ? OR fullname LIKE ? OR email LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if(in_array($role_filter, ['admin', 'staff'])) {
            $where .= " AND role = ?";
            $params[] = $role_filter;
        }
        
        $sql = "SELECT * FROM users WHERE $where ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        include 'user-list.php';
        break;
}
?>