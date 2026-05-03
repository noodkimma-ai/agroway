<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Delete user
// Block user instead of deleting
if (isset($_GET['block_id'])) {
    $block_id = intval($_GET['block_id']);
    
    // Make sure admin cannot be blocked
    $res_check = mysqli_query($conn, "SELECT role FROM users WHERE user_id=$block_id");
    $row = mysqli_fetch_assoc($res_check);
    
    if ($row && $row['role'] != 'admin') {
        mysqli_query($conn, "UPDATE users SET status='blocked' WHERE user_id=$block_id");
    }
    
    header("Location: users.php");
    exit;
}

// Fetch users
$res = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Users | AgroWay</title>

<style>
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

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #ffffff; /* white table background */
    border-radius: 12px;
    overflow: hidden;
}
thead { background: #66bb6a; color: white; }
th, td { padding: 12px 10px; font-size: 14px; }
tbody tr { background: #f0f9f0; }
tbody tr:nth-child(even) { background: #e0f4e0; }
tbody tr:hover { background: #c8e6c9; }

/* BADGES */
.badge { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; text-transform: capitalize; }
.badge.admin { background:#ef4444; }
.badge.farmer { background:#22c55e; }
.badge.buyer { background:#3b82f6; }

/* ACTION BUTTONS */
.actions a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    color: white;
    margin-right: 5px;
    font-size: 14px;
}
.edit { background:#16a34a; }
.delete { background:#ef4444; }

/* FOOTER */
/* FIXED FOOTER */
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
    .navbar, .menu-bar { flex-direction: column; align-items: flex-start; padding: 15px 20px; }
    .menu-bar a { padding: 6px 12px; font-size: 12px; }
    .wrapper { padding: 120px 15px 70px; }
    table, th, td { font-size: 12px; }
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
    <a href="dashboard.php">dashboard</a>
    <a href="users.php" class="active">Manage Users</a>
    <a href="admin_add_category.php">Add Category</a>
    <a href="view_reports.php">View Report</a>
</div>

<!-- CONTENT -->
<div class="wrapper">
<h2>👥 Manage Users</h2>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
<?php while($r = mysqli_fetch_assoc($res)): ?>
<tr>
    <td><?= $r['user_id']; ?></td>
    <td><?= htmlspecialchars($r['name']); ?></td>
    <td><?= htmlspecialchars($r['email']); ?></td>
    <td>
        <span class="badge <?= $r['role']; ?>">
            <?= $r['role']; ?>
        </span>
    </td>
    <td class="actions">
    <a class="edit" href="edit_user.php?edit_id=<?= $r['user_id']; ?>">Edit</a>
    <?php if($r['role'] != 'admin'): ?>
    <a class="delete"
       href="users.php?block_id=<?= $r['user_id']; ?>"
       onclick="return confirm('Are you sure you want to block this user?');">
       Block
    </a>
    <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

<!-- FOOTER -->
<div class="footer">
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</div>

</body>
</html>