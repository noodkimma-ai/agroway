<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* PRODUCT POTENTIAL PROFIT (PIE CHART) */
$productRes = mysqli_query($conn,"
SELECT name, (quantity*(price - cost)) AS profit
FROM products
WHERE farmer_id=$uid
");

$product_labels = [];
$product_data = [];

while($row=mysqli_fetch_assoc($productRes)){
    $product_labels[] = $row['name'];
    $product_data[] = (float)$row['profit'];
}

/* FARM POTENTIAL PROFIT (BAR CHART) */
$farmRes = mysqli_query($conn,"
SELECT f.farm_name, SUM(p.quantity*(p.price - p.cost)) AS profit
FROM products p
JOIN farms f ON p.farm_id=f.farm_id
WHERE p.farmer_id=$uid
GROUP BY f.farm_id
");

$farm_labels = [];
$farm_data = [];

while($row=mysqli_fetch_assoc($farmRes)){
    $farm_labels[] = $row['farm_name'];
    $farm_data[] = (float)$row['profit'];
}

/* SEASON POTENTIAL PROFIT (BAR CHART) */
$seasonRes = mysqli_query($conn,"
SELECT season, SUM(quantity*(price - cost)) AS profit
FROM products
WHERE farmer_id=$uid AND season IS NOT NULL
GROUP BY season
");

$season_labels = [];
$season_data = [];

while($row=mysqli_fetch_assoc($seasonRes)){
    $season_labels[] = $row['season'];
    $season_data[] = (float)$row['profit'];
}

/* BEST VALUES */
$bestProduct = count($product_data) ? $product_labels[array_keys($product_data, max($product_data))[0]] : 'N/A';
$bestFarm = count($farm_data) ? $farm_labels[array_keys($farm_data, max($farm_data))[0]] : 'N/A';
$bestSeason = count($season_data) ? $season_labels[array_keys($season_data, max($season_data))[0]] : 'N/A';
?>

<!DOCTYPE html>
<html>
<head>
<title>Farm Charts - AgroWay</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* BODY & BACKGROUND */
body{
    font-family:'Poppins',sans-serif;
    background:white;          /* white background */
    margin:0;
    color:#065f46;             /* dark green text */
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
    max-width:1000px;
    margin:140px auto 50px;
    display:flex;
    flex-direction:column;
    gap:20px;
    padding:10px;
    background:#dcfce7;       /* soft green container */
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

/* CARD */
.card{
    background:#F2EDC2;       /* lighter green card */
    padding:20px;
    border-radius:12px;
    backdrop-filter:blur(10px);
    text-align:center;
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
}
.card h2{ margin-bottom:15px; color:#22c55e; }
.card h3{ margin-top:15px; color:#065f46; }

/* CHART BOX */
.chart-box{
    width:300px;
    height:300px;
    margin:auto;
}
canvas{
    width:100% !important;
    height:100% !important;
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
<a href="farmer_orders.php" >📋 Orders</a>
<a href="buy_products.php">🛒 Buy</a>
<a href="farmer_profle.php" class="active">👤 Personal Info</a>
<a href="farmer_history.php">🕘 History</a>
<a href="farmer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">

<!-- PRODUCT PIE -->
<div class="card">
<h2>📊 Product Potential Profit</h2>
<div class="chart-box">
<canvas id="productChart"></canvas>
</div>
<h3>🏆 Best Product: <?= $bestProduct ?></h3>
</div>

<!-- FARM CHART -->
<div class="card">
<h2>🏡 Farm Comparison</h2>
<div class="chart-box">
<canvas id="farmChart"></canvas>
</div>
<h3>🏆 Best Farm: <?= $bestFarm ?></h3>
</div>

<!-- SEASON CHART -->
<div class="card">
<h2>🌤️ Season Comparison</h2>
<div class="chart-box">
<canvas id="seasonChart"></canvas>
</div>
<h3>🏆 Best Season: <?= $bestSeason ?></h3>
</div>

</div>

<div class="footer">© AgroWay</div>

<script>

/* PRODUCT PIE CHART */
new Chart(document.getElementById('productChart'), {
    type:'pie',
    data:{
        labels: <?= json_encode($product_labels) ?>,
        datasets:[{
            data: <?= json_encode($product_data) ?>,
            backgroundColor:[
                '#16a34a','#22d3ee','#facc15',
                '#f87171','#3b82f6','#a855f7','#f97316'
            ]
        }]
    }
});

/* FARM BAR CHART */
new Chart(document.getElementById('farmChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode($farm_labels) ?>,
        datasets:[{
            data: <?= json_encode($farm_data) ?>,
            backgroundColor:'#22c55e'
        }]
    }
});

/* SEASON BAR CHART */
new Chart(document.getElementById('seasonChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode($season_labels) ?>,
        datasets:[{
            data: <?= json_encode($season_data) ?>,
            backgroundColor:'#3b82f6'
        }]
    }
});

</script>
<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>
</body>
</html>