<?php
session_start(); require 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='farmer'){ header('Location: login.php'); exit; }
$uid = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id=c.category_id WHERE p.farmer_id=$uid");
?>
<!doctype html><html><head><meta charset="utf-8"><title>My Products</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
<h3>My Products</h3>
<a class="btn" href="add_product.php">Add New</a>
<table class="table"><tr><th>Image</th><th>Name</th><th>Qty</th><th>Price</th><th>Action</th></tr>
<?php while($r=mysqli_fetch_assoc($res)): ?>
<tr>
<td><img src="<?=$r['image_path']?:'assets/no-image.png'?>" style="width:80px;height:60px;object-fit:cover"></td>
<td><?=htmlspecialchars($r['name'])?><br><small><?=htmlspecialchars($r['category'])?></small></td>
<td><?=htmlspecialchars($r['quantity']).' '.$r['unit']?></td>
<td><?=htmlspecialchars($r['price'])?></td>
<td><a href="edit_product.php?id=<?=$r['product_id']?>">Edit</a> | <a href="delete_product.php?id=<?=$r['product_id']?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</div></body></html>