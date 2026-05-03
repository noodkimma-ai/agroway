<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Delete category
if (isset($_GET['delete_cat_id'])) {
    $delete_id = intval($_GET['delete_cat_id']);
    mysqli_query($conn, "DELETE FROM categories WHERE category_id = $delete_id");
    header("Location: admin_add_category.php"); 
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cat = mysqli_real_escape_string($conn, $_POST['category_name']);
    if (!empty($cat)) {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$cat')");
        header("Location: admin_add_category.php");
        exit;
    } else {
        $error = "Category name cannot be empty";
    }
}

$cats = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin | Add Category</title>
<style>
/* --- General Styles --- */
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

/* --- Form --- */
form {
    background: #d0efd0; /* slightly lighter green */
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
}
form input[type="text"] {
    width: calc(100% - 20px);
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #b2dfb2;
    margin-bottom: 12px;
}
form button {
    padding: 10px 18px;
    background: #86efac; /* light green button */
    color: #1f3d1f;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
form button:hover { background: #4caf50; }
.error { color: #dc2626; margin-bottom: 12px; }

/* --- List of Categories --- */
ul {
    list-style: none;
    padding-left: 0;
}
ul li {
    background: #c6f6d5; /* soft green item */
    padding: 10px 15px;
    margin-bottom: 8px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
ul li a {
    color: #dc2626;
    text-decoration: none;
    font-weight: bold;
}
ul li a:hover { text-decoration: underline; }

/* --- Footer --- */
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

/* --- Responsive --- */
@media(max-width:768px){
    .wrapper{ padding: 150px 20px 80px; }
    .navbar, .menu-bar { width: 100%; padding: 15px 20px; }
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
    <a href="admin_add_category.php" class="active">Add Category</a>
    <a href="view_reports.php">View Report</a>
</div>

<!-- CONTENT -->
<div class="wrapper">

<h2>Admin Dashboard - Add Category</h2>

<?php if($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <input type="text" name="category_name" placeholder="Category Name" required>
    <button type="submit">Add Category</button>
</form>

<h3>Existing Categories</h3>
<ul>
<?php while($c=mysqli_fetch_assoc($cats)): ?>
    <li>
        <?= htmlspecialchars($c['category_name']) ?>
        <a href="admin_add_category.php?delete_cat_id=<?= $c['category_id']; ?>"
           onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
    </li>
<?php endwhile; ?>
</ul>

</div>

<!-- FOOTER -->
<div class="footer">
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</div>

</body>
</html>