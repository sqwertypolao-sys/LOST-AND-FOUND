<?php
// Check if we're included from categories.php
if(!isset($conn) || !isset($category)) {
    die("Access denied.");
}
?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <a href="categories.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Back to Categories
            </a>
            <span class="navbar-text">
                Edit Category: <?php echo htmlspecialchars($category['name']); ?>
            </span>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <?php if(isset($error) && $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Category</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Category</button>
                                <a href="categories.php" class="btn btn-secondary">Cancel</a>
                                <?php if(isAdmin()): ?>
                                    <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-danger ms-auto" onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete Category
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>