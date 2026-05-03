<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch categories & subcategories
$catRes = mysqli_query($conn,"SELECT * FROM categories");
$subRes = mysqli_query($conn,"SELECT * FROM subcategories");

// Fetch soil types
$soilRes = mysqli_query($conn,"SELECT * FROM soil_types");

$success = "";

if(isset($_POST['add_product'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $subcategory_id = (int)$_POST['subcategory_id'];
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['quantity'];
    $unit = $_POST['unit'];
    $season = $_POST['season'];
    $soil_id = (int)$_POST['soil_id'];
    $desc = mysqli_real_escape_string($conn,$_POST['description']);
    $farm_id = (int)$_POST['farm_id'];
    $farm_location = mysqli_real_escape_string($conn,$_POST['farm_location']);

    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $img = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'],"uploads/".$img);
    } else {
        $img = "no-image.png";
    }

    mysqli_query($conn,"
        INSERT INTO products 
        (farmer_id,farm_id,category_id,subcategory_id,name,description,quantity,price,unit,image,season,soil_id,created_at)
        VALUES
        ($uid,$farm_id,$category_id,$subcategory_id,'$name','$desc',$qty,$price,'$unit','$img','$season',$soil_id,NOW())
    ");

    $success = "✅ Product added successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add Product - AgroWay</title>

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
    max-width:500px;
    margin:170px auto 80px; /* PUSH DOWN BELOW NAVBAR */
    background:#84B179; /* soft green */
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* FORM */
input, select, textarea{
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:8px;
    border:1px solid #22c55e;
    background:#f0fdf4;
    outline:none;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:#546B41;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#15803d;
}

/* SUCCESS MESSAGE */
.success{
    background:#16a34a;
    color:white;
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
    text-align:center;
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

<!-- HEADER -->
<div class="header">
    <h2>🌾 AgroWay</h2>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="navbar">
    <a href="index.php">🏠 Home</a>
    <a href="buyer_add_product.php" class="active">➕ Add</a>
    <a href="buyer_manage_products.php">📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php">👤 Profile</a>
     <a href="buyer_history.php">🕘 History</a>
    <a href="buyer_recommendation.php">🌱 Recommendation</a>
</div>

<!-- FORM -->
<div class="container">

<h2>Add Product</h2>

<?php if($success): ?>
<div class="success" id="msg"><?= $success ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<select name="category_id" id="category" required>
<option value="">Select Category</option>
<?php while($c=mysqli_fetch_assoc($catRes)): ?>
<option value="<?= $c['category_id'] ?>"><?= $c['category_name'] ?></option>
<?php endwhile; ?>
</select>

<select name="subcategory_id" id="subcategory" required>
<option value="">Select Subcategory</option>
</select>

<select name="farm_id" required>
<option value="">Select Farm</option>
<option value="1">Farm 1</option>
<option value="2">Farm 2</option>
<option value="3">Farm 3</option>
<option value="4">Farm 4</option>
<option value="5">Farm 5</option>
</select>
<input type="text" name="farm_location" placeholder="Farm Location " required>

<select name="season" required>
<option value="">Select Season</option>
<option>Spring</option>
<option>Summer</option>
<option>Autumn</option>
<option>Winter</option>
<option>All Season</option>
</select>

<!-- Soil selection -->
<select name="soil_id" required>
<option value="">Select Soil Type</option>
<?php while($soil=mysqli_fetch_assoc($soilRes)): ?>
<option value="<?= $soil['soil_id'] ?>"><?= $soil['soil_name'] ?></option>
<?php endwhile; ?>
</select>

<input type="text" name="name" placeholder="Product Name" required>
<input type="number" name="price" placeholder="Price" required>
<input type="number" name="quantity" placeholder="Quantity" required>

<select name="unit">
<option>kg</option>
<option>piece</option>
<option>dozen</option>
<option>quantal</option>
<option>litre</option>
</select>

<textarea name="description" placeholder="Description"></textarea>

<input type="file" name="image">

<button name="add_product">Add Product</button>

</form>
</div>



<script>
// Category → Subcategory filter
const subcategories = <?php mysqli_data_seek($subRes,0); echo json_encode(mysqli_fetch_all($subRes,MYSQLI_ASSOC)); ?>;

document.getElementById('category').addEventListener('change', function(){
    let id = this.value;
    let sub = document.getElementById('subcategory');

    sub.innerHTML = '<option>Select Subcategory</option>';

    subcategories.forEach(s=>{
        if(s.category_id == id){
            let opt = document.createElement('option');
            opt.value = s.subcategory_id;
            opt.textContent = s.subcategory_name;
            sub.appendChild(opt);
        }
    });
});

// Auto hide success message
setTimeout(()=>{
    let msg = document.getElementById('msg');
    if(msg){ msg.style.display='none'; }
},2000);
</script>
<div class="footer">
    © AgroWay <?=date('Y')?>
</div>


</body>
</html>