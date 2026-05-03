<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* BEST PRODUCT by potential profit */
$bestProduct = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT name, (quantity*(price - cost)) AS profit
FROM products
WHERE farmer_id=$uid
ORDER BY profit DESC
LIMIT 1
"));

/* BEST FARM by potential profit */
$bestFarm = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT farm_id, SUM(quantity*(price - cost)) AS profit
FROM products
WHERE farmer_id=$uid
GROUP BY farm_id
ORDER BY profit DESC
LIMIT 1
"));

/* BEST SEASON by potential profit */
$bestSeason = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT season, SUM(quantity*(price - cost)) AS profit
FROM products
WHERE farmer_id=$uid AND season IS NOT NULL
GROUP BY season
ORDER BY profit DESC
LIMIT 1
"));

/* BEST SOIL */
/* BEST SOIL BASED ON FARMER'S PRODUCTS */
$bestSoil = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT s.soil_name, COUNT(*) AS product_count
FROM products p
JOIN soil_types s ON p.soil_id=s.soil_id
WHERE p.farmer_id=$uid
GROUP BY s.soil_id
ORDER BY product_count DESC
LIMIT 1
"));

/* Get farm name for best farm */
if($bestFarm){
    $farm_id = $bestFarm['farm_id'];
    $farmRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT farm_name FROM farms WHERE farm_id=$farm_id"));
    $bestFarmName = $farmRow['farm_name'] ?? "No Data";
}else{
    $bestFarmName = "No Data";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Smart Recommendation</title>

<style>
<style>
/* BODY & BACKGROUND */
body{
    font-family:'Poppins',sans-serif;
    background:white;                  /* white background */
    margin:0;
    color:#065f46;                     /* dark green text */
}
body::before{
    content:"";
    position:fixed;
    inset:0;
    background:rgba(220,252,231,0.55); /* soft green overlay */
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
.container{
    margin:140px auto 50px;
    width:90%;
    max-width:800px;
    padding:10px;
}

/* PAGE TITLE */
h2{
    text-align:center;
    color:#065f46;                   /* dark green heading */
    margin-bottom:30px;
}

/* CARD */
.card{
    background:#F2EDC2;               /* light green card */
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.15);
}
.card h3{
    margin-top:0;
    color:#065f46;                     /* dark green card title */
}

/* CARD PARAGRAPHS */
.card p{
    font-weight:500;
    color:#065f46;                     /* dark green text */
}
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
<a href="farmer_orders.php" >📋 Orders</a>
<a href="buy_products.php">🛒 Buy</a>
<a href="farmer_profle.php">👤 Personal Info</a>
<a href="farmer_history.php">🕘 History</a>
<a href="farmer_recommendation.php" class="active">🌱 Recommendation</a>
</div>

<div class="container">

<h2 style="text-align:center;">🌱 Smart Crop Recommendation</h2>

<div class="card">
<h3>🏆 Best Crop</h3>
<p><?= $bestProduct['name'] ?? 'No Data' ?></p>
<p>Potential Profit: Rs <?= isset($bestProduct['profit']) ? number_format($bestProduct['profit'],2) : 0 ?></p>
</div>

<div class="card">
<h3>🏡 Best Farm</h3>
<p><?= $bestFarmName ?></p>
<p>Potential Profit: Rs <?= isset($bestFarm['profit']) ? number_format($bestFarm['profit'],2) : 0 ?></p>
</div>

<div class="card">
<h3>🌤️ Best Season</h3>
<p><?= $bestSeason['season'] ?? 'No Data' ?></p>
<p>Potential Profit: Rs <?= isset($bestSeason['profit']) ? number_format($bestSeason['profit'],2) : 0 ?></p>
</div>

<div class="card">
<h3>🌱 Best Soil</h3>
<p><?= $bestSoil['soil_name'] ?? 'No Data' ?></p>
<p>Profit: Rs <?= $bestSoil['profit'] ?? 0 ?></p>
</div>

</div>
<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>