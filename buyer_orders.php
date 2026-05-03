<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------
   DELETE ORDER
---------------------------- */
if(isset($_POST['delete_order'])){
    $order_id = (int)$_POST['order_id'];

    // 🔥 Mark ALL order items as deleted
    mysqli_query($conn,"
        UPDATE order_items 
        SET is_deleted=1 
        WHERE order_id=$order_id
    ");

    // OPTIONAL: also mark order deleted
    mysqli_query($conn,"
        UPDATE orders 
        SET is_deleted=1 
        WHERE order_id=$order_id
    ");

    // 🔥 Redirect to history page
    header("Location: buyer_history.php");
    exit;
}

/* ---------------------------
   UPDATE STATUS (SELLER)
---------------------------- */
if(isset($_POST['update_status'])){
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $delivery_date = $_POST['delivery_date'] ?? NULL;
    $delivery_time = $_POST['delivery_time'] ?? NULL;
    $phone = $_POST['phone'] ?? NULL;
    $message = $_POST['message'] ?? NULL;

    mysqli_query($conn,"
        UPDATE orders o
        JOIN order_items oi ON o.order_id=oi.order_id
        JOIN products p ON oi.product_id=p.product_id
        SET 
            o.status='$status',
            o.delivery_date = ".($delivery_date ? "'$delivery_date'" : "NULL").",
            o.delivery_time = ".($delivery_time ? "'$delivery_time'" : "NULL").",
            o.farmer_phone = ".($phone ? "'$phone'" : "NULL").",
            o.delivery_message = ".($message ? "'$message'" : "NULL")."
        WHERE o.order_id=$order_id AND p.farmer_id=$uid
    ");
}

/* ---------------------------
   BUYER ORDERS
---------------------------- */
$myOrders = mysqli_query($conn,"
SELECT o.*, p.name, p.unit, oi.qty, oi.price,
       (oi.qty*oi.price) AS total
FROM orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN products p ON oi.product_id=p.product_id
WHERE o.buyer_id=$uid AND o.is_deleted=0
ORDER BY o.created_at DESC
");

/* ---------------------------
   SELLER SALES (FIXED)
---------------------------- */
$mySales = mysqli_query($conn,"
SELECT o.order_id, o.status, o.created_at,
       o.delivery_date, o.delivery_time, o.farmer_phone, o.delivery_message,
       u.name AS buyer_name,
       p.name, p.unit,
       oi.qty, oi.price,
       (oi.qty*oi.price) AS total
FROM orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN products p ON oi.product_id=p.product_id
JOIN users u ON o.buyer_id=u.user_id
WHERE p.farmer_id=$uid
ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Orders - AgroWay</title>

<style>
body{
    font-family:Poppins;
    background:#f0fdf4;
    margin:0;
    color:#065f46;
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    width:97%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:5px 30px;
    background:#bce3c9; /* dark green */
    color:white;
    z-index:1000;
}
.header h2{ font-size:26px; 
color:#346739;}
.header a{
    font-size:16px;
    padding:8px 12px;
    border-radius:6px;
    background: #346739;
    text-decoration:none;
    color:white;
}
.header a:hover{ background:rgba(255,255,255,0.35); }

/* NAVBAR */
.navbar{
    position:fixed;
    top:75px;   /* ✔ correct spacing below header */
    left:0;
    width:100%;
    display:flex;
    justify-content:center;
    gap:15px;
    padding:25px;
    background:#E8F5BD;
    z-index:999;
}
.navbar a{
    color:white;
    text-decoration:none;
    padding:10px 14px;
    border-radius:8px;
    background:#A2CB8B;
}
.navbar a.active{ background:#14532d; } /* dark green */
.navbar a:hover{ background:#166534; }

/* CONTAINER */
.container{
    margin:150px auto;
    width:95%;
}

/* GRID */
.row{ display:flex; gap:20px; }
.col{ width:50%; }

/* CARD */
.card{
    background:#dcfce7;
    padding:15px;
    border-radius:12px;
    margin-bottom:15px;
}

/* STATUS COLORS */
.pending{color:#ca8a04;}
.accepted{color:#2563eb;}
.shipped{color:#7c3aed;}
.delivered{color:#16a34a;}
.rejected{color:#dc2626;}

/* DELIVERY BOX */
.delivery{
    background:#bbf7d0;
    padding:10px;
    border-radius:8px;
    margin-top:10px;
}

/* BUTTON */
button{
    background:#22c55e;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:6px;
}
.delete-btn{ background:#dc2626; }

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    width:100%;
    text-align:center;
    padding:10px;
    background:#bce3c9;
}
</style>

</head>
<body>
<div class="header">
    <h2>🌾 AgroWay</h2>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="navbar">
    <a href="index.php">🏠 Home</a>
    <a href="buyer_add_product.php">➕ Add</a>
    <a href="buyer_manage_products.php" >📦 Manage</a>
    <a href="buyer_orders.php" class="active">>📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
    <a href="buyer_history.php">🕘 History</a>
    <a href="buyer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">

<div class="row">

<!-- 🛒 BUYER -->
<div class="col">
<h2>My Purchases</h2>

<?php while($o=mysqli_fetch_assoc($myOrders)): ?>
<div class="card">

<p><b><?= $o['name'] ?></b></p>
<p>Qty: <?= $o['qty'] ?> <?= $o['unit'] ?></p>
<p>Total: Rs <?= $o['total'] ?></p>

<p class="<?= strtolower($o['status']) ?>">
Status: <?= $o['status'] ?>
</p>

<?php if($o['delivery_date'] || $o['delivery_time'] || $o['farmer_phone']): ?>
<div class="delivery">
📅 <?= $o['delivery_date'] ?: 'N/A' ?><br>
⏰ <?= $o['delivery_time'] ?: 'N/A' ?><br>
📞 <?= $o['farmer_phone'] ?: 'N/A' ?><br>
💬 <?= $o['delivery_message'] ?: 'No message' ?>
</div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
<button name="delete_order" class="delete-btn">Delete</button>
</form>

</div>
<?php endwhile; ?>
</div>

<!-- 💰 SELLER -->
<div class="col">
<h2>Your Sales</h2>

<?php while($s=mysqli_fetch_assoc($mySales)): ?>
<div class="card">

<p><b><?= $s['name'] ?></b></p>
<p>Buyer: <?= $s['buyer_name'] ?></p>
<p>Qty: <?= $s['qty'] ?> <?= $s['unit'] ?></p>
<p>Total: Rs <?= $s['total'] ?></p>

<p class="<?= strtolower($s['status']) ?>">
Status: <?= $s['status'] ?>
</p>

<?php if($s['delivery_date'] || $s['delivery_time'] || $s['farmer_phone']): ?>
<div class="delivery">
📅 <?= $s['delivery_date'] ?: 'N/A' ?><br>
⏰ <?= $s['delivery_time'] ?: 'N/A' ?><br>
📞 <?= $s['farmer_phone'] ?: 'N/A' ?><br>
💬 <?= $s['delivery_message'] ?: 'No message' ?>
</div>
<?php endif; ?>

</div>
<?php endwhile; ?>
</div>

</div>
</div>

<div class="footer">
© AgroWay <?=date('Y')?>
</div>

</body>
</html>