<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

// Fetch user's cart
$cartRes = mysqli_query($conn, "SELECT * FROM carts WHERE user_id=$uid");
if(!$cart = mysqli_fetch_assoc($cartRes)){
    die("Cart is empty");
}

// Fetch cart items
$itemsRes = mysqli_query($conn, "SELECT ci.*, p.quantity AS available_qty, p.price 
                                 FROM cart_items ci 
                                 JOIN products p ON ci.product_id=p.product_id
                                 WHERE ci.cart_id=".$cart['cart_id']);

if(mysqli_num_rows($itemsRes) == 0){
    die("Cart is empty");
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert order
    mysqli_query($conn, "INSERT INTO orders (buyer_id, address, created_at) VALUES ($uid, '$address', NOW())");
    $orderId = mysqli_insert_id($conn);

    while($item = mysqli_fetch_assoc($itemsRes)){
        $productId = $item['product_id'];
        $qty = $item['qty'];

        // Check stock
        if($qty > $item['available_qty']){
            throw new Exception("Quantity of product ID $productId exceeds available stock.");
        }

        // Insert into order_items
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price) 
                             VALUES ($orderId, $productId, $qty, ".$item['price'].")");

        // Deduct from products
        mysqli_query($conn, "UPDATE products SET quantity = quantity - $qty WHERE product_id=$productId");
    }

    // Clear cart
    mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id=".$cart['cart_id']);

    mysqli_commit($conn);

    header("Location: cart.php?success=1");
    exit;

} catch(Exception $e){
    mysqli_rollback($conn);
    die("Failed to place order: ".$e->getMessage());
}