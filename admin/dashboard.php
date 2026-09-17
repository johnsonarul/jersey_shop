<?php
require_once '../db.php';
include 'header.php';

// Get KPIs
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$available_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'available'")->fetch_assoc()['count'];
$sold_out_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'sold_out'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Pending'")->fetch_assoc()['count'];
$delivered_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Delivered'")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'Paid' OR payment_method = 'COD'")->fetch_assoc()['total'] ?? 0;
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h6 class="card-title">TOTAL PRODUCTS</h6>
                <h3><?= $total_products ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h6 class="card-title">AVAILABLE PRODUCTS</h6>
                <h3><?= $available_products ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-danger h-100">
            <div class="card-body">
                <h6 class="card-title">SOLD OUT</h6>
                <h3><?= $sold_out_products ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h6 class="card-title">TOTAL ORDERS</h6>
                <h3><?= $total_orders ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h6 class="card-title">PENDING ORDERS</h6>
                <h3><?= $pending_orders ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-secondary h-100">
            <div class="card-body">
                <h6 class="card-title">DELIVERED</h6>
                <h3><?= $delivered_orders ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-dark h-100">
            <div class="card-body">
                <h6 class="card-title">TOTAL CUSTOMERS</h6>
                <h3><?= $total_customers ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white h-100" style="background-color: #673ab7;">
            <div class="card-body">
                <h6 class="card-title">TOTAL SALES</h6>
                <h3>₹<?= number_format($total_sales, 2) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-card font-weight-bold">Available vs Sold Out</div>
            <div class="card-body">
                <canvas id="productChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx1 = document.getElementById('productChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Available', 'Sold Out'],
            datasets: [{
                data: [<?= $available_products ?>, <?= $sold_out_products ?>],
                backgroundColor: ['#4caf50', '#f44336']
            }]
        }
    });
</script>

<?php include 'footer.php'; ?>
