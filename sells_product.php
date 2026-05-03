<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Sell Product - AgroWay</title>

<style>
/* BODY */
body {
    font-family: Poppins, sans-serif;
    background:#ffffff; /* white background */
    margin:0;
    color:#065f46; /* dark green text */
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    width:97%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 30px;
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

/* STATUS SECTION */
.status-container{
    display:flex;
    gap:20px;
    justify-content:center;
    margin:160px auto 80px;
    flex-wrap:wrap;
}

/* CARDS */
.status-card{
    width:260px;
    background:#84B179; /* soft green */
    padding:35px;
    border-radius:14px;
    text-align:center;
    color:#065f46;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    transition:0.3s;
}
.status-card:hover{
    transform:translateY(-5px);
    background:#bbf7d0;
}

/* ICON */
.status-card .icon{
    font-size:40px;
    margin-bottom:10px;
}

/* TEXT */
.status-card h3{
    margin-bottom:10px;
}
.status-card p{
    font-size:14px;
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
    <a href="buyer_manage_products.php">📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
<a href="buyer_recommendation.php">🌱 Recommendation</a>
</div>

<!-- MESSAGE -->
<div class="status-container">

    <div class="status-card">
        <div class="icon">👋</div>
        <h3>Welcome Buyer</h3>
        <p>You can start selling your products from here.</p>
    </div>

    <div class="status-card">
        <div class="icon">📦</div>
        <h3>Add Products</h3>
        <p>Click "Add" in navbar to list your items for sale.</p>
    </div>

    <div class="status-card">
        <div class="icon">📊</div>
        <h3>Manage & Track</h3>
        <p>Manage your products and track orders easily.</p>
    </div>

</div>

<!-- FOOTER -->
<div class="footer">
    © AgroWay <?=date('Y')?>
</div>

</body>
</html>