<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Get product id from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product info
$productRes = mysqli_query($conn, "SELECT * FROM products WHERE product_id=$product_id AND farmer_id=$uid") or die(mysqli_error($conn));
if(mysqli_num_rows($productRes)==0){
    die("Product not found or you don't have permission to edit.");
}
$product = mysqli_fetch_assoc($productRes);

// Fetch categories & subcategories
$catRes = mysqli_query($conn,"SELECT * FROM categories");
$subRes = mysqli_query($conn,"SELECT * FROM subcategories");

$success = "";

if(isset($_POST['update_product'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $subcategory_id = (int)$_POST['subcategory_id'];
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['quantity'];
    $unit = mysqli_real_escape_string($conn,$_POST['unit']);
    $desc = mysqli_real_escape_string($conn,$_POST['description']);
    $farm_id = (int)$_POST['farm_id'];
    $season = mysqli_real_escape_string($conn,$_POST['season']);

    // Image upload
    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $img = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$img);
    } else {
        $img = $product['image']; // keep old image
    }

    // Update product
    mysqli_query($conn,"
        UPDATE products SET
        farm_id=$farm_id,
        category_id=$category_id,
        subcategory_id=$subcategory_id,
        name='$name',
        description='$desc',
        quantity=$qty,
        price=$price,
        unit='$unit',
        season='$season',
        image='$img'
        WHERE product_id=$product_id AND farmer_id=$uid
    ") or die(mysqli_error($conn));

    $success = "✅ Product updated successfully!";
    // Refresh product info
    $productRes = mysqli_query($conn, "SELECT * FROM products WHERE product_id=$product_id AND farmer_id=$uid");
    $product = mysqli_fetch_assoc($productRes);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Product - AgroWay</title>
<style>
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
.header h2{ font-size:26px; color:#346739; }
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
    top:75px;
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
    max-width:500px;
    margin:150px auto 80px;
    background:#84B179;
    padding:30px;
    border-radius:12px;
    backdrop-filter:blur(10px);
    color:white;
}

/* FORM */
input, select, textarea{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:6px;
    border:none;
}
button{
    background:#22c55e;
    color:white;
    border:none;
    padding:10px;
    width:100%;
    border-radius:8px;
    cursor:pointer;
}

/* SUCCESS MESSAGE */
.success{
    background:#d1fae5;
    color:#065f46;
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
}

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    width:100%;
    text-align:center;
    padding:12px;
    color:white;
    background:#bce3c9;
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
    <a href="buyer_edit_product.php" class="active">Edit Product</a>
    <a href="buyer_add_product.php">➕ Add</a>
    <a href="buyer_manage_products.php">📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
    <a href="buyer_recommendation.php">🌱 Recommendation</a>
</div>

<div class="container">
<h2>✏️ Edit Product</h2>

<?php if($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<select name="category_id" required>
<option value="">Select Category</option>
<?php
mysqli_data_seek($catRes,0);
while($c=mysqli_fetch_assoc($catRes)): ?>
<option value="<?= $c['category_id'] ?>" <?=($product['category_id']==$c['category_id'])?"selected":""?>><?= $c['category_name'] ?></option>
<?php endwhile; ?>
</select>

<select name="subcategory_id" required>
<option value="">Select Subcategory</option>
<?php
mysqli_data_seek($subRes,0);
while($s=mysqli_fetch_assoc($subRes)): ?>
<option value="<?= $s['subcategory_id'] ?>" <?=($product['subcategory_id']==$s['subcategory_id'])?"selected":""?>><?= $s['subcategory_name'] ?></option>
<?php endwhile; ?>
</select>

<select name="farm_id" required>
<option value="">Select Farm</option>
<?php
for($i=1;$i<=5;$i++){
    $selected = ($product['farm_id']==$i)?"selected":"";
    echo "<option value='$i' $selected>Farm $i</option>";
}
?>
</select>

<select name="season" required>
<option value="">Select Season</option>
<?php 
$seasons = ["Spring","Summer","Autumn","Winter","All Season"];
foreach($seasons as $s){
    $sel = ($product['season']==$s)?"selected":"";
    echo "<option value='$s' $sel>$s</option>";
}
?>
</select>

<input type="text" name="name" placeholder="Product Name" value="<?= htmlspecialchars($product['name']) ?>" required>
<input type="number" name="price" placeholder="Price" value="<?= $product['price'] ?>" required>
<input type="number" name="quantity" placeholder="Quantity" value="<?= $product['quantity'] ?>" required>

<select name="unit">
<option value="kg" <?=($product['unit']=="kg")?"selected":""?>>kg</option>
<option value="piece" <?=($product['unit']=="piece")?"selected":""?>>piece</option>
<option value="dozen" <?=($product['unit']=="dozen")?"selected":""?>>dozen</option>
</select>

<textarea name="description" placeholder="Description"><?= htmlspecialchars($product['description']) ?></textarea>

<p>Current Image:</p>
<?php if($product['image'] && file_exists("uploads/".$product['image'])): ?>
<img src="uploads/<?= $product['image'] ?>" width="120" style="border-radius:8px;margin-bottom:10px;">
<?php endif; ?>

<input type="file" name="image">

<button name="update_product">Update Product</button>

</form>
</div>

<div class="footer">
    © AgroWay <?=date('Y')?>
</div>

</body>
</html>