<?php
require 'config.php'; // make sure you have DB connection

function deleteProductWithHistory($conn, $product_id, $farmer_id) {
    // 1️⃣ Get product info
    $res = mysqli_query($conn, "SELECT p.*, f.farm_name FROM products p JOIN farms f ON p.farm_id=f.farm_id WHERE p.product_id=$product_id AND p.farmer_id=$farmer_id");
    $p = mysqli_fetch_assoc($res);
    if(!$p) return false;

    $name = mysqli_real_escape_string($conn, $p['name']);
    $farm_name = mysqli_real_escape_string($conn, $p['farm_name']);
    $price = (float)$p['price'];
    $quantity = (float)$p['quantity'];
    $unit = mysqli_real_escape_string($conn, $p['unit']);
    $season = mysqli_real_escape_string($conn, $p['season']);
    $farm_id = (int)$p['farm_id'];

    // 2️⃣ Insert into buyer_history
    mysqli_query($conn, "
        INSERT INTO buyer_history (product_id, product_name, farm_id, farm_name, farmer_id, quantity, price, unit, season)
        VALUES ($product_id, '$name', $farm_id, '$farm_name', $farmer_id, $quantity, $price, '$unit', '$season')
    ");

    // 3️⃣ Delete product
    mysqli_query($conn, "DELETE FROM products WHERE product_id=$product_id AND farmer_id=$farmer_id");
    return true;
}