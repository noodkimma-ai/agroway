<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php"); exit;
}

$fid = $_SESSION['user_id'];

// Handle "soft delete"
if(isset($_GET['del'])){
    $pid = (int)$_GET['del'];
    // Mark product as deleted instead of removing it
    mysqli_query($conn,"UPDATE products SET is_deleted=1 WHERE product_id=$pid AND farmer_id=$fid");
}

// Fetch farmer's products (exclude deleted ones)
$res = mysqli_query($conn,"
    SELECT 
        p.*,
        c.category_name,
        s.subcategory_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id
    WHERE p.farmer_id = $fid AND (p.is_deleted IS NULL OR p.is_deleted=0)
    ORDER BY p.product_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Products - AgroWay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffffff; /* white background */
    margin: 0;
    color: #065f46; /* dark green text for contrast */
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

/* PRODUCT GRID */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    padding: 150px 40px 80px 40px;
}

.card {
    background: #F2EDC2;
    border-radius: 12px;
    overflow: hidden;
    text-align: center;
    padding-bottom: 15px;
    color: #065f46;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.card img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.card h4 { margin: 10px 0 5px; font-size: 18px; color: #065f46; }
.card p { margin: 5px 0; font-size: 14px; }
.card .actions { margin-top: 10px; }
.card .actions a {
    text-decoration: none;
    padding: 6px 12px;
    margin: 0 5px;
    border-radius: 6px;
    font-weight: 600;
    transition: 0.3s;
}
.card .edit { background: #2563eb; color: white; }
.card .edit:hover { background: #1e40af; }
.card .delete { background: #dc2626; color: white; }
.card .delete:hover { background: #991b1b; }

/* TOP BUTTONS */
.top-buttons a {
    display: inline-block;
    margin-right: 10px;
    padding: 10px 18px;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    color: white;
    transition: 0.3s;
}
.back-btn { background: #14532d; }
.back-btn:hover { background: #0f3d1f; }
.add-btn { background: #22c55e; }
.add-btn:hover { background: #15803d; }

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
<a href="farmer_dashboard.php" >🏠 Home</a>
<a href="add_product.php" >➕ Add</a>
<a href="manage_products.php" class="active">📦 Manage</a>
<a href="farmer_orders.php" >📋 Orders</a>
<a href="buy_products.php" >🛒 Buy</a>
<a href="farmer_profle.php" >👤 Personal Info</a>
<a href="farmer_history.php" >🕘 History</a>
<a href="farmer_recommendation.php" >🌱 Recommendation</a>
</div>

<h2 style="padding-left: 40px;">My Products</h2>
<div class="grid">
<?php while($p = mysqli_fetch_assoc($res)): ?>
    <div class="card">
        <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        <h4><?= htmlspecialchars($p['name']) ?></h4>
        <p>Category: <?= htmlspecialchars($p['category_name']) ?></p>
        <p>Subcategory: <?= htmlspecialchars($p['subcategory_name'] ?? '-') ?></p>
        <p>Price: Rs. <?= htmlspecialchars($p['price']) ?></p>
        <p>Qty: <?= htmlspecialchars($p['quantity']) ?></p>
        <p>Unit: <?= htmlspecialchars($p['unit']) ?></p>
        <div class="actions">
            <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="edit">Edit</a>
            <a href="?del=<?= $p['product_id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
        </div>
    </div>
<?php endwhile; ?>
</div>

<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>