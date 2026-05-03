<?php
session_start();
require 'config.php';

$p1 = (int)($_GET['p1'] ?? 0);
$p2 = (int)($_GET['p2'] ?? 0);

$r1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE product_id=$p1"));
$r2 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE product_id=$p2"));

if(!$r1){
    die("Product 1 not found");
}
if(!$r2){
    die("Product 2 not found");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Combo Order</title>
<style>
body{font-family:Poppins;background:url('vegetable.webp') center/cover fixed;color:white;}
.container{max-width:600px;margin:100px auto;background:rgba(255,255,255,0.1);padding:20px;border-radius:10px;}
input{width:80px;padding:6px;}
.btn{padding:10px;background:#22c55e;color:white;border:none;border-radius:6px;}
</style>
</head>

<body>

<div class="container">

<h2>🔥 Combo Purchase</h2>

<form method="post" action="add_both_cart.php">

<input type="hidden" name="p1" value="<?= $p1 ?>">
<input type="hidden" name="p2" value="<?= $p2 ?>">

<p><?= $r1['name'] ?> (Rs <?= $r1['price'] ?>)</p>
Qty: <input type="number" name="q1" value="1" min="1">

<p><?= $r2['name'] ?> (Rs <?= $r2['price'] ?>)</p>
Qty: <input type="number" name="q2" value="1" min="1">

<br><br>

<button class="btn">Calculate & Order</button>

</form>

</div>

</body>
</html>