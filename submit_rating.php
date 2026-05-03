<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'msg'=>'Login required']); exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$user_id = $_SESSION['user_id'];

if($product_id<=0 || $rating<1 || $rating>5){
    echo json_encode(['success'=>false,'msg'=>'Invalid data']); exit;
}

// Check if user already rated
$check = mysqli_query($conn,"SELECT * FROM ratings WHERE product_id=$product_id AND user_id=$user_id");
if(mysqli_num_rows($check)>0){
    mysqli_query($conn,"UPDATE ratings SET rating=$rating WHERE product_id=$product_id AND user_id=$user_id");
}else{
    mysqli_query($conn,"INSERT INTO ratings (product_id,user_id,rating) VALUES ($product_id,$user_id,$rating)");
}

// Return updated avg rating
$ratingRes = mysqli_query($conn,"SELECT AVG(rating) as avg_rating, COUNT(*) as total_votes FROM ratings WHERE product_id=$product_id");
$ratingRow = mysqli_fetch_assoc($ratingRes);
$avgRating = round($ratingRow['avg_rating'],1);
$totalVotes = $ratingRow['total_votes'];

echo json_encode(['success'=>true,'avg_rating'=>$avgRating,'total_votes'=>$totalVotes]);
?>