<?php
session_start();
require 'config.php';

// Admin only
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard | AgroWay</title>

<style>
/* GLOBAL */
/* GLOBAL */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #ffffff; /* pure white */
    color:#333;
}

/* CONTAINER (soft green) */
.container {
    width: 90%;
    max-width: 1150px;
    margin: 40px auto;
    padding:25px;
    background: #e8f5e9; /* light soft green */
    border-radius: 16px;
}

/* HEADER */
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#ffffff;
    padding:15px 20px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.header h2 {
    margin:0;
    color:#2e7d32;
}

/* NAV */
.nav a {
    margin-left:10px;
    padding:7px 14px;
    text-decoration:none;
    border-radius:8px;
    background:#f0f0f0;
    color:#333;
    transition:0.3s;
}

.nav a:hover {
    background:#2e7d32;
    color:#fff;
}

/* WELCOME */
.welcome {
    margin:25px 0;
    padding:25px;
    border-radius:14px;
    background:#a5d6a7; /* medium green */
    color:#1b5e20;
    text-align:center;
}

/* GRID */
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

/* CARD (darker green) */
.card {
    background:#84B179; /* stronger green */
    padding:22px;
    border-radius:14px;
    text-align:center;
    color:white;
    transition:0.3s;
}

.card:hover {
    transform:translateY(-5px);
    background:#4caf50;
}

/* TEXT */
.card h4 {
    margin-top:0;
    font-size:20px;
}

.card p {
    font-size:14px;
    opacity:0.95;
}

/* BUTTON */
.card a {
    display:inline-block;
    margin-top:12px;
    padding:10px 16px;
    background:#1b5e20;
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-weight:500;
}

.card a:hover {
    background:#0d3b12;
}

/* FOOTER */
.footer {
    text-align:center;
    margin-top:70px;
    padding:15px;
    background:#e8f5e9;
    border-top:1px solid #c8e6c9;
}

/* MOBILE */
@media (max-width:768px){
    .container {
        margin:20px;
        padding:15px;
    }
}
</style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>AgroWay</h2>
        <div class="nav">
            Hello, <?= htmlspecialchars($name) ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <hr style="border:1px solid rgba(255,255,255,0.2); margin:20px 0;">

    <!-- WELCOME -->
    <div class="welcome">
        <h3>Welcome Administrator 🌱</h3>
        <p>Manage users and monitor system performance from one place.</p>
    </div>

    <!-- ADMIN ACTIONS -->
    <div class="grid">

        <div class="card">
            <h4>👥 Manage Users</h4>
            <p>Add, edit, block or remove farmers and buyers securely.</p>
            <a href="users.php">Go to Users</a>
        </div>

        <div class="card">
            <h4>➕ Add Category</h4>
            <p>Add category.</p>
            <a href="admin_add_category.php">Add Category</a>
        </div>

        <div class="card">
            <h4>📊 View Reports</h4>
            <p>Track users, products, orders and revenue growth.</p>
            <a href="view_reports.php">View Reports</a>
        </div>

    </div>

</div>

<!-- FOOTER -->
<div class="footer">
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</div>

</body>
</html>