<?php
// Check if we're included from categories.php
if(!isset($conn) || !isset($category) || !isset($item_count) || !isset($other_categories)) {
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
                Delete Category: <?php echo htmlspecialchars($category['name']); ?>
            </span>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-danger">Delete Category</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Warning!</h6>
                    <p class="mb-0">
                        This category contains <strong><?php echo $item_count; ?></strong> item(s). 
                        Please choose what to do with these items before deleting the category.
                    </p>
                </div>
                
                <form method="POST">
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action_type" id="reassign" value="reassign" checked>
                            <label class="form-check-label" for="reassign">
                                <strong>Reassign items to another category</strong>
                                <p class="text-muted mb-0">Move all items in this category to a different category</p>
                            </label>
                            <div class="mt-2 ms-4">
                                <select class="form-select" name="new_category" required>
                                    <option value="">Select new category</option>
                                    <?php foreach($other_categories as $other_cat): ?>
                                        <option value="<?php echo $other_cat['id']; ?>">
                                            <?php echo htmlspecialchars($other_cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="radio" name="action_type" id="force" value="force">
                            <label class="form-check-label" for="force">
                                <strong>Delete all items in this category</strong>
                                <p class="text-muted mb-0">Permanently delete all items associated with this category</p>
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-octagon"></i> This action cannot be undone!</h6>
                        <p class="mb-0">Please make sure you have selected the correct option.</p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">Delete Category</button>
                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>