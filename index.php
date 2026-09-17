<?php
require_once 'db.php';
include 'header.php';

// Fetch Trending / Available Products
$res = $conn->query("SELECT * FROM products WHERE status = 'available' ORDER BY id DESC LIMIT 4");
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>ALL JERSEYS AVAILABLE</h1>
        <p>Premium Quality | Player Version | Team Jersey Printing | Sportswear</p>
        <a href="products.php" class="btn btn-custom btn-lg">SHOP NOW</a>
        <a href="products.php" class="btn btn-outline-light btn-lg ms-2">VIEW ALL PRODUCTS</a>
    </div>
</section>

<!-- Categories -->
<section class="container py-5">
    <h2 class="text-center mb-4 font-weight-bold">SHOP BY CATEGORY</h2>
    <div class="row text-center justify-content-center">
        <?php 
        $cat_query = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC");
        if ($cat_query->num_rows > 0):
            while($cat = $cat_query->fetch_assoc()):
        ?>
        <div class="col-md-4 col-sm-6 mb-4">
            <a href="products.php?category=<?= urlencode($cat['name']) ?>" class="text-decoration-none">
                <div class="card bg-card hover-shadow h-100" style="border: 2px solid var(--glass-border); border-radius: 12px; transition: 0.3s; overflow: hidden; padding: 10px;">
                    <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="img-fluid rounded" style="height: 200px; object-fit: contain; width: 100%;">
                    <div class="card-body text-center p-3">
                        <h6 class="font-weight-bold mb-0 text-white text-uppercase" style="letter-spacing: 1px;"><?= $cat['name'] ?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php 
            endwhile;
        endif; 
        ?>
    </div>
</section>

<!-- Trending Products -->
<section class="container py-5">
    <h2 class="text-center mb-4 font-weight-bold">TRENDING JERSEYS</h2>
    <div class="row">
        <?php while($row = $res->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="product-card h-100 text-center text-decoration-none text-dark d-block pb-3">
                    <a href="product.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" class="product-image">
                            <?php if($row['discount'] > 0): ?>
                                <span class="discount-badge">-<?= $row['discount'] ?>%</span>
                            <?php endif; ?>
                            <?php if($row['status'] == 'sold_out'): ?>
                                <span class="sold-out-badge">SOLD OUT</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <div class="product-category"><?= htmlspecialchars($row['team_name']) ?> | <?= htmlspecialchars($row['category']) ?></div>
                            <div class="product-title"><?= htmlspecialchars($row['product_name']) ?></div>
                            <div class="text-muted small mb-2"><?= htmlspecialchars($row['model']) ?></div>
                            
                            <div class="price-wrap mb-2">
                                <span class="product-price">₹<?= $row['price'] ?></span>
                                <?php if($row['original_price'] > $row['price']): ?>
                                    <span class="original-price">₹<?= $row['original_price'] ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php
                                $sz_res = $conn->query("SELECT size, stock_quantity FROM product_sizes WHERE product_id = {$row['id']} AND stock_quantity > 0");
                                $sizes = [];
                                while($sz_row = $sz_res->fetch_assoc()){
                                    $sizes[] = $sz_row['size'];
                                }
                            ?>
                            <div class="size-list">
                                Sizes: <?= empty($sizes) ? 'N/A' : implode(' ', $sizes) ?>
                            </div>
                        </div>
                    </a>
                    <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-custom btn-sm w-75">VIEW DETAILS</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="text-center mt-4">
        <a href="products.php" class="btn btn-outline-light px-4 py-2 rounded-pill">VIEW ALL PRODUCTS</a>
    </div>
</section>

<?php include 'footer.php'; ?>
