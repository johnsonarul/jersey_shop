<?php
require_once '../db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $note = trim($_POST['note']);
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if($stmt->execute()) {
        $t_stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, note) VALUES (?, ?, ?)");
        $t_stmt->bind_param("iss", $order_id, $status, $note);
        $t_stmt->execute();
    }
    header("Location: orders.php");
    exit();
}

$status_filter = $_GET['status'] ?? '';
$where = "";
if($status_filter) {
    $where = "WHERE order_status = '" . $conn->real_escape_string($status_filter) . "'";
}

$res = $conn->query("SELECT * FROM orders $where ORDER BY id DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Orders Management</h2>
    
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Filter: <?= $status_filter ? $status_filter : 'All' ?>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="orders.php">All</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Pending">Pending</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Confirmed">Confirmed</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Packed">Packed</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Shipped">Shipped</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Delivered">Delivered</a></li>
            <li><a class="dropdown-item" href="orders.php?status=Cancelled">Cancelled</a></li>
        </ul>
    </div>
</div>

<div class="table-responsive bg-card p-3 rounded shadow-sm">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['order_number']) ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['shipping_name']) ?></strong><br>
                    <small class="text-muted"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($row['shipping_phone']) ?></small><br>
                    <small class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['shipping_email']) ?></small>
                </td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td>₹<?= $row['total_amount'] ?></td>
                <td>
                    <?= $row['payment_method'] ?> <br> 
                    <small class="text-muted"><?= $row['payment_status'] ?></small>
                    <?php if(!empty($row['transaction_id'])): ?>
                        <br><small class="text-info border border-info px-1 rounded">Txn: <?= htmlspecialchars($row['transaction_id']) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-<?= $row['order_status'] == 'Delivered' ? 'success' : ($row['order_status'] == 'Cancelled' ? 'danger' : 'warning text-dark') ?>">
                        <?= $row['order_status'] ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal<?= $row['id'] ?>">UPDATE</button>
                    <!-- Modal for Update -->
                    <div class="modal fade" id="updateModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Order #<?= $row['order_number'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="status" class="form-select">
                                                <?php
                                                $statuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled'];
                                                foreach($statuses as $st) {
                                                    $sel = ($row['order_status'] == $st) ? 'selected' : '';
                                                    echo "<option value='$st' $sel>$st</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Note to Customer</label>
                                            <textarea name="note" class="form-control" rows="2">Order status updated.</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary w-100">SAVE CHANGES</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
