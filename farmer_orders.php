<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------
   DELETE (PURCHASE OR SALES)
---------------------------- */
if(isset($_POST['delete_order'])){
    $order_id = (int)$_POST['order_id'];

    // buyer_id OR farmer_id check
    mysqli_query($conn,"
        UPDATE orders 
        SET is_deleted=1 
        WHERE order_id=$order_id 
        AND (buyer_id=$uid OR EXISTS(
            SELECT 1 FROM order_items oi
            JOIN products p ON oi.product_id=p.product_id
            WHERE oi.order_id=$order_id AND p.farmer_id=$uid
        ))
    ");
}

/* ---------------------------
   UPDATE STATUS
---------------------------- */
if(isset($_POST['update_status'])){
    $order_id = (int)$_POST['order_id'];
    $status   = mysqli_real_escape_string($conn, $_POST['status']);

    $delivery_date = $_POST['delivery_date'] ?? NULL;
    $delivery_time = $_POST['delivery_time'] ?? NULL;
    $phone         = $_POST['phone'] ?? NULL;
    $message       = $_POST['message'] ?? NULL;

    mysqli_query($conn,"
        UPDATE orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        SET 
            o.status = '$status',
            o.delivery_date = ".($delivery_date ? "'$delivery_date'" : "NULL").",
            o.delivery_time = ".($delivery_time ? "'$delivery_time'" : "NULL").",
            o.farmer_phone = ".($phone ? "'$phone'" : "NULL").",
            o.delivery_message = ".($message ? "'$message'" : "NULL")."
        WHERE o.order_id = $order_id
        AND p.farmer_id = $uid
    ");
}

/* ---------------------------
   SALES (LEFT)
---------------------------- */
$mySales = mysqli_query($conn,"
SELECT o.*, u.name AS buyer_name,
       p.name, oi.qty, oi.price,
       (oi.qty*oi.price) AS total
FROM orders o
JOIN order_items oi ON o.order_id=oi.order_id
JOIN products p ON oi.product_id=p.product_id
JOIN users u ON o.buyer_id=u.user_id
WHERE p.farmer_id=$uid AND o.is_deleted=0
ORDER BY o.created_at DESC
");

/* ---------------------------
   PURCHASE (RIGHT)
---------------------------- */
$myOrders = mysqli_query($conn, "
SELECT o.*, 
       p.name AS product_name,  -- <== Alias is now product_name
       p.unit, 
       oi.qty, 
       oi.price,
       (oi.qty * oi.price) AS total,
       u.name AS farmer_name
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
JOIN users u ON p.farmer_id = u.user_id
WHERE o.buyer_id = $uid AND o.is_deleted = 0
ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Farmer Orders - AgroWay</title>
<style>
/* BODY */
body {
    font-family:'Poppins',sans-serif;
    background:#ffffff;
    margin:0;
    color:#065f46;
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
    background: #A2CB8B; /* green header */
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
    background: #346739; /* darker green button */
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
    background:	#9FCB98; /* slightly darker soft green than cards */
    z-index: 999;
}
.navbar a {
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: #065f46; /* dark green text for visibility */
    font-weight: 600;
    transition: 0.3s;
}
.navbar a.active { 
    background: #22c55e; /* bright green for active */
    color: white; 
}
.navbar a:hover { 
    background: #15803d; 
    color: white; 
}

/* CONTAINER */
.container {
    margin:140px auto 80px;
    width:95%;
    display:flex;
    gap:20px;
}

/* COLUMNS */
.col {
    flex:1;
}

/* CARDS */
.card {
    background:#F2EDC2;
    color:#065f46;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

/* STATUS COLORS */
.status.pending { color:#ca8a04; font-weight:bold; }
.status.accepted { color:#2563eb; font-weight:bold; }
.status.shipped { color:#7c3aed; font-weight:bold; }
.status.delivered { color:#16a34a; font-weight:bold; }
.status.rejected { color:#dc2626; font-weight:bold; }

/* FORM ELEMENTS */
input, select, textarea {
    padding:5px; 
    border-radius:6px;
    border:1px solid #16a34a;
    margin-top:5px;
    width:100%;
}
button { 
    background:#16a34a; 
    color:white; 
    padding:6px 10px; 
    border:none; 
    border-radius:6px; 
    cursor:pointer; 
}
.delete-btn { background:#dc2626; margin-top:10px; }
.delivery { 
    margin-top:10px; 
    background:#bbf7d0; 
    padding:10px; 
    border-radius:8px; 
    color:#065f46; 
}

/* FOOTER */
footer {
    position: fixed; /* keeps it always at the bottom */
    bottom: 0;
    left: 0;
    width: 100%;
    background: #A2CB8B; /* green footer */
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
<a href="farmer_orders.php" class="active">📋 Orders</a>
<a href="buy_products.php">🛒 Buy</a>
<a href="farmer_profle.php">👤 Personal Info</a>
<a href="farmer_history.php">🕘 History</a>
<a href="farmer_recommendation.php">🌱 Recommendation</a>
</div>

<div class="container">

    <!-- LEFT → ORDERS FROM BUYERS -->
    <div class="col">
        <h2>📦 Orders from Buyers</h2>
        <?php while($s=mysqli_fetch_assoc($mySales)): ?>
        <div class="card">
            <p><b>Product:</b> <?= $s['name'] ?></p>
            <p>Buyer: <?= $s['buyer_name'] ?></p>
            <p>Qty: <?= $s['qty'] ?></p>
            <p>Total: Rs <?= $s['total'] ?></p>
            <p class="status <?= strtolower($s['status']) ?>"><?= $s['status'] ?></p>

            <?php if($s['status']=='Delivered'): ?>
            <div class="delivery">
                📦 <?= $s['delivery_date'] ?><br>
                ⏰ <?= $s['delivery_time'] ?><br>
                📞 <?= $s['farmer_phone'] ?><br>
                💬 <?= $s['delivery_message'] ?>
            </div>
            <?php endif; ?>

            <?php if($s['status']!='Delivered' && $s['status']!='Rejected'): ?>
            <form method="post">
                <input type="hidden" name="order_id" value="<?= $s['order_id'] ?>">
                <select name="status" onchange="toggleDelivery(this,<?= $s['order_id'] ?>)">
                    <option>Pending</option>
                    <option>Accepted</option>
                    <option>Rejected</option>
                    <option>Shipped</option>
                    <option>Delivered</option>
                </select>
                <div id="delivery_<?= $s['order_id'] ?>" style="display:none;">
                    <input type="date" name="delivery_date">
                    <input type="time" name="delivery_time">
                    <input type="text" name="phone" placeholder="Phone">
                    <textarea name="message" placeholder="Message"></textarea>
                </div>
                <button name="update_status">Update</button>
            </form>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="order_id" value="<?= $s['order_id'] ?>">
                <button name="delete_order" class="delete-btn">Delete</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- RIGHT → YOUR PURCHASES -->
    <div class="col">
        <h2>🛒 Your Purchases</h2>
        <?php while($o=mysqli_fetch_assoc($myOrders)): ?>
        <div class="card">
           <p>Farmer: <?= isset($o['farmer_name']) ? htmlspecialchars($o['farmer_name']) : '-' ?></p>
<p>Product: <?= isset($o['product_name']) ? htmlspecialchars($o['product_name']) : '-' ?></p>
<p>Qty: <?= isset($o['qty']) ? htmlspecialchars($o['qty']) : '-' ?> <?= isset($o['unit']) ? htmlspecialchars($o['unit']) : '-' ?></p>
<p>Total: Rs <?= isset($o['total']) ? htmlspecialchars($o['total']) : '-' ?></p>
<p class="status <?= isset($o['status']) ? strtolower($o['status']) : '' ?>"><?= isset($o['status']) ? htmlspecialchars($o['status']) : '-' ?></p>

            <?php if($o['status']=='Delivered'): ?>
            <div class="delivery">
                📦 <?= $o['delivery_date'] ?><br>
                ⏰ <?= $o['delivery_time'] ?><br>
                📞 <?= $o['farmer_phone'] ?><br>
                💬 <?= $o['delivery_message'] ?>
            </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                <button name="delete_order" class="delete-btn">Delete</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
function toggleDelivery(select,id){
    let box = document.getElementById("delivery_"+id);
    box.style.display = (select.value==="Delivered") ? "block" : "none";
}
</script>

<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>