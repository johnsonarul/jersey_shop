<?php
require_once '../db.php';
include 'header.php';

// Handle Enable / Disable
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    if ($action == 'disable') {
        $conn->query("UPDATE users SET status = 'inactive' WHERE id = $id");
    } elseif ($action == 'enable') {
        $conn->query("UPDATE users SET status = 'active' WHERE id = $id");
    }
    header("Location: users.php");
    exit();
}

$res = $conn->query("SELECT u.*, COUNT(o.id) as total_orders, SUM(o.total_amount) as total_spent FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.id DESC");
?>

<h2 class="mb-4">Customers</h2>

<div class="table-responsive bg-card p-3 rounded shadow-sm">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email & Mobile</th>
                <th>Registered Date</th>
                <th>Orders</th>
                <th>Total Spent</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?> <br> <small class="text-muted"><?= htmlspecialchars($row['mobile']) ?></small></td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td><?= $row['total_orders'] ?></td>
                <td>₹<?= $row['total_spent'] ?? 0 ?></td>
                <td>
                    <?php if($row['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Disabled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['status'] == 'active'): ?>
                        <a href="users.php?action=disable&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Disable this customer?');">Disable</a>
                    <?php else: ?>
                        <a href="users.php?action=enable&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">Enable</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
