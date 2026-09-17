<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();

if (isset($_GET['id'])) {
    $cart_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
}
header("Location: cart.php");
exit();
?>
