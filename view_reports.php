<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch counts
$total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$total_buyers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='buyer'"))[0];
$total_farmers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='farmer'"))[0];

$total_products = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM products"))[0];
$total_approved_products = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM products WHERE approved=1"))[0];

$total_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];

$res = mysqli_query($conn,"
    SELECT SUM(oi.qty * oi.price) AS total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'Completed'
");

$row = mysqli_fetch_assoc($res);
$total_revenue = $row['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Reports</title>
<style>
/* --- GENERAL STYLES --- */
body {

    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #e8f5e9;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* TOP NAVBAR */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 95%;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    background:#ffffff; /* navbar green */
    color: #333;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
}
.logo { font-size: 32px; font-weight: bold;
color:#2e7d32; }
.greeting { font-weight: 500; margin-right: 20px; }
.logout a {
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 8px;
     background:#f0f0f0;
    color:#333;
    font-weight: 600;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
.logout a:hover { background: #dc2626; }

/* --- Bottom Menu Bar --- */
.menu-bar {
    position: fixed;
    top: 85px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 18px;
    padding: 15px 0;
    background:#a5d6a7; /* medium green */
    color:#1b5e20;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 999;
}
.menu-bar a {
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 8px;
     background:#f0f0f0;
    color:#333;
    font-weight: 600;
    transition: all 0.3s ease;
}
.menu-bar a:hover { background:#4caf50;; transform: translateY(-2px); }
.menu-bar .active { background: #1e7d34 !important; }

/* CONTENT WRAPPER */
.wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 150px 20px 80px;
    flex: 1;
    background: #DAF9DE; /* soft green like dashboard container */
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    color: #1b5e20; /* dark green text for readability */
}

/* HEADER */
.header h2 { font-size: 34px; margin: 0 0 35px 0; }

/* GRID CARDS */
/* GRID CARDS */
.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 columns per row */
    grid-auto-rows: auto;
    gap: 25px;
    justify-items: stretch; /* cards stretch to fill column */
}

/* RESPONSIVE */
@media(max-width:1024px){
    .grid{ grid-template-columns: repeat(2, 1fr); }
}
@media(max-width:600px){
    .grid{ grid-template-columns: 1fr; }
}

/* CARD */
.card {
    background: #408A71; /* lighter soft green */
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-6px);
    background: #c6f6d5;
}

/* ICON */
.card .icon {
    width: 55px;
    height: 55px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #1f3d1f; /* dark green icon color */
    margin-bottom: 18px;
}

/* TEXT */
.card h4 { margin: 0; font-size: 17px; color: #1f3d1f; }
.card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: #1f3d1f; }

/* FOOTER */
.footer {
    position: fixed;      /* fixed at bottom */
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    padding: 15px 0;
    background: #a5d6a7; /* soft green like wrapper */
    border-top: 1px solid #c8e6c9;
    color:#2e7d32;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
    z-index: 1000;
}

/* RESPONSIVE */
@media(max-width:768px){
    .wrapper{ margin:120px 20px 80px; padding:20px; }
    .grid{ grid-template-columns: 1fr 1fr; }
    .navbar, .menu-bar { width: 100%; padding: 15px 20px; }
}
@media(max-width:480px){
    .grid{ grid-template-columns: 1fr; }
}
</style>
</head>

<body>

<!-- TOP NAVBAR -->
<div class="navbar">
    <div class="logo">🌾 AgroWay</div>
    <div class="greeting">👋 Hello, <?= htmlspecialchars($_SESSION['name']); ?></div>
    <div class="logout"><a href="logout.php">Logout</a></div>
</div>

<!-- BOTTOM MENU BAR -->
<div class="menu-bar">
    <a href="dashboard.php">Dashboard</a>
    <a href="users.php">Manage Users</a>
    <a href="admin_add_category.php">Add Category</a>
    <a href="view_reports.php" class="active">View Report</a>
</div>

<!-- WRAPPER -->
<div class="wrapper">
    <h2>📊 System Reports</h2>

    <div class="grid">
        <div class="card">
            <div class="icon">👥</div>
            <h4>Total Users</h4>
            <p><?= $total_users ?></p>
        </div>

        <div class="card">
            <div class="icon">🛒</div>
            <h4>Total Buyers</h4>
            <p><?= $total_buyers ?></p>
        </div>

        <div class="card">
            <div class="icon">🌾</div>
            <h4>Total Farmers</h4>
            <p><?= $total_farmers ?></p>
        </div>

        <div class="card">
            <div class="icon">📦</div>
            <h4>Total Products</h4>
            <p><?= $total_products ?></p>
        </div>

        <div class="card">
            <div class="icon">✅</div>
            <h4>Approved Products</h4>
            <p><?= $total_approved_products ?></p>
        </div>

        <div class="card">
            <div class="icon">📑</div>
            <h4>Total Orders</h4>
            <p><?= $total_orders ?></p>
        </div>

        <div class="card">
            <div class="icon">💰</div>
            <h4>Total Revenue</h4>
            <p>Rs. <?= number_format($total_revenue) ?></p>
        </div>
    </div>
</div>

<!-- FOOTER -->
<div class="footer">
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</div>

</body>
</html>