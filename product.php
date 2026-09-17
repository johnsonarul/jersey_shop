<?php
require_once 'db.php';
include 'header.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status != 'inactive'");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    echo "<div class='container py-5 text-center'><h3>Product not found</h3></div>";
    include 'footer.php';
    exit();
}
$product = $res->fetch_assoc();

// Get sizes
$s_stmt = $conn->prepare("SELECT size, stock_quantity FROM product_sizes WHERE product_id = ?");
$s_stmt->bind_param("i", $id);
$s_stmt->execute();
$s_res = $s_stmt->get_result();

$has_purchased = false;
$msg = '';
if (isset($_SESSION['user_id'])) {
    $chk = $conn->query("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = {$_SESSION['user_id']} AND oi.product_id = {$id}");
    if ($chk->num_rows > 0) {
        $has_purchased = true;
    }
    
    if (isset($_POST['submit_review']) && $has_purchased) {
        $rating = intval($_POST['rating']);
        $review_text = htmlspecialchars($_POST['review_text']);
        $user_id = $_SESSION['user_id'];
        
        $ins = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $ins->bind_param("iiis", $user_id, $id, $rating, $review_text);
        if ($ins->execute()) {
            $msg = "<div class='alert alert-success'>Thank you for your review!</div>";
        }
    }
}
$db_sizes = [];
$total_stock = 0;
while($row = $s_res->fetch_assoc()){
    $db_sizes[$row['size']] = $row['stock_quantity'];
    $total_stock += $row['stock_quantity'];
}
$all_sizes = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
$sizes = [];
foreach ($all_sizes as $sz) {
    $sizes[] = [
        'size' => $sz,
        'stock_quantity' => $db_sizes[$sz] ?? 0
    ];
}

$is_sold_out = ($product['status'] == 'sold_out' || $total_stock <= 0);
?>

<div class="container py-5">
    <div class="row">
        <!-- Image -->
        <div class="col-md-5 mb-4">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-main-img">
        </div>
        
        <!-- Details -->
        <div class="col-md-7">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #e0e0e0;">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php?category=<?= urlencode($product['category']) ?>" class="text-decoration-none" style="color: #e0e0e0;"><?= htmlspecialchars($product['category']) ?></a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($product['team_name']) ?></li>
                </ol>
            </nav>
            
            <h2 class="font-weight-bold text-white"><?= htmlspecialchars($product['product_name']) ?></h2>
            <p class="mb-2" style="color: #cccccc;">Model: <?= htmlspecialchars($product['model']) ?> | Material: <?= htmlspecialchars($product['material']) ?></p>
            
            <div class="mb-3">
                <span class="fs-2 font-weight-bold" style="color: var(--primary-color);">₹<?= $product['price'] ?></span>
                <?php if($product['original_price'] > $product['price']): ?>
                    <span class="original-price fs-5">₹<?= $product['original_price'] ?></span>
                    <span class="badge bg-success ms-2">Save ₹<?= ($product['original_price'] - $product['price']) ?></span>
                <?php endif; ?>
            </div>
            
            <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            
            <?php if($is_sold_out): ?>
                <div class="alert alert-danger d-inline-block px-4 py-2 fw-bold fs-4">SOLD OUT</div>
            <?php else: ?>
                <form action="add_to_cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold mb-0">Select Size:</h6>
                        <span id="stock-display" class="small font-weight-bold" style="color: var(--primary-color);"></span>
                    </div>
                    <div class="size-selector mb-4">
                        <?php foreach($sizes as $s): ?>
                            <?php $disabled = ($s['stock_quantity'] <= 0) ? 'disabled' : ''; ?>
                            <input type="radio" name="size" id="size_<?= $s['size'] ?>" value="<?= $s['size'] ?>" required <?= $disabled ?> onchange="updateStock(<?= $s['stock_quantity'] ?>)">
                            <label for="size_<?= $s['size'] ?>"><?= $s['size'] ?></label>
                        <?php endforeach; ?>
                    </div>
                    
                    <script>
                        function updateStock(stock) {
                            document.getElementById('stock-display').innerHTML = stock + ' available in stock';
                            const qtyInput = document.querySelector('input[name="quantity"]');
                            qtyInput.max = stock;
                            if (parseInt(qtyInput.value) > stock) {
                                qtyInput.value = stock;
                            }
                        }
                    </script>
                    
                    <h6 class="font-weight-bold mb-2">Quantity:</h6>
                    <div class="quantity-selector mb-4">
                        <button type="button" class="qty-minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="10" readonly>
                        <button type="button" class="qty-plus">+</button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-custom px-4 py-2 fs-5"><i class="fas fa-shopping-cart me-2"></i> ADD TO CART</button>
                        <!-- Add to Wishlist could just be a separate form or a link -->
                        <a href="wishlist.php?add=<?= $product['id'] ?>" class="btn btn-outline-danger px-4 py-2 fs-5"><i class="far fa-heart"></i></a>
                    </div>
                </form>
            <?php endif; ?>
            
            <hr class="mt-5 border-secondary">
            <div class="mt-4 p-3 border border-secondary rounded" style="background-color: rgba(255, 51, 102, 0.05);">
                <h6 style="color: var(--primary-color);"><i class="fas fa-truck me-2"></i> Delivery Information</h6>
                <p class="small mb-0" style="color: #e0e0e0;">Free shipping on orders above ₹1500. Standard delivery takes 3-5 business days.</p>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4" style="color: var(--primary-color);">Customer Reviews</h4>
            <?= $msg ?>
            
            <?php
            $rev_res = $conn->query("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = {$id} ORDER BY r.created_at DESC");
            if ($rev_res && $rev_res->num_rows > 0):
                while($rev = $rev_res->fetch_assoc()):
            ?>
                <div class="mb-3 p-3 rounded bg-card border border-secondary">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($rev['full_name']) ?></strong>
                        <span class="text-warning">
                            <?= str_repeat('<i class="fas fa-star"></i>', $rev['rating']) ?>
                            <?= str_repeat('<i class="far fa-star"></i>', 5 - $rev['rating']) ?>
                        </span>
                    </div>
                    <small style="color: #cccccc;"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                    <p class="mt-2 mb-0"><?= nl2br($rev['review_text']) ?></p>
                </div>
            <?php 
                endwhile;
            else:
                echo "<p class='text-muted'>No reviews yet. Be the first to review this product!</p>";
            endif;
            ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($has_purchased): ?>
                    <div class="mt-4 p-4 rounded bg-card border border-secondary">
                        <h5>Write a Review</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select bg-dark text-white border-secondary" required style="max-width: 150px; color: #000;">
                                    <option value="5" style="color:#000;">5 - Excellent</option>
                                    <option value="4" style="color:#000;">4 - Very Good</option>
                                    <option value="3" style="color:#000;">3 - Average</option>
                                    <option value="2" style="color:#000;">2 - Poor</option>
                                    <option value="1" style="color:#000;">1 - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Review</label>
                                <textarea name="review_text" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-custom px-4">Submit Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-4 bg-card border-info text-info">
                        <i class="fas fa-info-circle"></i> You can only write a review for this product after purchasing it.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-secondary mt-4 bg-card border-secondary text-white">
                    Please <a href="login.php" class="text-primary font-weight-bold">Login</a> to write a review.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
