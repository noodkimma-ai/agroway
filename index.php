<?php
session_start();
require 'config.php';

// Fetch categories
$catRes = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

// Get selected category
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Search
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Query for products with correct farms table
$sql = "SELECT 
            p.*, 
            u.name AS farmer, 
            c.category_name AS category,
            f.farm_name,
            f.farm_location
        FROM products p 
        JOIN users u ON p.farmer_id = u.user_id 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        LEFT JOIN farms f ON p.farm_id = f.farm_id
        WHERE p.is_deleted=0 ";  // 🔹 Exclude deleted products

if ($categoryFilter > 0) {
    $sql .= "AND p.category_id = $categoryFilter ";
}

if (!empty($searchQuery)) {
    $sql .= "AND p.name LIKE '%$searchQuery%' ";
}

$sql .= "ORDER BY p.created_at DESC";

$res = mysqli_query($conn, $sql) or die(mysqli_error($conn));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Smart Farmer Marketplace</title>
<style>
body {
    margin:0;
    font-family: "Poppins", sans-serif;
    background:#f5f7fa; /* clean white/light bg */
    color:#333;
}

/* HEADER */
.header {
    position:fixed;
    top:0;
    left:0;
    width:97%;
    background:#bce3c9;
    color:#333;
    z-index:1000;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.header h2 { font-size:26px; }

.nav a {
    color:#333;
    margin-left:12px;
    text-decoration:none;
    padding:7px 12px;
    border-radius:6px;
    background:#f0f0f0;
    transition:0.3s;
}

.nav a:hover { background:#28a745; color:white; }

.toggle-btn {
    cursor:pointer;
    background:#28a745;
    color:white;
    padding:7px 12px;
    border-radius:6px;
}

/* CONTAINER */
.container {
    width:90%;
    max-width:1150px;
    margin:140px auto 100px;
}

/* SEARCH */
.search-box { text-align:center; margin-bottom:25px; }

.search-input {
    width:55%;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
}

.category-select {
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
}

.search-box button {
    padding:10px 15px;
    border-radius:8px;
    border:none;
    background:#28a745;
    color:white;
    font-weight:bold;
}

.search-box button:hover { background:#1f7f36; }

/* INFO CARDS */
.info-section {
    display:flex;
    gap:20px;
    margin-bottom:35px;
    flex-wrap:wrap;
}

.info-card {
    flex:1;
    background:	#b2d8d8;
    padding:22px;
    border-radius:14px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

/* PRODUCT GRID */
.product-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:22px;
}

/* 🎨 CARD COLOR UPDATED */
.product-card {
    display:flex;
    flex-direction:column;
    gap:10px;
    background:	#c9e9d4; /* light green card */
    padding:15px;
    border-radius:14px;
    transition:0.25s;
    color:#333;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

.product-card:hover {
    transform:translateY(-5px);
    background:#d0f0d4;
}

.product-card img {
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:10px;
}

.product-info h4 { margin:0; }

.product-info .price {
    color:#2e7d32;
    font-weight:600;
}

.product-info .meta {
    font-size:14px;
}

.product-info .desc {
    font-size:13px;
}

/* BUTTON */
.btn {
    display:inline-block;
    margin-top:8px;
    padding:8px 14px;
    background:#28a745;
    color:white;
    border-radius:6px;
    text-decoration:none;
    font-weight:bold;
}

.btn:hover { background:#1f7f36; }

/* RATING */
.rating span { color:orange; }

/* FOOTER */
.footer {
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    background:#bce3c9;
    color:#333;
    text-align:center;
    padding:15px 0;
    box-shadow:0 -2px 10px rgba(0,0,0,0.1);
}

/* MOBILE */
@media(max-width:768px){
    .product-grid { grid-template-columns:1fr; }
    .info-section { flex-direction:column; }
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <h2>AgroWay 🌾</h2>
    <div class="nav">
        <?php if(isset($_SESSION['user_id'])): ?>
            Hello, <?=htmlspecialchars($_SESSION['name'])?>
            <a href="cart.php">Cart</a>
            <a href="sells_product.php">➕ Sell Product</a>
            <a href="logout.php">Logout</a>
        <?php else: ?> 
            <a href="register.php">Register</a>
            <a href="login.php">🚪 Login</a>
        <?php endif; ?>
        <span class="toggle-btn" onclick="toggleDark()">🌙</span>
    </div>
</div>

<div class="container">

    <!-- SEARCH BOX -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search products..." value="<?=htmlspecialchars($searchQuery)?>" class="search-input">
            <select name="category" class="category-select">
                <option value="0">All Categories</option>
                <?php while($c = mysqli_fetch_assoc($catRes)): ?>
                    <option value="<?=$c['category_id']?>" <?=($categoryFilter==$c['category_id'])?"selected":""?>>
                        <?=htmlspecialchars($c['category_name'])?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- INFO CARDS -->
    <?php if(empty($searchQuery) && $categoryFilter==0): ?>
    <div class="info-section">
        <div class="info-card">
            <div class="info-icon">🌾</div>
            <h3>Fresh from Farmers</h3>
            <p>All products come directly from real, verified farmers.</p>
        </div>
        <div class="info-card">
            <div class="info-icon">💚</div>
            <h3>Support Local Farmers</h3>
            <p>Your purchase helps farmers grow and sustain their livelihood.</p>
        </div>
        <div class="info-card">
            <div class="info-icon">✔️</div>
            <h3>Best Quality</h3>
            <p>100% naturally grown & carefully selected products.</p>
        </div>
    </div>
    <?php endif; ?>

    <h3 style="color:white; text-align:center; margin-bottom:20px;">Products</h3>

    <!-- PRODUCTS GRID -->
    <div class="product-grid">
    <?php while($row = mysqli_fetch_assoc($res)): ?>
        <div class="product-card">
            <img src="uploads/<?=htmlspecialchars($row['image'])?>">
            <div class="product-info">
                <h4><?=htmlspecialchars($row['name'])?></h4>
                <p class="price">Rs. <?=$row['price']?> / <?=$row['unit']?></p>
                <p class="meta">
                    Farmer: <?=htmlspecialchars($row['farmer'])?><br>
                    Farm: <?=htmlspecialchars($row['farm_name'] ?: '-')?><br>
                    Farm Location: <?=htmlspecialchars($row['farm_location'] ?: '-')?><br>
                    Category: <?=htmlspecialchars($row['category'])?><br>
                    Quantity: <?=intval($row['quantity'])?>
                </p>
                <p class="desc"><?=htmlspecialchars(substr($row['description'],0,90))?>...</p>

                <!-- STAR RATING -->
                <div class="rating">
                    <?php
                        $rating = isset($row['rating']) ? round($row['rating']) : rand(3,5);
                        for($i=1;$i<=5;$i++){
                            echo $i <= $rating ? "<span>★</span>" : "<span style='color:#444;'>☆</span>";
                        }
                    ?>
                </div>

                <?php if(intval($row['quantity']) > 0): ?>
                    <a class="btn" href="product.php?id=<?=$row['product_id']?>">View</a>
                <?php else: ?>
                    <span style="color:#ff6666; font-weight:bold;">Out of Stock</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

</div>

<div class="footer">
    &copy; <?=date('Y')?> AgroWay. All rights reserved.
</div>

<script>
function toggleDark(){
    document.body.classList.toggle("dark");
}
</script>

</body>
</html>