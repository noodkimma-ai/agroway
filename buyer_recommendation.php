<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

/* 🌱 SEASON DETECTION */
$month = date('m');

if($month >= 3 && $month <= 5){
    $season = "Spring";
}
elseif($month >= 6 && $month <= 8){
    $season = "Summer";
}
elseif($month >= 9 && $month <= 11){
    $season = "Autumn";
}
else{
    $season = "Winter";
}

/* 🌱 SELECT SEASON */
if(isset($_GET['season'])){
    $season = $_GET['season'];
}

/* 🌱 SEASON PRODUCTS */
$seasonProducts = mysqli_query($conn,"
SELECT p.*, u.name AS farmer, f.farm_name
FROM products p
JOIN users u ON p.farmer_id=u.user_id
LEFT JOIN farms f ON p.farm_id=f.farm_id
WHERE p.season='$season' OR p.season='All Season'
");

/* ⭐ TRENDING PRODUCTS */
$trendingProducts = mysqli_query($conn,"
SELECT p.*, SUM(oi.qty) AS total_sold
FROM order_items oi
JOIN products p ON oi.product_id=p.product_id
GROUP BY p.product_id
ORDER BY total_sold DESC
LIMIT 6
");

/* 📊 TOP CROPS */
$topCrops = mysqli_query($conn,"
SELECT p.name, SUM(oi.qty) AS total_sold
FROM order_items oi
JOIN products p ON oi.product_id=p.product_id
GROUP BY p.name
ORDER BY total_sold DESC
LIMIT 5
");

/* 🤖 SMART RECOMMENDATION */
$smartProducts = mysqli_query($conn,"
SELECT p.*, IFNULL(SUM(oi.qty),0) AS total_sold
FROM products p
LEFT JOIN order_items oi ON p.product_id=oi.product_id
WHERE p.season='$season' OR p.season='All Season'
GROUP BY p.product_id
ORDER BY total_sold DESC, p.price ASC
LIMIT 6
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Smart Recommendation - AgroWay</title>

<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Poppins, sans-serif;
}

/* BODY */
body {
    background: #f0fdf4; /* light green */
    color: #299772;       /* dark green text */
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
    background:#DCF0C3;
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
.container {
    max-width: 1200px;
    margin: 150px auto 80px;
    padding: 20px;
}

/* GRID */
.grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

/* CARD */
.card {
    width: 200px;
    background: #c9d3c7; /* light green card */
    padding: 12px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.card img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}
.card h4 {
    margin: 8px 0 4px;
    color: #065f46;
}
.card p {
    margin: 4px 0;
    font-size: 14px;
}
.card a.btn {
    display: inline-block;
    margin-top: 8px;
    padding: 6px 10px;
    background: #22c55e;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
}
.card a.btn:hover {
    background: #15803d;
}

/* SEASON BUTTONS */
.season-btn {
    padding: 8px 14px;
    margin: 5px;
    border-radius: 10px;
    background: #d1fae5; /* lighter green button */
    color: #065f46;
    text-decoration: none;
    font-weight: bold;
    border: 1px solid #22c55e;
    transition: 0.3s;
}
.season-btn:hover {
    background: #22c55e;
    color: white;
}

/* TOP CROPS LIST */
ul {
    margin: 10px 0 20px 20px;
    list-style: disc;
}
ul li {
    margin: 5px 0;
    color: #065f46;
}

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

/* RESPONSIVE */
@media (max-width: 768px) {
    .grid {
        justify-content: center;
    }
    .card {
        width: 45%;
    }
}
@media (max-width: 480px) {
    .card {
        width: 90%;
    }
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
    <a href="buyer_manage_products.php">📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
     <a href="buyer_history.php">🕘 History</a>
<a href="buyer_recommendation.php" class="active">🌱 Recommendation</a>
</div>

<div class="container">

<h2 style="text-align:center;">🤖 Smart Buyer Recommendation</h2>

<!-- 🌱 SEASON BUTTONS -->
<div style="text-align:center;,padding:10px; " >
<a href="?season=Spring" class="season-btn">🌸 Spring</a>
<a href="?season=Summer" class="season-btn">☀️ Summer</a>
<a href="?season=Autumn" class="season-btn">🍂 Autumn</a>
<a href="?season=Winter" class="season-btn">❄️ Winter</a>
</div>

<!-- 🌱 SEASON PRODUCTS -->
<h3>🌱 Season Products (<?= $season ?>)</h3>
<div class="grid">
<?php while($row = mysqli_fetch_assoc($seasonProducts)): ?>
<div class="card">
<img src="uploads/<?= $row['image'] ?>">
<h4><?= $row['name'] ?></h4>
<p>💰 Rs <?= $row['price'] ?></p>
<p>📦 Qty: <?= $row['quantity'] ?></p>
<p>👨‍🌾 <?= $row['farmer'] ?></p>
<p>🏡 <?= $row['farm_name'] ?? 'Farm' ?></p>
<a href="product.php?id=<?= $row['product_id'] ?>" class="btn">View</a>
</div>
<?php endwhile; ?>
</div>

<!-- ⭐ TRENDING -->
<h3>🔥 Trending Products</h3>
<div class="grid">
<?php while($row = mysqli_fetch_assoc($trendingProducts)): ?>
<div class="card">
<img src="uploads/<?= $row['image'] ?>">
<h4><?= $row['name'] ?></h4>
<p>💰 Rs <?= $row['price'] ?></p>
<p>📈 Sold: <?= $row['total_sold'] ?></p>
<a href="product.php?id=<?= $row['product_id'] ?>" class="btn">View</a>
</div>
<?php endwhile; ?>
</div>

<!-- 📊 TOP CROPS -->
<h3>📊 Top Selling Crops</h3>
<ul>
<?php while($row = mysqli_fetch_assoc($topCrops)): ?>
<li><?= $row['name'] ?> (<?= $row['total_sold'] ?> sold)</li>
<?php endwhile; ?>
</ul>

<!-- 🤖 SMART PICKS -->
<h3>🤖 Smart Picks</h3>
<div class="grid">
<?php while($row = mysqli_fetch_assoc($smartProducts)): ?>
<div class="card">
<img src="uploads/<?= $row['image'] ?>">
<h4><?= $row['name'] ?></h4>
<p>💰 Rs <?= $row['price'] ?></p>
<a href="product.php?id=<?= $row['product_id'] ?>" class="btn">View</a>
</div>
<?php endwhile; ?>
</div>

</div>

<div class="footer">
    © AgroWay <?=date('Y')?>
</div>
</body>
</html>