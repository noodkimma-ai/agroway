<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------
   RESTORE ITEM
---------------------------- */
if(isset($_POST['restore_item'])){
    $item_id = (int)$_POST['item_id'];

    // Restore from order_items if exists
    mysqli_query($conn,"
    UPDATE order_items 
    SET is_deleted=0 
    WHERE order_item_id=$item_id
    ");

    // Restore from products if exists (soft-deleted product)
    mysqli_query($conn,"
    UPDATE products
    SET is_deleted=0
    WHERE product_id=$item_id
    ");
}

/* ---------------------------
   PERMANENT DELETE
---------------------------- */
if(isset($_POST['permanent_delete'])){
    $item_id = (int)$_POST['item_id'];

    // Delete from order_items if exists
    mysqli_query($conn,"
    DELETE FROM order_items 
    WHERE order_item_id=$item_id
    ");

    // Delete from products if exists
    mysqli_query($conn,"
    DELETE FROM products
    WHERE product_id=$item_id
    ");
}

/* ---------------------------
   FETCH PURCHASED (DELETED)
---------------------------- */
$deletedItems = mysqli_query($conn,"
SELECT oi.*, p.name, p.unit, (oi.qty*oi.price) AS total, o.created_at
FROM order_items oi
JOIN products p ON oi.product_id=p.product_id
JOIN orders o ON oi.order_id=o.order_id
WHERE o.buyer_id=$uid AND oi.is_deleted=1
ORDER BY o.created_at DESC
");

/* ---------------------------
   FETCH SALES (DELETED)
   Includes:
   1. Deleted order_items
   2. Deleted products (never sold but deleted by farmer)
---------------------------- */
$deletedSales = mysqli_query($conn,"
SELECT
    COALESCE(oi.order_item_id, p.product_id) AS item_id,
    p.name,
    IFNULL(oi.qty,0) AS qty,
    p.unit,
    IFNULL(oi.qty*oi.price,0) AS total,
    IFNULL(o.created_at, p.created_at) AS created_at
FROM products p
LEFT JOIN order_items oi ON p.product_id = oi.product_id AND oi.is_deleted=1
LEFT JOIN orders o ON oi.order_id = o.order_id
WHERE p.farmer_id=$uid AND (p.is_deleted=1 OR oi.is_deleted=1)
GROUP BY item_id
ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Order History - AgroWay</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins, sans-serif;
}

/* BODY */
body{
    background:#ffffff;
    color:#065f46;
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 30px;
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
    margin:160px auto 80px;
    width:95%;
}

/* GRID */
.grid{
    display:flex;
    gap:20px;
}

/* COLUMN */
.column{
    width:50%;
}

/* CARD */
.card{
    background:rgba(255,255,255,0.15);
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}

/* BUTTONS */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    margin-top:5px;
}
.restore{ background:#22c55e; color:white; }
.delete{ background:#dc2626; color:white; }

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    width:100%;
    text-align:center;
    padding:12px;
    color:white;
    background:#bce3c9; /* dark green */
    z-index: 1000;
}
</style>
</head>

<body>

<!-- HEADER -->

<div class="header">
    <h2>🌾 AgroWay</h2>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="navbar">
    <a href="index.php">🏠 Home</a>
    <a href="buyer_add_product.php">➕ Add</a>
    <a href="buyer_manage_products.php" >📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
    <a href="buyer_history.php" class="active">>🕘 History</a>
    <a href="buyer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">

<h2 style="text-align:center;">🕘 Deleted Items</h2>

<div class="grid">

<!-- LEFT: PURCHASE -->
<div class="column">
<h3 style="text-align:center;">🛒 Purchases</h3>

<?php if(mysqli_num_rows($deletedItems)==0): ?>
<p>No deleted purchases.</p>
<?php endif; ?>

<?php while($item=mysqli_fetch_assoc($deletedItems)): ?>
<div class="card">
    <p><b><?= $item['name'] ?></b></p>
    <p>Qty: <?= $item['qty'] ?> <?= $item['unit'] ?></p>
    <p>Total: Rs <?= $item['total'] ?></p>

    <form method="post">
        <input type="hidden" name="item_id" value="<?= $item['order_item_id'] ?>">
        <button class="btn restore" name="restore_item">♻ Restore</button>
        <button class="btn delete" name="permanent_delete" onclick="return confirm('Delete permanently?')">❌ Delete</button>
    </form>
</div>
<?php endwhile; ?>
</div>

<!-- RIGHT: SALES -->
<div class="column">
<h3 style="text-align:center;">🌾 Sales</h3>

<?php if(mysqli_num_rows($deletedSales)==0): ?>
<p>No deleted sales.</p>
<?php endif; ?>

<?php while($sale=mysqli_fetch_assoc($deletedSales)): ?>
<div class="card">
    <p><b><?= $sale['name'] ?></b></p>
    <?php if($sale['qty']>0): ?>
        <p>Qty Sold: <?= $sale['qty'] ?> <?= $sale['unit'] ?></p>
        <p>Earned: Rs <?= $sale['total'] ?></p>
    <?php else: ?>
        <p>Qty: N/A (Deleted before sale)</p>
        <p>Earned: N/A</p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="item_id" value="<?= $sale['item_id'] ?>">
        <button class="btn restore" name="restore_item">♻ Restore</button>
        <button class="btn delete" name="permanent_delete" onclick="return confirm('Delete permanently?')">❌ Delete</button>
    </form>
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