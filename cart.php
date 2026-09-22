<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();
include 'header.php';

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT c.*, p.product_name, p.price, p.image, ps.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id JOIN product_sizes ps ON c.product_id = ps.product_id AND c.size = ps.size WHERE c.user_id = $user_id");

$subtotal = 0;
$cart_items = [];
while($row = $res->fetch_assoc()){
    $cart_items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

$shipping = ($subtotal == 0) ? 0 : 50;
$discount = 0;

if (isset($_SESSION['reward_active']) && $_SESSION['reward_active']) {
    $max_price = 0;
    foreach($cart_items as $item) {
        if ($item['price'] > $max_price) $max_price = $item['price'];
    }
    $discount = $max_price;
    $subtotal -= $discount;
    if ($subtotal < 0) $subtotal = 0;
}

$total = $subtotal + $shipping;
?>

<div class="container py-5">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-5 border border-secondary rounded" style="background-color: var(--bg-card); color: var(--text-main);">
            <h4 class="mb-3">Your cart is empty</h4>
            <a href="products.php" class="btn btn-custom px-4 py-2">CONTINUE SHOPPING</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle" style="background-color: transparent;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="img" width="60" class="me-3 rounded">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                <?php if($item['quantity'] > $item['stock_quantity']): ?>
                                                    <span class="text-danger small">Only <?= $item['stock_quantity'] ?> left in stock!</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $item['size'] ?></td>
                                    <td>₹<?= $item['price'] ?></td>
                                    <td>
                                        <form action="update_cart.php" method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                            <div class="quantity-selector" style="max-width: 100px;">
                                                <button type="button" class="qty-minus" onclick="this.nextElementSibling.stepDown(); this.form.submit();">-</button>
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" readonly>
                                                <button type="button" class="qty-plus" onclick="this.previousElementSibling.stepUp(); this.form.submit();">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>₹<?= $item['price'] * $item['quantity'] ?></td>
                                    <td>
                                        <a href="remove_from_cart.php?id=<?= $item['id'] ?>" style="color: var(--primary-color);"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="products.php" class="btn btn-custom mt-3">CONTINUE SHOPPING</a>
            </div>
            
            <div class="col-md-4 mt-4 mt-md-0">
                <div class="card bg-card border border-secondary" style="background-color: var(--bg-card); color: var(--text-main);">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Cart Summary</h4>
                        <div class="d-flex justify-content-between mb-2 text-secondary">
                            <span>Subtotal</span>
                            <span>₹<?= number_format($subtotal + $discount, 2) ?></span>
                        </div>
                        <?php if($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Free Jersey Reward</span>
                            <span>-₹<?= number_format($discount, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-3 text-secondary">
                            <span>Shipping</span>
                            <span>₹<?= number_format($shipping, 2) ?></span>
                        </div>
                        <hr class="border-secondary">
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Grand Total</strong>
                            <strong class="fs-4" style="color: var(--primary-color);">₹<?= number_format($total, 2) ?></strong>
                        </div>
                        <a href="checkout.php" class="btn btn-custom w-100 py-3 fs-5">PROCEED TO CHECKOUT</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
