<?php
session_start();
require 'config.php';

// Only allow farmers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

// Ensure category is selected
if(!isset($_GET['category_id'])){
    header("Location: select_category.php");
    exit;
}

$category_id = (int)$_GET['category_id'];

// Fetch category name
$catRes = mysqli_query($conn,"SELECT category_name FROM categories WHERE category_id=$category_id");
$category_row = mysqli_fetch_assoc($catRes);
$category_name = $category_row['category_name'];

// Fetch subcategories from DB

$subRes = mysqli_query($conn,"SELECT * FROM subcategories WHERE category_id=$category_id");
$subcategories_list = [];
while($row = mysqli_fetch_assoc($subRes)){
   $subcategories_list[] = [
    'id' => $row['subcategory_id'],
    'name' => $row['subcategory_name']
];
 // store full row
}

// Handle form submission
if($_SERVER['REQUEST_METHOD']=='POST'){
    $subcategory_id = (int)$_POST['subcategory_id'];
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $desc = mysqli_real_escape_string($conn,$_POST['description']);
    $qty = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $unit = mysqli_real_escape_string($conn,$_POST['unit']);

    if(!isset($_FILES['image']) || $_FILES['image']['error']!=0){
        die("Image required!");
    }

    $imgName = time().'_'.basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$imgName");

    mysqli_query($conn,"
        INSERT INTO products
        (farmer_id, category_id, subcategory_id, name, description, quantity, price, unit, image)
        VALUES
        ({$_SESSION['user_id']}, $category_id, $subcategory_id, '$name', '$desc', $qty, $price, '$unit', '$imgName')
    ") or die(mysqli_error($conn));

    header("Location: manage_products.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add Product | <?= htmlspecialchars($category_name) ?></title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: url('vegetable.webp') center/cover no-repeat fixed;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
}
body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: -1;
}

.card {
    background: rgba(255,255,255,0.15);
    padding: 40px 30px;
    border-radius: 20px;
    backdrop-filter: blur(15px);
    color: white;
    max-width: 500px;
    width: 100%;
    text-align: center;
}

h2 {
    margin-bottom: 30px;
    font-size: 24px;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
}

textarea {
    height: 80px;
}

button {
    width: 100%;
    padding: 14px;
    background: #22c55e;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s ease;
}

button:hover {
    background: #15803d;
}

.back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 18px;
    background: #14532d;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: background 0.3s ease;
}

.back-btn:hover {
    background: #0f3d1f;
}
</style>
</head>
<body>

<div class="card">
<h2>🌱 Add Product (<?= htmlspecialchars($category_name) ?>)</h2>

<form method="post" enctype="multipart/form-data">

<select name="subcategory" id="subcategory" required>
    <option value="">--Select Subcategory--</option>
</select>

<input type="text" name="name" placeholder="Product Name" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="number" name="quantity" placeholder="Quantity" required>
<input type="number" step="0.01" name="price" placeholder="Price" required>
<input type="text" name="unit" placeholder="Unit (kg/dozen)" required>
<input type="file" name="image" required>

<button type="submit">Add Product</button>
</form>

<a href="select_category.php" class="back-btn">← Back to Categories</a>
</div>

<script>
// Populate subcategories dynamically
const subcategories = <?php echo json_encode($subcategories_list); ?>;
const subSelect = document.getElementById("subcategory");

subcategories.forEach(sc => {
    let opt = document.createElement("option");
    opt.value = sc.id;          // IMPORTANT
    opt.textContent = sc.name; // Display name
    subSelect.appendChild(opt);
});

</script>

</body>
</html>
