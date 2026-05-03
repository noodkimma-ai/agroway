<?php
session_start();
require 'config.php';

$p1 = $_POST['p1'];
$p2 = $_POST['p2'];
$q1 = $_POST['q1'];
$q2 = $_POST['q2'];
$address = $_POST['address'];

$r1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE product_id=$p1"));
$r2 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE product_id=$p2"));

$total = ($r1['price']*$q1)+($r2['price']*$q2);
$discount = round($total * 0.10);
$final = $total - $discount;
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Success</title>
<style>
body{font-family:Poppins;background:url('vegetable.webp');color:white;}
.box{
    max-width:500px;
    margin:100px auto;
    background:rgba(255,255,255,0.1);
    padding:20px;
    border-radius:10px;
    text-align:center;
}
</style>
</head>

<body>

<div class="box">

<h2>✅ Order Placed!</h2>

<p>Total: Rs <?= $total ?></p>
<p style="color:#facc15;">You saved Rs <?= $discount ?> 🎉</p>
<p><b>Paid: Rs <?= $final ?></b></p>

<a href="index.php">Go Home</a>

</div>

</body>
</html>