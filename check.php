<?php require 'db.php'; $conn->query('UPDATE products SET status='available''); echo 'Updated all to available'; ?>
