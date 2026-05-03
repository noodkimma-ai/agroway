<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch categories, subcategories, soil types
$categories = mysqli_fetch_all(mysqli_query($conn,"SELECT * FROM categories"), MYSQLI_ASSOC);
$subcategories = mysqli_fetch_all(mysqli_query($conn,"SELECT * FROM subcategories"), MYSQLI_ASSOC);
$soils = mysqli_fetch_all(mysqli_query($conn,"SELECT * FROM soil_types"), MYSQLI_ASSOC);

// Handle form submission
if($_SERVER['REQUEST_METHOD']=='POST'){
    $category_id = (int)$_POST['category_id'];
    $subcategory_id = (int)$_POST['subcategory_id'];
    $farm_id = (int)$_POST['farm_id'];
    $farm_address = mysqli_real_escape_string($conn,$_POST['farm_address']);
    $product_name = mysqli_real_escape_string($conn,$_POST['product_name']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $qty = (float)$_POST['quantity'];
    $unit = mysqli_real_escape_string($conn,$_POST['unit']);
    $price = (float)$_POST['price'];
    $season = mysqli_real_escape_string($conn,$_POST['season']);
    $soil_id = (int)$_POST['soil_type_id'];

    // Image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $imgName = time().'_'.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$imgName");
    } else {
        $imgName = null;
    }

    $query = "INSERT INTO products
        (farmer_id, farm_id, farm_address, category_id, subcategory_id, name, description, quantity, price, unit, season, soil_id, image)
        VALUES
        ($uid, $farm_id, '$farm_address', $category_id, $subcategory_id, '$product_name', '$description', $qty, $price, '$unit', '$season', $soil_id, '".($imgName??'')."')";

    if(mysqli_query($conn,$query)){
        $success = true;
    } else {
        $error = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>AgroWay - Add Product</title>
<style>
/* PAGE BACKGROUND */
body { 
    font-family: Poppins, sans-serif; 
    background: #ffffff; /* white page background */
    margin: 0; 
    color: #1f2937; /* dark text color for readability */
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
.container {
    max-width: 500px;
    margin: 150px auto 80px;
    background: #F2EDC2; /* soft green container */
    padding: 40px;
    border-radius: 15px;
    color: #1f2937;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* FORM ELEMENTS */
input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #22c55e;
    background: #f0fdf4; /* very soft green input */
    color: #1f2937;
}
input::placeholder, textarea::placeholder {
    color: #6b7280;
}
button {
    width: 100%;
    padding: 12px;
    background: #22c55e;
    border: none;
    color: white;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #16a34a;
}

/* TOAST */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #16a34a;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    display: none;
    z-index: 2000;
}

/* FOOTER */
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
</head>

<body>

<header>
    <div class="logo">🌾 AgroWay</div>
    <a href="logout.php">🚪 Logout</a>
</header>

<div class="navbar">
<a href="farmer_dashboard.php">🏠 Home</a>
<a href="add_product.php" class="active">➕ Add</a>
<a href="manage_products.php">📦 Manage</a>
<a href="farmer_orders.php" >📋 Orders</a>
<a href="buy_products.php">🛒 Buy</a>
<a href="farmer_profle.php">👤 Personal Info</a>
<a href="farmer_history.php">🕘 History</a>
<a href="farmer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">
<h2>Add Product</h2>

<form method="post" enctype="multipart/form-data">
<select name="category_id" id="category" required>
<option value="">Select Category</option>
<?php foreach($categories as $c): ?>
<option value="<?= $c['category_id'] ?>"><?= $c['category_name'] ?></option>
<?php endforeach; ?>
</select>

<select name="subcategory_id" id="subcategory" required>
<option value="">Select Subcategory</option>
</select>

<select name="soil_type_id" required>
<option value="">Select Soil Type</option>
<?php foreach($soils as $s): ?>
<option value="<?= $s['soil_id'] ?>"><?= $s['soil_name'] ?></option>
<?php endforeach; ?>
</select>

<select name="farm_id" required>
<option value="">Select Farm</option>
<option value="1">Farm 1</option>
<option value="2">Farm 2</option>
<option value="3">Farm 3</option>
<option value="4">Farm 4</option>
<option value="5">Farm 5</option>
</select>

<input type="text" name="farm_address" placeholder="Farm Address">
<input type="text" name="product_name" placeholder="Product Name">
<textarea name="description" placeholder="Description"></textarea>
<input type="number" name="quantity" placeholder="Quantity">
<select name="unit">
<option>kg</option>
<option>piece</option>
<option>dozen</option>
<option>quental</option>
</select>
<select name="season">
<option value="">Select Season</option>
<option>Spring</option>
<option>Summer</option>
<option>Autumn</option>
<option>Winter</option>
<option>All Season</option>
</select>
<input type="number" name="price" placeholder="Price">
<input type="file" name="image">
<button>Add Product</button>
</form>
</div>

<div class="toast" id="toast">✅ Product added successfully!</div>

<script>
const subcategories = <?php echo json_encode($subcategories); ?>;
const categorySelect = document.getElementById('category');
const subSelect = document.getElementById('subcategory');
const productInput = document.querySelector('input[name="product_name"]');

categorySelect.addEventListener('change', function(){
    let selected = this.value;
    subSelect.innerHTML = '<option value="">Select Subcategory</option>';
    const filtered = subcategories.filter(s => s.category_id == selected);
    filtered.forEach(s => {
        let opt = document.createElement('option');
        opt.value = s.subcategory_id;
        opt.textContent = s.subcategory_name;
        subSelect.appendChild(opt);
    });
    if(filtered.length>0) productInput.value = filtered[0].subcategory_name;
});

subSelect.addEventListener('change', function(){
    let sel = subcategories.find(s => s.subcategory_id==this.value);
    if(sel) productInput.value = sel.subcategory_name;
});

// Show toast if success
<?php if(isset($success) && $success): ?>
let toast = document.getElementById('toast');
toast.style.display = 'block';
setTimeout(()=>{ toast.style.display='none'; }, 3000);
<?php endif; ?>
</script>
<!-- ADD THIS JUST BEFORE </body> -->
<footer>
    &copy; <?= date('Y'); ?> AgroWay. All rights reserved.
</footer>

</body>
</html>