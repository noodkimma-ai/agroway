<?php
require 'config.php';
if(!isset($_GET['cid'])) exit;
$cid = (int)$_GET['cid'];

$res = mysqli_query($conn,"SELECT * FROM subcategories WHERE category_id=$cid ORDER BY subcategory_name");

echo '<option value="">Select Subcategory</option>';
while($r=mysqli_fetch_assoc($res)){
    echo '<option value="'.$r['subcategory_id'].'">'.htmlspecialchars($r['subcategory_name']).'</option>';
}
