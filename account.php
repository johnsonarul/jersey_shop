<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();
include 'header.php';

$user_id = $_SESSION['user_id'];
$u_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

// Get recent orders
$o_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$o_stmt->bind_param("i", $user_id);
$o_stmt->execute();
$orders = $o_stmt->get_result();
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="account.php" class="list-group-item list-group-item-action active bg-danger border-danger">My Profile</a>
                <a href="orders.php" class="list-group-item list-group-item-action">My Orders</a>
                <a href="wishlist.php" class="list-group-item list-group-item-action">Wishlist</a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger font-weight-bold">Logout</a>
            </div>
        </div>
        
        <!-- Content -->
        <div class="col-md-9">
            <h3 class="mb-4">Welcome, <?= htmlspecialchars($user['full_name']) ?></h3>
            
            <?php if(isset($_SESSION['quiz_msg'])) { echo $_SESSION['quiz_msg']; unset($_SESSION['quiz_msg']); } ?>
            
            <div class="card bg-card border border-primary mb-4 p-4 shadow-sm" style="background-color: rgba(255,51,102,0.1);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" style="color: var(--primary-color);"><i class="fas fa-trophy"></i> Sports Quiz Rewards</h5>
                    <span class="badge bg-warning text-dark fs-6" style="color: #000 !important;"><?= $user['quiz_points'] ?> / 10 Points</span>
                </div>
                <div class="progress mb-3" style="height: 20px; background-color: rgba(255,255,255,0.1);">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, $user['quiz_points'] * 10) ?>%;"></div>
                </div>
                <?php if ($user['quiz_points'] >= 10): ?>
                    <p class="text-white mb-3">Congratulations! You have earned a FREE Jersey.</p>
                    <?php if (isset($_SESSION['reward_active']) && $_SESSION['reward_active']): ?>
                        <div class="alert alert-success">Reward is active! The most expensive jersey in your next order will be free.</div>
                    <?php else: ?>
                        <a href="activate_reward.php" class="btn btn-primary px-4 py-2" style="border-radius: 30px; background: var(--primary-color); border: none;">Activate Free Jersey Pass</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-white mb-0" style="color: #cccccc !important; font-size: 0.9rem;">Place an order to get a quiz question. Answer correctly in 10s to earn a point! Reach 10 points for a free jersey.</p>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white font-weight-bold">Personal Information</div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white font-weight-bold">Shipping Address</div>
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($user['address'])) ?></p>
                            <p><?= htmlspecialchars($user['city']) ?>, <?= htmlspecialchars($user['state']) ?> - <?= htmlspecialchars($user['pincode']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4 class="mt-4 mb-3">Recent Orders</h4>
            <?php if($orders->num_rows == 0): ?>
                <div class="alert alert-info">You haven't placed any orders yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                    <td>₹<?= $order['total_amount'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $order['order_status'] == 'Delivered' ? 'success' : ($order['order_status'] == 'Cancelled' ? 'danger' : 'warning text-dark') ?>">
                                            <?= $order['order_status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <a href="orders.php" class="btn btn-link">View All Orders</a>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
