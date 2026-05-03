<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

$id = (int)($_GET['id'] ?? 0);

// Soft delete (NOT permanent delete)
mysqli_query($conn,"
UPDATE products 
SET is_deleted=1 
WHERE product_id=$id AND farmer_id=$uid
");

// Redirect to history
header("Location: buyer_history.php");
exit;