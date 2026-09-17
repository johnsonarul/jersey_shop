<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$msg = '';

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE categories SET status = IF(status='active', 'inactive', 'active') WHERE id = $id");
    $msg = "<div class='alert alert-success'>Category status updated!</div>";
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    $msg = "<div class='alert alert-success'>Category deleted!</div>";
}

$res = $conn->query("SELECT * FROM categories ORDER BY id DESC");

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Categories</h2>
    <a href="add_category.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
</div>

<?= $msg ?>

<div class="bg-card rounded shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle text-center">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res->num_rows > 0): ?>
                    <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="rounded" style="height: 50px; width: 50px; object-fit: cover;">
                            </td>
                            <td class="font-weight-bold">
                                <?= htmlspecialchars($row['name']) ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="categories.php?toggle=<?= $row['id'] ?>" class="btn btn-sm <?= $row['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> me-2">
                                    <i class="fas <?= $row['status'] == 'active' ? 'fa-eye-slash' : 'fa-eye' ?>"></i> Toggle
                                </a>
                                <a href="categories.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="py-4 text-muted">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
