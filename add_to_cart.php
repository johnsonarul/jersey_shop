<?php
require_once 'db.php';
require_once 'auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $size = trim($_POST['size']);
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];

    if (empty($size) || $quantity <= 0) {
        header("Location: product.php?id=$product_id&error=invalid");
        exit();
    }

    // Check stock
    $stmt = $conn->prepare("SELECT stock_quantity FROM product_sizes WHERE product_id = ? AND size = ?");
    $stmt->bind_param("is", $product_id, $size);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $stock_available = $row['stock_quantity'];
        if ($quantity > $stock_available) {
            header("Location: product.php?id=$product_id&error=stock");
            exit();
        }
    } else {
        header("Location: product.php?id=$product_id&error=notfound");
        exit();
    }

    // Check if already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?");
    $stmt->bind_param("iis", $user_id, $product_id, $size);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $new_qty = $row['quantity'] + $quantity;
        // Check against the stock fetched earlier
        if ($new_qty > $stock_available) { 
            $new_qty = $stock_available; // Cap it at max stock
        }
        $c_id = $row['id'];
        $conn->query("UPDATE cart SET quantity = $new_qty WHERE id = $c_id");
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $user_id, $product_id, $size, $quantity);
        $stmt->execute();
    }
    
    header("Location: cart.php");
    exit();
}
?>
