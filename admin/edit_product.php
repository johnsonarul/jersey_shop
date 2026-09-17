<?php
require_once '../db.php';
include 'header.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}
$id = intval($_GET['id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $discount = intval($_POST['discount']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, price = ?, discount = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssdisi", $product_name, $category, $price, $discount, $status, $id);
    
    if ($stmt->execute()) {
        // Update stock
        $sizes = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
        foreach ($sizes as $size) {
            if (isset($_POST['stock_' . $size])) {
                $stock = intval($_POST['stock_' . $size]);
                // Check if exists
                $chk = $conn->query("SELECT id FROM product_sizes WHERE product_id = $id AND size = '$size'");
                if($chk->num_rows > 0) {
                    $conn->query("UPDATE product_sizes SET stock_quantity = $stock WHERE product_id = $id AND size = '$size'");
                } else if($stock > 0) {
                    $conn->query("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES ($id, '$size', $stock)");
                }
            }
        }
        $success = "Product updated successfully.";
    } else {
        $error = "Failed to update product.";
    }
}

$p_res = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $p_res->fetch_assoc();

$sz_res = $conn->query("SELECT * FROM product_sizes WHERE product_id = $id");
$stocks = [];
while($row = $sz_res->fetch_assoc()){
    $stocks[$row['size']] = $row['stock_quantity'];
}
?>

<h2 class="mb-4">Edit Product</h2>

<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="bg-card p-4 rounded shadow-sm max-w-800">
    <form method="POST" action="">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Product Name *</label>
                <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php 
                    $cat_res = $conn->query("SELECT name FROM categories WHERE status='active' ORDER BY name ASC");
                    while($c = $cat_res->fetch_assoc()):
                        $selected = ($product['category'] == $c['name']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($c['name']) ?>" <?= $selected ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label>Discount (%)</label>
                <input type="number" name="discount" class="form-control" value="<?= $product['discount'] ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= $product['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="sold_out" <?= $product['status'] == 'sold_out' ? 'selected' : '' ?>>Sold Out</option>
                </select>
            </div>
        </div>

        <h5 class="mt-4 mb-3">Sizes & Stock</h5>
        <div class="row">
            <?php foreach(['S', 'M', 'L', 'XL', 'XXL', '3XL'] as $size): ?>
            <div class="col-md-4 mb-3 border p-2 rounded">
                <div class="input-group input-group-sm">
                    <span class="input-group-text font-weight-bold" style="width: 50px; justify-content: center;"><?= $size ?></span>
                    <input type="number" name="stock_<?= $size ?>" class="form-control" value="<?= $stocks[$size] ?? 0 ?>" min="0">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary py-2 px-5">UPDATE PRODUCT</button>
            <a href="products.php" class="btn btn-outline-secondary ms-2 py-2 px-5">CANCEL</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
