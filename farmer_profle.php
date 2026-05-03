<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php"); 
    exit;
}

$uid = $_SESSION['user_id'];

/* Farmer info */
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT name,email,address FROM users WHERE user_id=$uid"));

/* Total monthly income */
$monthlyIncomeRes = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT IFNULL(SUM(oi.qty * oi.price),0) AS income
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE p.farmer_id = $uid
"));
$monthly_income = $monthlyIncomeRes['income'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Farmer Personal Info - AgroWay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* BODY & BACKGROUND */
body{
    font-family:'Poppins',sans-serif;
    background:white;   /* white background */
    margin:0;
    color:#065f46;      /* dark green text */
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
    max-width:900px; margin:160px auto 80px; padding:20px;
    background:#dcfce7; /* soft green container */
    border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

/* CARD */
.card{
    background:#F2EDC2; /* lighter green card */
    padding:20px; border-radius:12px; color:#065f46;
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
}
.card h2{ margin-bottom:15px; color:#22c55e; }
.card p{ margin:5px 0; font-weight:500; }

/* BUTTON */
.view-charts{
    display:inline-block; margin-top:15px; padding:10px 18px;
    background:#22c55e; color:white; text-decoration:none; border-radius:8px;
    transition:0.3s;
}
.view-charts:hover{ background:#15803d; }

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
    <div class="card">
        <h2>👤 Personal Info</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Address:</strong> <?= !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not Provided' ?></p>
        <p><strong>Total Income:</strong> Rs. <?= number_format($monthlyIncomeRes['income'],2) ?></p>

        <!-- Button to open farm charts -->
        <a href="fram_charts.php" class="view-charts">📊 View Farm Charts</a>
    </div>
</div>

<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>