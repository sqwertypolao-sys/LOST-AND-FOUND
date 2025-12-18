<?php
// Check if we're included from categories.php
if(!isset($conn) || !isset($categories)) {
    die("Access denied.");
}
?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <span class="navbar-brand">Manage Categories</span>
            <a href="categories.php?action=add" class="btn btn-sm btn-primary">Add New</a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <!-- Success/Error Messages -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo ($status_filter ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($status_filter ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Categories Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($categories) > 0): ?>
                                <?php foreach($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo $cat['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo $cat['description'] ? htmlspecialchars(substr($cat['description'], 0, 50)) . (strlen($cat['description']) > 50 ? '...' : '') : '<span class="text-muted">No description</span>'; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $cat['item_count']; ?></span>
                                        </td>
                                        <td>
                                            <?php if($cat['status'] == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <?php if(isAdmin()): ?>
                                                    <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-folder text-muted" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No categories found</p>
                                        <a href="categories.php?action=add" class="btn btn-sm btn-primary mt-2">Add New Category</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>