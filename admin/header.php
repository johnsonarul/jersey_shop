<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JR JERSEY SHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="padding-top: 0;">

<div class="d-md-none bg-dark text-white p-3 d-flex justify-content-between align-items-center">
    <strong>JR JERSEY SHOP ADMIN</strong>
    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar">
        <i class="fas fa-bars"></i>
    </button>
</div>

<div class="admin-sidebar collapse d-md-block" id="adminSidebar">
    <div class="text-center mb-4 mt-2">
        <img src="../assets/images/logo.png" alt="JR Jersey Shop" height="60" style="filter: invert(1);">
    </div>
    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
    <a href="categories.php" class="<?= $current_page == 'categories.php' || $current_page == 'add_category.php' ? 'active' : '' ?>"><i class="fas fa-tags me-2"></i> Categories</a>
    <a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>"><i class="fas fa-tshirt me-2"></i> Products</a>
    <a href="add_product.php" class="<?= $current_page == 'add_product.php' ? 'active' : '' ?>"><i class="fas fa-plus me-2"></i> Add Jersey</a>
    <a href="orders.php" class="<?= $current_page == 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-bag me-2"></i> Orders</a>
    <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>"><i class="fas fa-users me-2"></i> Customers</a>
    <a href="reviews.php" class="<?= $current_page == 'reviews.php' ? 'active' : '' ?>"><i class="fas fa-star me-2"></i> Reviews</a>
    <a href="settings.php" class="<?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog me-2"></i> Settings</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="admin-content">
