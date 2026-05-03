<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: buy_products.php");
    exit;
}

if(!isset($_POST['product_id']) || !isset($_POST['qty'])){
    die("Invalid Request");
}

$uid = (int)$_SESSION['user_id'];
$pid = (int)$_POST['product_id'];
$qty = (int)$_POST['qty'];

if($qty <= 0){
    die("Invalid quantity");
}

$productRes = mysqli_query($conn,"
    SELECT product_id, farmer_id, price, quantity
    FROM products
    WHERE product_id = $pid
");

if(!$productRes || mysqli_num_rows($productRes) == 0){
    die("Product not found");
}

$product = mysqli_fetch_assoc($productRes);

if($product['farmer_id'] == $uid){
    die("You cannot buy your own product");
}

if($qty > $product['quantity']){
    die("Not enough stock available");
}

$price = $product['price'];

$cartRes = mysqli_query($conn,"SELECT * FROM carts WHERE user_id=$uid");

if($cart = mysqli_fetch_assoc($cartRes)){
    $cart_id = $cart['cart_id'];
} else {
    mysqli_query($conn,"INSERT INTO carts (user_id) VALUES ($uid)");
    $cart_id = mysqli_insert_id($conn);
}

/* Check if product already in cart */
$check = mysqli_query($conn,"
    SELECT * FROM cart_items 
    WHERE cart_id=$cart_id AND product_id=$pid
");

if(mysqli_num_rows($check) > 0){
    mysqli_query($conn,"
        UPDATE cart_items 
        SET qty = qty + $qty
        WHERE cart_id=$cart_id AND product_id=$pid
    ");
} else {
    mysqli_query($conn,"
        INSERT INTO cart_items (cart_id, product_id, qty, price)
        VALUES ($cart_id, $pid, $qty, $price)
    ");
}

header("Location: cart_farmer.php");
exit;
?>
