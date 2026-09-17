<?php
require_once 'db.php';
include 'header.php';

$where = ["status != 'inactive'"];
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where[] = "(product_name LIKE ? OR team_name LIKE ? OR category LIKE ? OR model LIKE ?)";
    array_push($params, $search, $search, $search, $search);
    $types .= "ssss";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

$where_clause = implode(" AND ", $where);
$sql = "SELECT * FROM products WHERE $where_clause ORDER BY id DESC";

if(!empty($params)){
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3 mb-4">
            <div class="bg-card p-3 rounded border border-secondary">
                <h5 class="mb-3 font-weight-bold" style="color: var(--primary-color);">Filters</h5>
                
                <form action="products.php" method="GET">
                    <?php if(isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold text-white">Category</label>
                        <select name="category" class="form-select form-select-sm" style="background-color: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-color);" onchange="this.form.submit()">
                            <option value="" style="color: #000;">All Categories</option>
                            <?php 
                            $fcat_res = $conn->query("SELECT name FROM categories WHERE status='active' ORDER BY name ASC");
                            while($fc = $fcat_res->fetch_assoc()):
                                $sel = (isset($_GET['category']) && $_GET['category'] == $fc['name']) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($fc['name']) ?>" <?= $sel ?> style="color: #000;"><?= htmlspecialchars($fc['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-md-9">
            <h3 class="mb-4">
                All Products 
                <span style="color: #cccccc; font-size: 1rem;">(<?= $res->num_rows ?> products found)</span>
            </h3>
            
            <?php if($res->num_rows == 0): ?>
                <div class="alert alert-warning text-center p-5">
                    <h4>No jerseys found</h4>
                    <p>Try adjusting your search or filters.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php while($row = $res->fetch_assoc()): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card h-100 text-center pb-3">
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
                                        <div class="product-category"><?= htmlspecialchars($row['team_name']) ?></div>
                                        <div class="product-title"><?= htmlspecialchars($row['product_name']) ?></div>
                                        <div class="price-wrap mb-2">
                                            <span class="product-price">₹<?= $row['price'] ?></span>
                                            <?php if($row['original_price'] > $row['price']): ?>
                                                <span class="original-price">₹<?= $row['original_price'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                                <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-custom btn-sm w-75">VIEW DETAILS</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
