<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch all orders of this user
$orders = mysqli_query($conn,
    "SELECT * FROM orders WHERE buyer_id=$uid ORDER BY created_at DESC"
);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .order-box {
            background: #fff;
            padding: 15px;
            margin-bottom: 18px;
            border-radius: 10px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
        }
        .order-items {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th, .order-items td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>My Orders</h3>

    <?php if (mysqli_num_rows($orders) == 0): ?>
        <div class="alert">No orders found.</div>
    <?php endif; ?>

    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <div class="order-box">
            <h4>Order #<?= $o['order_id'] ?></h4>

            <p><b>Status:</b> <?= $o['status'] ?></p>
            <p><b>Total Amount:</b> Rs. <?= $o['total_amount'] ?></p>
            <p><b>Date:</b> <?= $o['created_at'] ?></p>
            <p><b>Address:</b> <?= $o['address'] ?></p>

            <h5>Ordered Items</h5>

            <?php
            // Fetch order items
            $oid = $o['order_id'];
            $items = mysqli_query($conn,
                "SELECT oi.*, p.name, p.unit 
                 FROM order_items oi
                 JOIN products p ON oi.product_id=p.product_id
                 WHERE oi.order_id=$oid"
            );
            ?>

            <table class="order-items">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>

                <?php while ($it = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $it['name'] ?></td>
                    <td><?= $it['qty'] . ' ' . $it['unit'] ?></td>
                    <td>Rs. <?= $it['price'] ?></td>
                    <td>Rs. <?= $it['qty'] * $it['price'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
