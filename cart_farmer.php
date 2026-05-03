<?php
session_start();
require 'config.php';

// Only logged-in farmers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer'){
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$success_msg = "";

/* Get cart */
$cartRes = mysqli_query($conn,"SELECT * FROM carts WHERE user_id=$uid");
$cart = mysqli_fetch_assoc($cartRes);
$cart_id = $cart ? $cart['cart_id'] : 0;

/* Remove item */
if(isset($_GET['remove'])){
    $cid = (int)$_GET['remove'];
    mysqli_query($conn,"DELETE FROM cart_items WHERE cart_item_id=$cid AND cart_id=$cart_id");
    header("Location: cart_farmer.php");
    exit;
}

/* Update quantity */
if(isset($_POST['update_qty'])){
    $cart_item_id = (int)$_POST['cart_item_id'];
    $new_qty = (int)$_POST['qty'];

    if($new_qty > 0){
        $itemRes = mysqli_query($conn,"SELECT product_id FROM cart_items WHERE cart_item_id=$cart_item_id AND cart_id=$cart_id");
        if($item = mysqli_fetch_assoc($itemRes)){
            $pid = $item['product_id'];
            $prodRes = mysqli_query($conn,"SELECT quantity FROM products WHERE product_id=$pid");
            $prod = mysqli_fetch_assoc($prodRes);
            if($new_qty <= $prod['quantity']){
                mysqli_query($conn,"UPDATE cart_items SET qty=$new_qty WHERE cart_item_id=$cart_item_id AND cart_id=$cart_id");
            } else {
                echo "<script>alert('Quantity exceeds available stock');</script>";
            }
        }
    }
    header("Location: cart_farmer.php");
    exit;
}

