<?php
require_once '../db.php';
include 'header.php';

// Handle Mark Available / Sold Out / Delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'sold_out') {
        $conn->query("UPDATE products SET status = 'sold_out' WHERE id = $id");
    } elseif ($action == 'available') {
        // Also check if stock is > 0 overall
        $conn->query("UPDATE products SET status = 'available' WHERE id = $id");
    } elseif ($action == 'delete') {
        // Soft delete (inactive)
        $conn->query("UPDATE products SET status = 'inactive' WHERE id = $id");
    }
    header("Location: products.php");
    exit();
}

$res = $conn->query("SELECT p.*, (SELECT SUM(stock_quantity) FROM product_sizes WHERE product_id = p.id) as total_stock FROM products p WHERE p.status != 'inactive' ORDER BY p.id DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Products</h2>
    <a href="add_product.php" class="btn btn-primary">+ ADD NEW JERSEY</a>
</div>

<div class="table-responsive bg-card p-3 rounded shadow-sm">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><img src="../<?= htmlspecialchars($row['image']) ?>" alt="Img" width="50" height="50" style="object-fit:cover; border-radius:4px;"></td>
                <td><?= htmlspecialchars($row['product_name']) ?> <br> <small class="text-muted"><?= htmlspecialchars($row['model']) ?></small></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td>₹<?= $row['price'] ?></td>
                <td><?= $row['total_stock'] ?? 0 ?></td>
                <td>
                    <?php if($row['status'] == 'available'): ?>
                        <span class="badge bg-success">Available</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Sold Out</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">EDIT</a>
                    
                    <?php if($row['status'] == 'available'): ?>
                        <a href="products.php?action=sold_out&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">MARK SOLD OUT</a>
                    <?php else: ?>
                        <a href="products.php?action=available&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">MARK AVAILABLE</a>
                    <?php endif; ?>
                    
                    <a href="products.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this product?');">DELETE</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
