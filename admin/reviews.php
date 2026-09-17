<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$msg = '';

if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM reviews WHERE id = $del_id");
    $msg = "<div class='alert alert-success'>Review deleted successfully.</div>";
}

$rev_res = $conn->query("SELECT r.*, u.full_name, u.email, p.product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC");

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customer Reviews</h2>
</div>

<?= $msg ?>

<div class="bg-card rounded shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle text-center">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th class="text-start">Review Text</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($rev_res->num_rows > 0): ?>
                    <?php while($row = $rev_res->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?= htmlspecialchars($row['full_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td>
                                <a href="../product.php?id=<?= $row['product_id'] ?>" target="_blank" class="text-info text-decoration-none">
                                    <?= htmlspecialchars($row['product_name']) ?>
                                </a>
                            </td>
                            <td class="text-warning">
                                <?= str_repeat('<i class="fas fa-star"></i>', $row['rating']) ?>
                            </td>
                            <td class="text-start text-wrap" style="max-width: 300px;">
                                <?= nl2br(htmlspecialchars($row['review_text'])) ?>
                            </td>
                            <td>
                                <a href="reviews.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this review?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="py-4 text-muted">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
