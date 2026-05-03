<?php
session_start();
require 'config.php';

/* Must be logged in */
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

/* Validate input */
if(!isset($_POST['product_id'], $_POST['qty'])){
    die("Invalid request");
}

$pid = (int)$_POST['product_id'];
$qty = (int)$_POST['qty'];

if($qty <= 0){
    die("Invalid quantity");
}

/* Fetch product info */
$productRes = mysqli_query($conn,"
    SELECT product_id, farmer_id, price, quantity
    FROM products
    WHERE product_id = $pid
");

if(mysqli_num_rows($productRes) == 0){
    die("Product not found");
}

$product = mysqli_fetch_assoc($productRes);

/* ❌ Prevent buying own product (VERY IMPORTANT) */
if($product['farmer_id'] == $uid){
    die("You cannot buy your own product");
}

/* ❌ Check available stock */
if($qty > $product['quantity']){
    die("Not enough stock available");
}

$price = $product['price'];

/* Ensure cart exists */
$cart_res = mysqli_query($conn,"SELECT cart_id FROM carts WHERE user_id=$uid");

if($cart = mysqli_fetch_assoc($cart_res)){
    $cart_id = $cart['cart_id'];
} else {
    mysqli_query($conn,"INSERT INTO carts (user_id) VALUES ($uid)");
    $cart_id = mysqli_insert_id($conn);
}

/* Check if product already in cart */
$exists = mysqli_query($conn,"
    SELECT cart_item_id, qty
    FROM cart_items
    WHERE cart_id=$cart_id AND product_id=$pid
");

if($row = mysqli_fetch_assoc($exists)){
    $newQty = $row['qty'] + $qty;

    if($newQty > $product['quantity']){
        die("Total quantity exceeds available stock");
    }

    mysqli_query($conn,"
        UPDATE cart_items
        SET qty = $newQty
        WHERE cart_item_id = {$row['cart_item_id']}
    ");
} else {
    mysqli_query($conn,"
        INSERT INTO cart_items (cart_id, product_id, qty, price)
        VALUES ($cart_id, $pid, $qty, $price)
    ");
}

/* Redirect to cart (same as buyer flow) */
header('Location: cart.php');
exit;
?>
