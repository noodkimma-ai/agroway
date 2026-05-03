<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------
   RESTORE OR PERMANENT DELETE ORDER
---------------------------- */
if(isset($_POST['restore_order'])){
    $order_id = (int)$_POST['order_id'];

    mysqli_query($conn,"
        UPDATE orders 
        SET is_deleted = 0 
        WHERE order_id = $order_id
        AND (buyer_id=$uid OR order_id IN (
            SELECT o.order_id FROM orders o
            JOIN order_items oi ON o.order_id=oi.order_id
            JOIN products p ON oi.product_id=p.product_id
            WHERE p.farmer_id=$uid
        ))
    ");
}

if(isset($_POST['delete_order'])){
    $order_id = (int)$_POST['order_id'];

    // Permanently delete order items first
    mysqli_query($conn,"DELETE oi FROM order_items oi
        JOIN products p ON oi.product_id=p.product_id
        WHERE oi.order_id=$order_id AND p.farmer_id=$uid
    ");

    // Then delete the order
    mysqli_query($conn,"DELETE FROM orders WHERE order_id=$order_id AND (buyer_id=$uid OR order_id IN (
        SELECT o.order_id FROM orders o
        JOIN order_items oi ON o.order_id=oi.order_id
        JOIN products p ON oi.product_id=p.product_id
        WHERE p.farmer_id=$uid
    ))");
}

/* ---------------------------
   SALES HISTORY (DELETED PRODUCTS)
---------------------------- */
$salesHistory = mysqli_query($conn,"
SELECT o.order_id, o.status, u.name AS buyer_name,
       p.name AS product_name, oi.qty, oi.price
FROM orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN products p ON oi.product_id=p.product_id
JOIN users u ON o.buyer_id=u.user_id
WHERE p.farmer_id=$uid AND o.is_deleted=1
ORDER BY o.created_at DESC
");

/* ---------------------------
   PURCHASE HISTORY (DELETED ORDERS BY FARMER)
---------------------------- */
$purchaseHistory = mysqli_query($conn,"
SELECT o.order_id, o.status, p.name AS product_name, p.unit, oi.qty, oi.price
FROM orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN products p ON oi.product_id=p.product_id
WHERE o.buyer_id=$uid AND o.is_deleted=1
ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Farmer History - AgroWay</title>

<style>
body{
    font-family:'Poppins',sans-serif;
    background:white;
    margin:0;
    color:#065f46;
}
body::before{
    content:"";
    position:fixed;
    inset:0;
    background:rgba(220,252,231,0.55);
    z-index:-1;
}

/* HEADER */
header {
    position: fixed;
    top: 0;
    width: 97%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    color: white;
    background: #A2CB8B;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
header .logo {
    font-size: 32px;
    font-weight: bold;
    color:#346739;
}
header a {
    font-size: 18px;
    padding: 10px 18px;
    border-radius: 10px;
    background: #346739;
    text-decoration: none;
    color: white;
    transition: 0.3s;
}
header a:hover {
    background: #15803d;
}

/* NAVBAR */
.navbar {
    position: fixed;
    top: 70px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 20px 0;
    backdrop-filter: blur(10px);
    background: #9FCB98;
    z-index: 999;
}
.navbar a {
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: #065f46;
    font-weight: 600;
    transition: 0.3s;
}
.navbar a.active { 
    background: #22c55e;
    color: white; 
}
.navbar a:hover { 
    background: #15803d;
    color: white; 
}

/* CONTAINER */
.container{
    margin:140px auto 50px;
    width:95%;
}
.row{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}
.col{
    width:50%;
}

/* CARD */
.card{
    background:#bbf7d0;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.15);
}
button{
    background:#22c55e;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    transition:0.3s;
    margin-right:5px;
}
button:hover{
    background:#14532d;
}
h2{
    color:#065f46;
    margin-bottom:10px;
}

/* FOOTER */
footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #A2CB8B;
    color: white;
    text-align: center;
    padding: 15px 0;
    font-size: 14px;
    z-index: 1000;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.2);
}
</style>
</head>

<body>

<header>
    <div class="logo">🌾 AgroWay</div>
    <a href="logout.php">🚪 Logout</a>
</header>

<div class="navbar">
<a href="farmer_dashboard.php">🏠 Home</a>
<a href="add_product.php">➕ Add</a>
<a href="manage_products.php">📦 Manage</a>
<a href="farmer_orders.php">📋 Orders</a>
<a href="buy_products.php">🛒 Buy</a>
<a href="farmer_profle.php">👤 Personal Info</a>
<a href="farmer_history.php" class="active">🕘 History</a>
<a href="farmer_recommendation.php">🌱 Recommendation</a>
</div>

<div class="container">
<div class="row">

<!-- LEFT → SALES HISTORY -->
<div class="col">
<h2>📦 Deleted Sales</h2>

<?php if(mysqli_num_rows($salesHistory)==0): ?>
<p>No deleted sales</p>
<?php endif; ?>

<?php while($s=mysqli_fetch_assoc($salesHistory)): ?>
<div class="card">
<p><b>Product:</b> <?= $s['product_name'] ?></p>
<p><b>Buyer:</b> <?= $s['buyer_name'] ?></p>
<p><b>Qty:</b> <?= $s['qty'] ?></p>
<p><b>Price:</b> Rs <?= $s['price'] ?></p>
<p><b>Total:</b> Rs <?= $s['qty']*$s['price'] ?></p>
<p><b>Status:</b> <?= $s['status'] ?></p>

<form method="post" style="display:flex; gap:5px;">
<input type="hidden" name="order_id" value="<?= $s['order_id'] ?>">
<button name="restore_order">♻ Restore</button>
<button name="delete_order">🗑 Permanent Delete</button>
</form>
</div>
<?php endwhile; ?>
</div>

<!-- RIGHT → PURCHASE HISTORY -->
<div class="col">
<h2>🛒 Deleted Purchases</h2>

<?php if(mysqli_num_rows($purchaseHistory)==0): ?>
<p>No deleted purchases</p>
<?php endif; ?>

<?php while($p=mysqli_fetch_assoc($purchaseHistory)): ?>
<div class="card">
<p><b>Product:</b> <?= $p['product_name'] ?></p>
<p><b>Qty:</b> <?= $p['qty'] ?> <?= $p['unit'] ?></p>
<p><b>Price:</b> Rs <?= $p['price'] ?></p>
<p><b>Total:</b> Rs <?= $p['qty']*$p['price'] ?></p>
<p><b>Status:</b> <?= $p['status'] ?></p>

<form method="post" style="display:flex; gap:5px;">
<input type="hidden" name="order_id" value="<?= $p['order_id'] ?>">
<button name="restore_order">♻ Restore</button>
<button name="delete_order">🗑 Permanent Delete</button>
</form>
</div>
<?php endwhile; ?>
</div>

</div>
</div>

<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>