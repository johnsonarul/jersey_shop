<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    
    // Image Upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 2000000) {
            $new_name = uniqid() . '.' . $ext;
            $dest = '../assets/images/categories/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = 'assets/images/categories/' . $new_name;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format or size > 2MB.";
        }
    } else {
        $error = "Category image is required.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, image, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $image_path, $status);
        if ($stmt->execute()) {
            $success = "Category added successfully!";
        } else {
            $error = "Failed to add category: " . $conn->error;
        }
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Category</h2>
    <a href="categories.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back to Categories</a>
</div>

<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="bg-card p-4 rounded shadow-sm max-w-500">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Football Socks" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Category Image *</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
            <small class="text-muted">Recommended aspect ratio: 1:1 or 4:3. Max 2MB.</small>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active">Active (Visible to customers)</option>
                <option value="inactive">Inactive (Hidden)</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Add Category</button>
    </form>
</div>

<?php include 'footer.php'; ?>
