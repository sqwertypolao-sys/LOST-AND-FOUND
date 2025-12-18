<?php
require_once 'includes/config.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$item_type   = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$search      = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Build WHERE clause
$where = "WHERE i.status = 'published'";
$params = [];

if ($category_id > 0) {
    $where .= " AND i.category_id = ?";
    $params[] = $category_id;
}

if (in_array($item_type, ['lost', 'found'])) {
    $where .= " AND i.item_type = ?";
    $params[] = $item_type;
}

if (!empty($search)) {
    $where .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location_found LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Count total items
$count_sql = "SELECT COUNT(*) as total FROM items i $where";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get paginated items
$sql = "SELECT i.*, c.name AS category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        $where
        ORDER BY i.created_at DESC
        LIMIT $items_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost and Found Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #F57C00;
            --primary-dark: #E65100;
            --dark-gray: #2E2E2E;
            --light-gray: #EEEEEE;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            padding-top: 56px;
        }

        .navbar, .footer {
            background-color: var(--dark-gray) !important;
        }

        .navbar-brand {
            color: var(--primary) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--white) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary) !important;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 8px 24px;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .bg-primary {
            background-color: var(--primary) !important;
        }

        .bg-secondary {
            background-color: var(--dark-gray) !important;
            color: var(--white);
        }

        .footer {
            background-color: var(--dark-gray);
            color: var(--light-gray);
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer a {
            color: var(--light-gray);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between mb-4">
        <h1>Lost & Found Items</h1>
        <a href="post-item.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Report Item
        </a>
    </div>

    <!-- FILTERS -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label>Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id']; ?>" <?= ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Item Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="lost" <?= ($item_type == 'lost') ? 'selected' : ''; ?>>Lost</option>
                        <option value="found" <?= ($item_type == 'found') ? 'selected' : ''; ?>>Found</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search); ?>">
                        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ITEMS -->
    <div class="row">
        <?php if ($items): foreach ($items as $item): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <?php if ($item['image_url']): ?>
                        <img src="uploads/items/<?= $item['image_url']; ?>" class="card-img-top">
                    <?php else: ?>
                        <div class="bg-light d-flex justify-content-center align-items-center" style="height:200px;">
                            <i class="bi bi-image fs-1 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['title']); ?></h5>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($item['location_found']); ?>
                        </small>
                        <p class="card-text"><?= substr(htmlspecialchars($item['description']), 0, 90); ?>...</p>
                        <span class="badge bg-primary"><?= $item['category_name']; ?></span>
                        <span class="badge bg-secondary"><?= ucfirst($item['item_type']); ?></span>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between">
                        <small><?= date('M d, Y', strtotime($item['date_found'])); ?></small>
                        <a href="item-details.php?id=<?= $item['id']; ?>" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="text-center py-5">
                <h4>No items found</h4>
            </div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
