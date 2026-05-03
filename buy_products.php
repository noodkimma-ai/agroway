<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$fid = (int)$_SESSION['user_id'];

// Fetch products from other farmers with avg rating
$res = mysqli_query($conn, "
    SELECT 
        p.product_id, p.name, p.price, p.unit, p.image, p.quantity,
        c.category_name,
        u.name AS farmer_name, u.phone,
        IFNULL(AVG(r.rating),0) AS avg_rating,
        COUNT(r.rating_id) AS rating_count,
        (SELECT rating FROM ratings WHERE product_id=p.product_id AND user_id=$fid) AS my_rating
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    JOIN users u ON p.farmer_id = u.user_id
    LEFT JOIN ratings r ON r.product_id = p.product_id
    WHERE p.farmer_id != $fid AND p.quantity > 0
    GROUP BY p.product_id
    ORDER BY p.product_id DESC
");

// Fetch cart count
$cartRes = mysqli_query($conn,"SELECT * FROM carts WHERE user_id=$fid");
$cart = mysqli_fetch_assoc($cartRes);
$cart_id = $cart ? $cart['cart_id'] : 0;
$cartItems = [];
if($cart_id){
    $cartRes2 = mysqli_query($conn,"SELECT product_id, qty FROM cart_items WHERE cart_id=$cart_id");
    while($row = mysqli_fetch_assoc($cartRes2)){
        $cartItems[$row['product_id']] = $row['qty'];
    }
}
$totalCartItems = count($cartItems);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Buy Products - AgroWay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* BODY & BACKGROUND */
body {
    font-family:'Poppins',sans-serif;
    background:white;   /* changed from vegetable image */
    margin:0;
    color:#065f46;       /* dark green text */
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
    margin:140px auto 80px;
    padding:20px;
    background:#dcfce7; /* soft green container */
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* GRID CARDS */
.grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; }
.card{
    background:#F2EDC2; /* lighter green for cards */
    padding:20px;
    border-radius:15px;
    text-align:center;
    color:#065f46; /* dark green text */
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    transition:0.3s;
}
.card:hover{ transform:scale(1.03); background:#a7f3d0; }
.card img{ width:100%; height:180px; object-fit:cover; border-radius:10px; margin-bottom:10px;}
.card h4{ margin:10px 0 5px; }
.card p{ margin:5px 0; font-size:14px; }
.card input[type=number]{ width:60px; padding:5px; border-radius:6px; border:none; margin-bottom:10px;}
.card button{ padding:8px 12px; background:#22c55e; border:none; color:white; border-radius:6px; cursor:pointer;}
.card button:hover{ background:#15803d; }

/* STAR RATING */
.star-rating i{ font-size:18px; color:#065f46; cursor:pointer; margin:0 2px; }
.star-rating i.hover, .star-rating i.selected{ color:#facc15; } /* yellow stars */

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
<script>
// Star rating
function setRating(el, pid){
    let stars = el.parentElement.querySelectorAll('i');
    let rating = 0;
    stars.forEach((star, index)=>{
        star.classList.remove('selected');
        if(star===el){ rating=index+1; }
    });
    for(let i=0;i<rating;i++){
        stars[i].classList.add('selected');
    }
    // send rating via AJAX
    fetch('rate_product.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'product_id='+pid+'&rating='+rating
    }).then(r=>r.text()).then(data=>console.log(data));
}
function hoverStars(el){
    let stars = el.parentElement.querySelectorAll('i');
    let index = Array.from(stars).indexOf(el);
    stars.forEach((s,i)=> s.classList.toggle('hover', i<=index));
}
function clearHover(el){
    let stars = el.parentElement.querySelectorAll('i');
    stars.forEach(s=>s.classList.remove('hover'));
}
</script>
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
<a href="buy_products.php" class="active">🛒 Buy</a>
<a href="farmer_profle.php">👤 Personal Info</a>
<a href="farmer_history.php">🕘 History</a>
<a href="farmer_recommendation.php" >🌱 Recommendation</a>
</div>
    
   


<div class="container">
<h2>📦 Buy Products from Other Farmers</h2>
<div class="grid">
<?php while($p = mysqli_fetch_assoc($res)):
    $image = (!empty($p['image']) && file_exists("uploads/".$p['image'])) ? "uploads/".$p['image'] : "assets/no-image.png";
    $inCart = isset($cartItems[$p['product_id']]);
?>
<div class="card">
    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    <h4><?= htmlspecialchars($p['name']) ?></h4>
    <p><b>Category:</b> <?= htmlspecialchars($p['category_name']) ?></p>
    <p><b>Price:</b> Rs. <?= $p['price'] ?> / <?= htmlspecialchars($p['unit']) ?></p>
    <p><b>Available:</b> <?= $p['quantity'] ?></p>
    <p><b>Farmer:</b> <?= htmlspecialchars($p['farmer_name']) ?></p>

    <!-- Star Rating -->
    <div class="star-rating">
    <?php
    $avg = round($p['avg_rating']);
    for($i=1;$i<=5;$i++){
        $cls = $i <= $avg ? 'selected' : '';
        echo '<i class="fa fa-star '.$cls.'" onclick="setRating(this,'.$p['product_id'].')" onmouseover="hoverStars(this)" onmouseout="clearHover(this)"></i>';
    }
    ?>
    </div>

    <!-- Add to Cart -->
    <?php if($inCart): ?>
        <p style="color:#facc15;font-weight:bold;">Already in Cart (<?= $cartItems[$p['product_id']] ?>)</p>
    <?php else: ?>
    <form method="post" action="addtocart.php">
        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
        <input type="number" name="qty" min="1" max="<?= $p['quantity'] ?>" value="1" required>
        <br>
        <button type="submit">Add to Cart</button>
    </form>
    <?php endif; ?>
</div>
<?php endwhile; ?>
</div>
</div>

<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>
</body>
</html>