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

$res = $conn->query("SELECT c.*, p.product_name, p.price, ps.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id JOIN product_sizes ps ON c.product_id = ps.product_id AND c.size = ps.size WHERE c.user_id = $user_id");

$subtotal = 0;
$cart_items = [];
while($row = $res->fetch_assoc()){
    if($row['quantity'] > $row['stock_quantity']){
        // Stock reduced since they added to cart!
        echo "<script>alert('Some items in your cart exceed available stock. Please adjust quantities.'); window.location.href='cart.php';</script>";
        exit();
    }
    $cart_items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

if(empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$shipping = ($subtotal > 1500) ? 0 : 80;
$total = $subtotal + $shipping;
?>

<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    <form action="place_order.php" method="POST">
        <div class="row">
            <!-- Shipping Details -->
            <div class="col-md-7 mb-4">
                <div class="card bg-card border border-secondary" style="background-color: var(--bg-card); color: var(--text-main);">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Shipping Details</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mobile *</label>
                                <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($user['mobile']) ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Address *</label>
                                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>City *</label>
                                <input type="text" name="city" class="form-control" required value="<?= htmlspecialchars($user['city']) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>State *</label>
                                <input type="text" name="state" class="form-control" required value="<?= htmlspecialchars($user['state']) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Pincode *</label>
                                <input type="text" name="pincode" class="form-control" required value="<?= htmlspecialchars($user['pincode']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-md-5">
                <div class="card bg-card border border-secondary" style="background-color: var(--bg-card); color: var(--text-main);">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Your Order</h4>
                        
                        <?php foreach($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                    <small class="text-muted">Size: <?= $item['size'] ?> | Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <span>₹<?= $item['price'] * $item['quantity'] ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between mt-4 mb-2">
                            <span>Subtotal</span>
                            <span>₹<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                            <span>Shipping</span>
                            <span><?= $shipping == 0 ? 'FREE' : '₹'.number_format($shipping, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-4" style="color: var(--primary-color);">₹<?= number_format($total, 2) ?></strong>
                        </div>
                        
                        <h5 class="mb-3">Payment Method</h5>
                        <div class="form-check mb-3 border border-secondary p-3 rounded">
                            <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="upi" value="UPI" checked>
                            <label class="form-check-label font-weight-bold" for="upi">
                                UPI / GPay
                            </label>
                        </div>
                        
                        <!-- UPI Details Box -->
                        <div id="upi-details" class="mb-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--primary-color);">
                            <h6 style="color: var(--primary-color);">Pay via UPI / GPay</h6>
                            <p class="small mb-3">Please send the exact amount <strong>₹<?= number_format($total, 2) ?></strong> to:</p>
                            
                            <div class="d-flex flex-wrap align-items-center mb-3">
                                <div class="me-4 mb-3 mb-md-0 bg-white p-2 rounded d-inline-block">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=<?= urlencode('upi://pay?pa=john9791649247@okaxis&pn=Johnson arul&cu=INR&am=' . $total) ?>" alt="UPI QR Code" width="130" height="130">
                                </div>
                                <div>
                                    <div class="mb-2"><strong>Name:</strong> Johnson arul</div>
                                    <div class="mb-2"><strong>GPay Number:</strong> +91 9791649247</div>
                                    <div class="mb-2"><strong>UPI ID:</strong> john9791649247@okaxis</div>
                                </div>
                            </div>
                            
                            <p class="small text-warning mb-2">After successful payment, enter your Transaction ID below:</p>
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="e.g. 123456789012" required>
                        </div>
                        
                        <button type="submit" class="btn btn-custom w-100 py-3 fs-5">PLACE ORDER</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



<?php include 'footer.php'; ?>