/* Place Order */
if(isset($_POST['place_order'])){
    $address = mysqli_real_escape_string($conn,$_POST['address']);

    if(empty($address)){
        echo "<script>alert('Please enter delivery address');</script>";
    } else {
        // Calculate total
        $total_amount = 0;
        $items = mysqli_query($conn,"SELECT * FROM cart_items WHERE cart_id=$cart_id");
        while($item = mysqli_fetch_assoc($items)){
            $total_amount += $item['qty'] * $item['price'];
        }

        // Insert order
        mysqli_query($conn,"INSERT INTO orders (buyer_id, address, status, total_amount, created_at)
            VALUES ($uid, '$address', 'Pending', $total_amount, NOW())");
        $order_id = mysqli_insert_id($conn);

        // Insert order items and reduce stock
        $items2 = mysqli_query($conn,"SELECT * FROM cart_items WHERE cart_id=$cart_id");
        while($item2 = mysqli_fetch_assoc($items2)){
            mysqli_query($conn,"INSERT INTO order_items (order_id, product_id, qty, price)
                VALUES ($order_id, {$item2['product_id']}, {$item2['qty']}, {$item2['price']})");
            mysqli_query($conn,"UPDATE products SET quantity = quantity - {$item2['qty']} WHERE product_id = {$item2['product_id']}");
        }

        // Clear cart
        mysqli_query($conn,"DELETE FROM cart_items WHERE cart_id=$cart_id");

        $success_msg = "✅ Your order has been successfully placed!";
    }
}

/* Fetch cart items */
$itemsRes = mysqli_query($conn," 
    SELECT ci.*, p.name, p.image, p.unit, p.price, u.name AS farmer_name, u.phone 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.product_id 
    JOIN users u ON p.farmer_id = u.user_id 
    WHERE ci.cart_id=$cart_id
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>My Cart - AgroWay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{ font-family:Poppins,sans-serif; background:url('vegetable.webp') center/cover no-repeat fixed; margin:0; color:white; }
body::before{ content:""; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:-1; }

/* HEADER */
header{position:fixed;top:0;width:97%;display:flex;justify-content:space-between;align-items:center;padding:20px 40px;color:white;backdrop-filter: blur(10px);background: rgba(255,255,255,0.08); z-index:1000;}
header .logo{ font-size:32px; font-weight:bold; }
header a{font-size:18px;padding:10px 18px;border-radius:10px;background: rgba(255,255,255,0.15);text-decoration:none;color:white; transition:0.3s;}
header a:hover{ background: rgba(255,255,255,0.3); }

/* NAVBAR */
.navbar{position:fixed; top:65px; left:0; width:100%; display:flex; justify-content:center; gap:20px; padding:30px 0; backdrop-filter: blur(10px); background: rgba(255,255,255,0.08); z-index:999;}
.navbar a{padding:8px 14px; border-radius:10px; text-decoration:none; color:white; background:rgba(255,255,255,0.15); transition:0.3s;}
.navbar a.active{ background:#22c55e; }
.navbar a:hover{ background:#15803d; }

/* CONTAINER */
.container{ max-width:1000px; margin:150px auto 80px; padding:20px; }

/* CART ITEMS */
.cart-box{ background:rgba(255,255,255,0.12); padding:15px; border-radius:12px; margin-bottom:15px; display:flex; gap:15px; align-items:center; backdrop-filter:blur(10px);}
.cart-box img{ width:100px; height:100px; object-fit:cover; border-radius:10px; }
.cart-details{ flex:1; }
.cart-details h4{ margin:0 0 5px; }
.cart-details p{ margin:2px 0; font-size:14px; }
input[type=number]{ width:60px; padding:5px; border-radius:6px; border:none; }
button{ padding:6px 12px; border:none; border-radius:6px; cursor:pointer; background:#22c55e; color:white; margin-top:5px; }
button.remove{ background:#dc2626; margin-left:5px; }
textarea{ width:100%; padding:10px; border-radius:10px; margin-top:10px; }
.success{ background:#d1fae5; padding:10px; border-radius:8px; margin-bottom:15px; color:#065f46; text-align:center; }

/* FOOTER */
.footer{ position:fixed; bottom:0; left:0; width:100%; background:rgba(0,0,0,0.6); color:white; text-align:center; padding:15px 0; font-size:14px; z-index:1000; }

/* MOBILE */
@media(max-width:768px){ .cart-box{ flex-direction:column; align-items:flex-start; } }
</style>
</head>
<body>

<header>
    <div class="logo">🌾 AgroWay</div>
    <a href="logout.php">🚪 Logout</a>
</header>

<div class="navbar">
    <a href="farmer_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="select_category.php"><i class="fa fa-plus"></i> Add</a>
    <a href="manage_products.php"><i class="fa fa-edit"></i> Manage</a>
    <a href="farmer_orders.php"><i class="fa fa-box"></i> Orders</a>
    <a href="buy_products.php" class="active"><i class="fa fa-shopping-cart"></i> Buy Products</a>
    <a href="farmer_profle.php"><i class="fa fa-user"></i> Personal Info</a>
</div>

<div class="container">

<h2>🛒 My Cart</h2>

<?php if($success_msg): ?>
<div class="success"><?= $success_msg ?></div>
<?php endif; ?>

<?php if(mysqli_num_rows($itemsRes) == 0): ?>
<p>Your cart is empty.</p>
<?php else: ?>
<?php while($item = mysqli_fetch_assoc($itemsRes)): ?>
<div class="cart-box">
<?php $image = $item['image'] && file_exists("uploads/".$item['image']) ? "uploads/".$item['image'] : "assets/no-image.png"; ?>
<img src="<?= $image ?>" alt="<?= htmlspecialchars($item['name']) ?>">
<div class="cart-details">
<h4><?= htmlspecialchars($item['name']) ?></h4>
<p>Farmer: <?= htmlspecialchars($item['farmer_name']) ?> (<?= $item['phone'] ?>)</p>
<p>Price: Rs. <?= $item['price'] ?> / <?= htmlspecialchars($item['unit']) ?></p>

<form method="post" style="display:inline;">
<input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
<input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="<?= $item['qty'] ?>" required>
<button name="update_qty">Update</button>
</form>

<a href="?remove=<?= $item['cart_item_id'] ?>">
<button class="remove">Remove</button>
</a>
</div>
</div>
<?php endwhile; ?>

<form method="post">
<h3>Delivery Address</h3>
<textarea name="address" placeholder="Enter your delivery address" required></textarea>
<br><br>
<button name="place_order">Place Order</button>
</form>

<?php endif; ?>
</div>

<div class="footer">
&copy; <?= date('Y') ?> AgroWay. All rights reserved.
</div>

</body>
</html>