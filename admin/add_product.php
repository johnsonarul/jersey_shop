<?php
require_once '../db.php';
include 'header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $team_name = trim($_POST['team_name']);
    $model = trim($_POST['model']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = floatval($_POST['original_price']);
    $discount = intval($_POST['discount']);
    $material = trim($_POST['material']);
    
    // Image Upload
    $image_path = "assets/images/products/placeholder.png"; // default
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['product_image']['size'] <= 2000000) {
            $new_name = uniqid('prod_') . '.' . $ext;
            $upload_path = '../assets/uploads/' . $new_name;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image_path = 'assets/uploads/' . $new_name;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format or size > 2MB.";
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, category, team_name, model, description, price, original_price, discount, image, material) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssddiss", $product_name, $category, $team_name, $model, $description, $price, $original_price, $discount, $image_path, $material);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Sizes and Stock
            $sizes = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
            foreach ($sizes as $size) {
                if (isset($_POST['size_' . $size]) && isset($_POST['stock_' . $size])) {
                    $stock = intval($_POST['stock_' . $size]);
                    if ($stock > 0) {
                        $s_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)");
                        $s_stmt->bind_param("isi", $product_id, $size, $stock);
                        $s_stmt->execute();
                    }
                }
            }
            $success = "Jersey added successfully.";
        } else {
            $error = "Failed to add product: " . $conn->error;
        }
    }
}
?>

<h2 class="mb-4">Add New Jersey</h2>

<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="bg-card p-4 rounded shadow-sm max-w-800">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Product Name *</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php 
                    $cat_res = $conn->query("SELECT name FROM categories WHERE status='active' ORDER BY name ASC");
                    while($c = $cat_res->fetch_assoc()):
                    ?>
                        <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Team Name</label>
                <input type="text" name="team_name" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Model *</label>
                <input type="text" name="model" class="form-control" placeholder="e.g. Home 2026" required>
            </div>
            <div class="col-md-12 mb-3">
                <label>Description *</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Original Price (₹)</label>
                <input type="number" step="0.01" name="original_price" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label>Discount (%)</label>
                <input type="number" name="discount" class="form-control" value="0">
            </div>
            <div class="col-md-6 mb-3">
                <label>Material</label>
                <input type="text" name="material" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Product Image *</label>
                <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
        </div>

        <h5 class="mt-4 mb-3">Sizes & Stock</h5>
        <div class="row">
            <?php foreach(['S', 'M', 'L', 'XL', 'XXL', '3XL'] as $size): ?>
            <div class="col-md-4 mb-3 border p-2 rounded">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="size_<?= $size ?>" id="sz_<?= $size ?>" value="1">
                    <label class="form-check-label font-weight-bold" for="sz_<?= $size ?>"><?= $size ?></label>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Stock</span>
                    <input type="number" name="stock_<?= $size ?>" class="form-control" value="0" min="0">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary mt-4 py-2 px-5">ADD PRODUCT</button>
    </form>
</div>

<?php include 'footer.php'; ?>
