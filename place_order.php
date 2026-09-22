<?php
require_once 'db.php';
require_once 'auth.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Get shipping details
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    $payment_method = $_POST['payment_method'] ?? 'COD';

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Fetch cart items with lock (FOR UPDATE)
        $res = $conn->query("SELECT c.*, p.product_name, p.price, ps.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id JOIN product_sizes ps ON c.product_id = ps.product_id AND c.size = ps.size WHERE c.user_id = $user_id FOR UPDATE");
        
        $subtotal = 0;
        $cart_items = [];
        while($row = $res->fetch_assoc()){
            if($row['quantity'] > $row['stock_quantity']){
                throw new Exception("Insufficient stock for " . $row['product_name'] . " (Size: " . $row['size'] . "). Only " . $row['stock_quantity'] . " left.");
            }
            $cart_items[] = $row;
            $subtotal += ($row['price'] * $row['quantity']);
        }

        if(empty($cart_items)) {
            throw new Exception("Your cart is empty.");
        }

        $shipping = 50;
        
        $discount = 0;
        if (isset($_SESSION['reward_active']) && $_SESSION['reward_active']) {
            // Find max price item
            $max_price = 0;
            foreach($cart_items as $item) {
                if ($item['price'] > $max_price) $max_price = $item['price'];
            }
            $discount = $max_price;
            $subtotal -= $discount;
            if ($subtotal < 0) $subtotal = 0;
            
            // Deduct 10 points
            $conn->query("UPDATE users SET quiz_points = quiz_points - 10 WHERE id = $user_id");
            unset($_SESSION['reward_active']);
        }
        
        $total = $subtotal + $shipping;
        
        $order_number = 'FS' . date('YmdHi') . rand(1000,9999);
        $payment_status = ($payment_method == 'COD') ? 'COD' : 'Pending';
        $transaction_id = null;
        
        if ($payment_method == 'UPI') {
            $transaction_id = trim($_POST['transaction_id'] ?? '');
            if(empty($transaction_id)) {
                throw new Exception("Please enter the Transaction ID for UPI payment.");
            }
            // Basic validation: UPI Transaction IDs are usually 12 digits long
            if(!preg_match('/^[0-9]{12}$/', $transaction_id)) {
                throw new Exception("Invalid Transaction ID. A valid UPI Transaction ID must be exactly 12 digits.");
            }
            
            // Prevent duplicate transaction IDs
            $chk_stmt = $conn->prepare("SELECT id FROM orders WHERE transaction_id = ?");
            $chk_stmt->bind_param("s", $transaction_id);
            $chk_stmt->execute();
            if($chk_stmt->get_result()->num_rows > 0) {
                throw new Exception("This Transaction ID has already been used for another order.");
            }
        }

        // Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_charge, discount, payment_method, transaction_id, payment_status, shipping_name, shipping_phone, shipping_email, shipping_address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdddssssssssss", $user_id, $order_number, $total, $shipping, $discount, $payment_method, $transaction_id, $payment_status, $name, $phone, $email, $address, $city, $state, $pincode);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Insert Order Items and Update Stock
        foreach($cart_items as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            $i_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $i_stmt->bind_param("iissidd", $order_id, $item['product_id'], $item['product_name'], $item['size'], $item['quantity'], $item['price'], $item_subtotal);
            $i_stmt->execute();
            
            // Reduce stock
            $new_stock = $item['stock_quantity'] - $item['quantity'];
            $s_stmt = $conn->prepare("UPDATE product_sizes SET stock_quantity = ? WHERE product_id = ? AND size = ?");
            $s_stmt->bind_param("iis", $new_stock, $item['product_id'], $item['size']);
            $s_stmt->execute();
            
            // Recalculate product availability
            $p_stmt = $conn->prepare("SELECT SUM(stock_quantity) as total_stock FROM product_sizes WHERE product_id = ?");
            $p_stmt->bind_param("i", $item['product_id']);
            $p_stmt->execute();
            $p_res = $p_stmt->get_result()->fetch_assoc();
            
            if($p_res['total_stock'] <= 0) {
                $conn->query("UPDATE products SET status = 'sold_out' WHERE id = " . $item['product_id']);
            }
        }
        
        // Insert Tracking
        $conn->query("INSERT INTO order_tracking (order_id, status, note) VALUES ($order_id, 'Pending', 'Order Placed Successfully')");
        
        // Clear Cart
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");

        $conn->commit();
        
        echo "<script>alert('Order Placed Successfully! Your Order Number is $order_number'); window.location.href='quiz.php?order_id=$order_number';</script>";
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='cart.php';</script>";
        exit();
    }
}
?>
