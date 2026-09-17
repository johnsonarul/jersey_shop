<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();
include 'header.php';

$user_id = $_SESSION['user_id'];

// Handle Add/Remove
if (isset($_GET['add'])) {
    $pid = intval($_GET['add']);
    // Check if exists
    $chk = $conn->query("SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $pid");
    if($chk->num_rows == 0) {
        $conn->query("INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $pid)");
    }
    echo "<script>window.location.href='wishlist.php';</script>";
    exit();
}
if (isset($_GET['remove'])) {
    $pid = intval($_GET['remove']);
    $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $pid");
    echo "<script>window.location.href='wishlist.php';</script>";
    exit();
}

$res = $conn->query("SELECT w.product_id, p.product_name, p.price, p.image, p.status FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = $user_id ORDER BY w.id DESC");
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="account.php" class="list-group-item list-group-item-action">My Profile</a>
                <a href="orders.php" class="list-group-item list-group-item-action">My Orders</a>
                <a href="wishlist.php" class="list-group-item list-group-item-action active bg-danger border-danger">Wishlist</a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger font-weight-bold">Logout</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <h3 class="mb-4">My Wishlist</h3>
            
            <?php if($res->num_rows == 0): ?>
                <div class="alert alert-info">No products in your wishlist.</div>
            <?php else: ?>
                <div class="row">
                    <?php while($row = $res->fetch_assoc()): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card h-100 text-center pb-3">
                                <a href="product.php?id=<?= $row['product_id'] ?>" class="text-decoration-none text-dark">
                                    <div class="position-relative">
                                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" class="product-image">
                                        <?php if($row['status'] == 'sold_out'): ?>
                                            <span class="sold-out-badge">SOLD OUT</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-details">
                                        <div class="product-title"><?= htmlspecialchars($row['product_name']) ?></div>
                                        <div class="product-price mb-3">₹<?= $row['price'] ?></div>
                                    </div>
                                </a>
                                <div>
                                    <a href="product.php?id=<?= $row['product_id'] ?>" class="btn btn-custom btn-sm">VIEW</a>
                                    <a href="wishlist.php?remove=<?= $row['product_id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
