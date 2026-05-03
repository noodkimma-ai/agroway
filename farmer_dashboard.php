<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id']; 

// Fetch farmer name only
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT name FROM users WHERE user_id=$uid"));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Farmer Dashboard - AgroWay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#ffffff; /* white background */
    margin:0;
    color:#1f2937; /* dark text for contrast */
}

/* HEADER */
header{
    position:fixed;
    top:0;
    width:97%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 40px;
    color:white;
    backdrop-filter: blur(10px);
    background:#79AE6F; /* soft green header */
    z-index:1000;
    border-radius:0 0 15px 15px;
}
header .logo{ font-size:32px; font-weight:bold; color:#346739; }
header a{
    font-size:18px;
    padding:10px 18px;
    border-radius:10px;
    background: #346739;
    text-decoration:none;
    color:white;
    transition:0.3s;
}
header a:hover{ background: #15803d; }

/* CONTAINER */
.container{
    max-width:1100px;
    margin:150px auto 80px;
    padding:20px;
}

/* WELCOME CARD */
.welcome{
    background:#F2EDC2; /* light green */
    padding:25px;
    border-radius:15px;
    margin-bottom:30px;
    color:#065f46;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

/* GRID CARDS */
.grid{
    display:grid;
    grid-template-columns:repeat(3,1fr); /* 3 columns per row */
    gap:25px;
}
.card{
    background:#9FCB98; /* soft green */
    padding:25px;
    border-radius:15px;
    text-align:center;
    color:white;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    transition:0.3s;
}
.card:hover{
    transform:translateY(-5px);
    background:#16a34a; /* darker green on hover */
}
.card a{
    color:white;
    text-decoration:none;
    font-size:18px;
    display:block;
    font-weight:bold;
}

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    background:#79AE6F; /* soft green footer */
    color:white;
    text-align:center;
    padding:15px 0;
    font-size:14px;
    z-index:1000;
    box-shadow:0 -5px 15px rgba(0,0,0,0.15);
}

/* RESPONSIVE */
@media(max-width:1024px){
    .grid{ grid-template-columns:repeat(2,1fr); }
}
@media(max-width:768px){
    .grid{ grid-template-columns:1fr; }
}
</style>
</head>
<body>

<header>
    <div class="logo">🌾 AgroWay</div>
    <a href="logout.php">🚪 Logout</a>
</header>

<div class="container">

<div class="welcome">
    <h2>👋 Welcome, <?= htmlspecialchars($user['name']) ?></h2>
    <p>Manage your products, orders & purchases</p>
</div>

<div class="grid">
    <div class="card"><a href="select_category.php">➕ Add Product</a></div>
    <div class="card"><a href="manage_products.php">✏️ Manage Products</a></div>
    <div class="card"><a href="farmer_orders.php">📦 View Orders</a></div>
    <div class="card"><a href="buy_products.php">🛒 Buy Products</a></div>
    <div class="card"><a href="farmer_profle.php">👤 Personal Info</a></div>
</div>

</div>

<div class="footer">
    &copy; <?= date('Y') ?> AgroWay. All rights reserved.
</div>

</body>
</html>