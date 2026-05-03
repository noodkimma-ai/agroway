<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* ---------------------------
   SOFT DELETE PRODUCT
---------------------------- */
if(isset($_GET['delete_id'])){
    $pid = (int)$_GET['delete_id'];
    mysqli_query($conn, "UPDATE products SET is_deleted=1 WHERE product_id=$pid AND farmer_id=$uid");
    $_SESSION['msg'] = "Your product has been deleted. Check History to restore.";
    header("Location: buyer_manage_products.php");
    exit;
}

/* ---------------------------
   PAGINATION SETTINGS
---------------------------- */
$limit = 6; // products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total products (excluding soft deleted if you want)
$totalRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE farmer_id=$uid AND is_deleted=0");
$totalRow = mysqli_fetch_assoc($totalRes);
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $limit);

// Fetch products with farm info and category (excluding deleted)
$res = mysqli_query($conn,"
    SELECT p.*, c.category_name, f.farm_name, f.farm_location
    FROM products p
    LEFT JOIN categories c ON p.category_id=c.category_id
    LEFT JOIN farms f ON p.farm_id=f.farm_id
    WHERE p.farmer_id=$uid AND p.is_deleted=0
    ORDER BY p.created_at DESC
    LIMIT $start, $limit
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Products - AgroWay</title>
<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins, sans-serif;
}

/* BODY */
body{
    background:#ffffff;
    color:#065f46;
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

/* CONTAINER */
.container{
    max-width:1100px;
    margin:170px auto 80px; /* FIXED spacing */
    padding:20px;
}

/* HEADING */
.container h2{
    text-align:center;
    margin-bottom:20px;
    color:#065f46;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
}

/* CARD */
.card{
    background:#84B179;
    padding:15px;
    border-radius:12px;
    display:flex;
    flex-direction:column;
    gap:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

.card img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:10px;
}

/* CARD TEXT */
.card-info h3{
    margin-bottom:5px;
    color:#065f46;
}

.card-info p{
    font-size:14px;
}

/* ACTION BUTTONS */
.card-info .actions a{
    display:inline-block;
    margin-right:8px;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    background:#22c55e;
}

.card-info .actions a.delete{
    background:#dc2626;
}

.card-info .actions a:hover{
    opacity:0.85;
}

/* PAGINATION */
.pagination{
    text-align:center;
    margin-top:20px;
}

.pagination a{
    margin:0 5px;
    padding:6px 12px;
    background:#bbf7d0;
    color:#065f46;
    text-decoration:none;
    border-radius:6px;
}

.pagination a.active{
    background:#22c55e;
    color:white;
}

.pagination a:hover{
    background:#16a34a;
    color:white;
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
    <a href="buyer_manage_products.php" class="active">📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
    <a href="buyer_history.php">🕘 History</a>
    <a href="buyer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">
<h2>My Products</h2>

<?php
if(isset($_SESSION['msg'])){
    echo "<p style='text-align:center;color:red;margin-bottom:15px;'>{$_SESSION['msg']}</p>";
    unset($_SESSION['msg']);
}
?>

<div class="grid">
<?php while($p=mysqli_fetch_assoc($res)): ?>
<div class="card">
    <img src="uploads/<?= htmlspecialchars($p['image'] ? $p['image'] : 'no-image.png') ?>">
    <div class="card-info">
        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <p>Category: <?= htmlspecialchars($p['category_name']) ?></p>
        <p>Farm: <?= htmlspecialchars($p['farm_name']) ?> (<?= htmlspecialchars($p['farm_location']) ?>)</p>
        <p>Qty: <?= intval($p['quantity']) ?> | Price: Rs. <?= $p['price'] ?></p>
        <div class="actions">
            <a href="buyer_edit_product.php?id=<?= $p['product_id'] ?>">Edit</a>
            <a href="buyer_manage_products.php?delete_id=<?= $p['product_id'] ?>" 
               class="delete"
               onclick="return confirm('Are you sure you want to delete this product?')">
               Delete
            </a>
        </div>
    </div>
</div>
<?php endwhile; ?>
</div>

<!-- Pagination Links -->
<div class="pagination">
<?php
for($i=1;$i<=$totalPages;$i++){
    $active = ($i==$page) ? "active" : "";
    echo "<a class='$active' href='buyer_manage_products.php?page=$i'>$i</a>";
}
?>
</div>

</div>
<div class="footer">
    © AgroWay <?=date('Y')?>
</div>

</body>
</html>