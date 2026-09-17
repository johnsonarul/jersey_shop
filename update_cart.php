<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $qty = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];

    if ($qty > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $cart_id, $user_id);
        $stmt->execute();
    }
}
header("Location: cart.php");
exit();
?>
