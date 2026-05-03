<?php
session_start();
require 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];

// Handle delete request
if(isset($_GET['delete']) && intval($_GET['delete']) > 0){
    $deleteId = intval($_GET['delete']);
    // Fetch user's cart
    $cartRes = mysqli_query($conn, "SELECT * FROM carts WHERE user_id=$uid");
    if($cartRow = mysqli_fetch_assoc($cartRes)){
        mysqli_query($conn, "DELETE FROM cart_items WHERE cart_item_id=$deleteId AND cart_id=".$cartRow['cart_id']);
    }
    header("Location: cart.php");
    exit;
}

// Fetch user's cart
$cart = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM carts WHERE user_id=$uid"));
$items = [];
$total = 0;

if($cart){
    $res = mysqli_query($conn, "SELECT ci.*, p.name, p.unit, p.price FROM cart_items ci JOIN products p ON ci.product_id=p.product_id WHERE ci.cart_id=".$cart['cart_id']);
    while($it = mysqli_fetch_assoc($res)){
        $items[] = $it;
        $total += ((float)$it['price'] * (float)$it['qty']);
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Cart - AgroWay</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Roboto',sans-serif;}

/* BODY */
body{
    background:#ffffff; /* white */
    min-height:100vh;
    padding:20px;
    color:#065f46; /* dark green text */
}

/* HEADER */
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    background:#C7EABB; /* dark green */
    color:white;
    border-radius:10px;
    margin-bottom:30px;
}
header h2{font-size:2rem;
color:#427A43;}
header .nav a{
    color:#427A43;
    text-decoration:none;
    padding:8px 12px;
    border-radius:6px;
    background:#9CAB84;
    margin-left:10px;
    font-weight:bold;
    transition:0.3s;
}
header .nav a:hover{background:#CF0F0F;}

/* CONTAINER */
.container{
    max-width:900px;
    margin:auto;
    background:#dcfce7; /* soft green */
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

h3{text-align:center;margin-bottom:25px;color:#065f46;}

/* TABLE */
.table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:25px;
}
.table th, .table td{
    padding:12px 15px;
    text-align:left;
}
.table th{
    background:#22c55e; /* green */
    color:white;
}
.table tr{
    background:#bbf7d0; /* light green row */
}
.table tr:nth-child(even){
    background:#a7f3d0;
}
.table td strong{color:#065f46;}

/* DELETE BUTTON */
.delete-btn{
    background:#ef4444;
    color:white;
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
    text-decoration:none;
}
.delete-btn:hover{background:#b91c1c;}

/* TEXTAREA */
textarea{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #22c55e;
    margin-bottom:20px;
    font-size:1rem;
}

/* BUTTON */
.btn{
    display:inline-block;
    background:#22c55e;
    color:white;
    padding:12px 25px;
    font-size:1rem;
    font-weight:500;
    border:none;
    border-radius:10px;
    cursor:pointer;
}
.btn:hover{
    background:#15803d;
}

/* EMPTY CART */
.empty-cart{
    text-align:center;
    padding:50px 0;
    font-size:1.2rem;
    color:#dc2626;
}

/* SUCCESS BOX */
.success-box{
    display:flex;
    gap:15px;
    align-items:center;
    background:#22c55e;
    color:white;
    padding:18px 22px;
    border-radius:14px;
    margin-top:20px;
}
.success-box .icon{font-size:34px;}
.success-box h4{margin:0;font-size:18px;}
.success-box p{margin:4px 0 0;font-size:14px;}

/* FOOTER (optional if you want) */
.footer{
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    background:#15803d;
    color:white;
    text-align:center;
    padding:12px 0;
}

</style>
</head>
<body>

<header>
    <h2>AgroWay 🌾</h2>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">
    <h3>Your Cart</h3>

    <?php if(empty($items)): ?>
        <div class="empty-cart">Your cart is empty 😔</div>
    <?php else: ?>
        <table class="table">
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach($items as $it): ?>
            <tr>
                <td><?=htmlspecialchars($it['name'])?></td>
                <td><?= number_format((float)$it['qty'],1).' '.$it['unit'] ?></td>
                <td><?=htmlspecialchars($it['price'])?></td>
                <td><?= number_format((float)$it['price'] * (float)$it['qty'],2) ?></td>
                <td>
                    <a href="cart.php?delete=<?=$it['cart_item_id']?>" class="delete-btn" onclick="return confirm('Are you sure you want to remove this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong><?= number_format($total,2) ?></strong>td>
                <td></td>
            </tr>
        </table>

        <form method="post" action="checkout.php">
            <textarea name="address" required placeholder="Enter your delivery address..."></textarea>
            <button class="btn" type="submit">Place Order</button>
        </form>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="success-box">
    <span class="icon">✅</span>
    <div>
        <h4>Order Placed Successfully!</h4>
        <p>Have a great Day!</p>
    </div>
</div>
<?php endif; ?>

</body>
</html>