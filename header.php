<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $cart_count = $row['count'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JR JERSEY SHOP | Men's & Sports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="fixed-top w-100" style="z-index: 9999; top: 0; left: 0;">

<!-- Top Announcement Bar -->
<div class="top-bar text-center">
    <div class="container">
        ⚽ Your Game. Your Jersey. Your Style. | Free Shipping Above ₹1,500
    </div>
</div>

<!-- Main Header -->
<nav class="navbar navbar-expand-lg main-header">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="JR Jersey Shop" height="40" style="filter: invert(1);">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">HOME</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">ABOUT US</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">ALL PRODUCTS</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="account.php">MY ACCOUNT</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="contact.php">CONTACT US</a>
                </li>
            </ul>

            <form class="d-flex me-3" action="products.php" method="GET">
                <input class="form-control me-2 rounded-pill" type="search" name="search" placeholder="Search jerseys..." aria-label="Search" style="color: #ffffff !important; background-color: rgba(255,255,255,0.1) !important;">
                <button class="btn btn-outline-danger rounded-pill" type="submit"><i class="fas fa-search"></i></button>
            </form>

            <div class="header-icons d-flex align-items-center">
                <a href="wishlist.php" title="Wishlist"><i class="far fa-heart"></i></a>
                <a href="cart.php" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="badge-cart"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="account.php" title="My Account"><i class="far fa-user"></i></a>
                    <a href="logout.php" title="Logout" class="ms-3 text-danger"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" title="Login / Register" class="ms-3 btn btn-outline-light btn-sm rounded-pill px-3" style="font-size: 0.8rem; padding: 5px 15px;">Login / Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
</div>
