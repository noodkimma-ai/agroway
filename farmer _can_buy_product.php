<?php
session_start();
require 'config.php';

/* Allow only farmer */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$fid = (int)$_SESSION['user_id'];

/* Category filter (optional) */
$filterCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

/* Fetch categories for filter */
$catRes = mysqli_query($conn,"SELECT * FROM categories");

/* Build product query */
$sql = "
SELECT p.*, c.category_name, u.name AS farmer_name
FROM products p
JOIN categories c ON p.category_id = c.category_id
JOIN users u ON p.farmer_id = u.user_id
WHERE p.farmer_id != $fid
";

if($filterCategory > 0){
    $sql .= " AND p.category_id = $filterCategory ";
}

$sql .= " ORDER BY c.category_name, p.subcategory";

$res = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Buy Products</title>
<style>
body{font-family:Poppins;background:#ecfeff;margin:0;padding:20px}
h2{text-align:center}
.filter{text-align:center;margin-bottom:20px}
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:20px
}
.card{
    background:#fff;
    padding:15px;
    border-radius:14px;
    box-shadow:0 10px 20px rgba(0,0,0,.1)
}
img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:10px
}
button{
    padding:8px 14px;
    background:#22c55e;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer
}
button:hover{background:#15803d}
select,input{padding:8px;border-radius:6px;border:1px solid #ccc}
.empty{text-align:center;color:#555;margin-top:50px}
</style>
</head>
<body>

<h2>🛒 Buy Products from Other Farmers</h2>

<!-- CATEGORY FILTER -->
<div class="filter">
<form method="get">
<select name="category_id" onchange="this.form.submit()">
<option value="">All Categories</option>
<?php while($c=mysqli_fetch_assoc($catRes)): ?>
<option value="<?= $c['category_id'] ?>"
<?= ($filterCategory==$c['category_id'])?'selected':'' ?>>
<?= htmlspecialchars($c['category_name']) ?>
</option>
<?php endwhile; ?>
</select>
</form>
</div>

<div class="grid">
<?php if(mysqli_num_rows($res)==0): ?>
    <div class="empty">No products available</div>
<?php else: ?>
<?php while($p=mysqli_fetch_assoc($res)): ?>
<div class="card">
<img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="product">

<h4><?= htmlspecialchars($p['name']) ?></h4>

<p>
<b><?= htmlspecialchars($p['category_name']) ?></b> →
<?= htmlspecialchars($p['subcategory']) ?>
</p>

<p>Rs. <?= number_format($p['price'],2) ?> / <?= htmlspecialchars($p['unit']) ?></p>

<p>Seller: <?= htmlspecialchars($p['farmer_name']) ?></p>

<form method="post" action="add_to_cart.php">
<input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
<input type="number" name="qty" value="1" min="1" max="<?= $p['quantity'] ?>" required>
<button type="submit">Add to Cart</button>
</form>

</div>
<?php endwhile; ?>
<?php endif; ?>
</div>

</body>
</html>
