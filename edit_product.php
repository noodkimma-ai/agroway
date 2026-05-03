<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    header("Location: login.php");
    exit;
}

$fid = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$pid = (int)$_GET['id'];

/* Fetch product */
$pq = mysqli_query($conn, "
    SELECT * FROM products 
    WHERE product_id = $pid AND farmer_id = $fid
");

if (mysqli_num_rows($pq) == 0) {
    die("Unauthorized access");
}

$product = mysqli_fetch_assoc($pq);

/* Fetch category name */
$catQ = mysqli_query($conn,"
    SELECT category_name 
    FROM categories 
    WHERE category_id = {$product['category_id']}
");
$cat = mysqli_fetch_assoc($catQ);

/* Fetch subcategories */
$subQ = mysqli_query($conn,"
    SELECT * FROM subcategories 
    WHERE category_id = {$product['category_id']}
");

/* Update product */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $qty  = (float)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $sub_id = (int)$_POST['subcategory_id'];

    $imgSql = "";

    if (!empty($_FILES['image']['name'])) {
        $img = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img");
        $imgSql = ", image='$img'";
    }

    mysqli_query($conn,"
        UPDATE products SET
            name='$name',
            description='$desc',
            quantity=$qty,
            price=$price,
            unit='$unit',
            subcategory_id=$sub_id
            $imgSql
        WHERE product_id=$pid AND farmer_id=$fid
    ");

    header("Location: manage_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<style>
body{
    background:#ecfeff;
    font-family:Poppins,sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.card{
    background:white;
    padding:30px;
    border-radius:16px;
    width:420px;
    box-shadow:0 10px 25px rgba(0,0,0,.1);
}
h2{
    text-align:center;
    margin-bottom:20px;
    color:#14532d;
}
input, select, textarea{
    width:100%;
    padding:12px;
    margin-bottom:12px;
    border-radius:10px;
    border:1px solid #ccc;
}
button{
    width:100%;
    padding:14px;
    background:#22c55e;
    border:none;
    color:white;
    font-weight:600;
    border-radius:12px;
    cursor:pointer;
}
button:hover{
    background:#15803d;
}
.back{
    text-align:center;
    margin-top:15px;
}
.back a{
    text-decoration:none;
    color:#14532d;
    font-weight:600;
}
img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:10px;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="card">
<h2>Edit Product</h2>

<img src="uploads/<?= htmlspecialchars($product['image']) ?>">

<form method="post" enctype="multipart/form-data">

<input type="text" value="<?= htmlspecialchars($cat['category_name']) ?>" disabled>

<select name="subcategory_id" required>
<?php while($s = mysqli_fetch_assoc($subQ)): ?>
<option value="<?= $s['subcategory_id'] ?>"
<?= ($s['subcategory_id'] == $product['subcategory_id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($s['subcategory_name']) ?>
</option>
<?php endwhile; ?>
</select>

<input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

<textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>

<input type="number" step="0.01" name="quantity" value="<?= $product['quantity'] ?>" required>

<input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>

<input type="text" name="unit" value="<?= htmlspecialchars($product['unit']) ?>" required>

<input type="file" name="image">

<button type="submit">Update Product</button>

</form>

<div class="back">
<a href="manage_products.php">← Back to Products</a>
</div>

</div>

</body>
</html>
