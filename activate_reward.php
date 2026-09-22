<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];
$u_stmt = $conn->prepare("SELECT quiz_points FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

if ($user['quiz_points'] >= 10) {
    $_SESSION['reward_active'] = true;
    echo "<script>alert('Reward Activated! Your most expensive jersey in the cart is now FREE!'); window.location.href='cart.php';</script>";
} else {
    echo "<script>alert('You do not have enough points.'); window.location.href='account.php';</script>";
}
?>
