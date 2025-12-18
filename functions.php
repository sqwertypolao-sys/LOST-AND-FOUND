<?php
// Upload file function
function uploadFile($file, $directory = 'items/') {
    $target_dir = UPLOAD_PATH . $directory;
    $file_name = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file is an actual image
    $check = getimagesize($file['tmp_name']);
    if($check === false) {
        return ['success' => false, 'error' => 'File is not an image.'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File is too large.'];
    }
    
    // Allow certain file formats
    $allowed_formats = ['jpg', 'jpeg', 'png', 'gif'];
    if(!in_array($imageFileType, $allowed_formats)) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'file_name' => $file_name];
    } else {
        return ['success' => false, 'error' => 'Sorry, there was an error uploading your file.'];
    }
}

// Delete file function
function deleteFile($filename, $directory = 'items/') {
    $file_path = UPLOAD_PATH . $directory . $filename;
    if (file_exists($file_path)) {
        unlink($file_path);
        return true;
    }
    return false;
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Get item status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'published' => '<span class="badge bg-success">Published</span>',
        'claimed' => '<span class="badge bg-info">Claimed</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

// Get category badge
function getCategoryBadge($category_id, $conn) {
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    if ($category) {
        return '<span class="badge bg-primary">' . $category['name'] . '</span>';
    }
    return '<span class="badge bg-secondary">Unknown</span>';
}

// Generate pagination
function generatePagination($total_items, $items_per_page, $current_page, $url) {
    $total_pages = ceil($total_items / $items_per_page);
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination .= '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($current_page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($current_page - 1) . '">&laquo; Previous</a></li>';
        }
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $current_page) ? ' active' : '';
            $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($current_page + 1) . '">Next &raquo;</a></li>';
        }
        
        $pagination .= '</ul></nav>';
    }
    
    return $pagination;
}

// Send email notification
function sendEmail($to, $subject, $message) {
    $headers = "From: " . getSetting('system_email', $GLOBALS['conn']) . "\r\n";
    $headers .= "Reply-To: " . getSetting('system_email', $GLOBALS['conn']) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

?>