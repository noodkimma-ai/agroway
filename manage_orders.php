<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    header("Location: login.php");
    exit;
}

$fid = $_SESSION['user_id'];

/* update status */
if (isset($_GET['oid'], $_GET['status'])) {
    $oid = (int)$_GET['oid'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    mysqli_query($conn,
        "UPDATE orders o
         JOIN order_items oi ON o.order_id = oi.order_id
         JOIN products p ON oi.product_id = p.product_id
         SET o.status='$status'
         WHERE o.order_id=$oid AND p.farmer_id=$fid");
}

/* fetch orders */
$sql = "
SELECT 
    o.order_id,
    o.status,
    o.created_at,
    u.name AS buyer,
    p.name AS product,
    oi.qty,
    oi.price
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
JOIN users u ON o.buyer_id = u.user_id
WHERE p.farmer_id = $fid
ORDER BY o.created_at DESC
";

$res = mysqli_query($conn, $sql);
?>

<h2>📦 Order Requests</h2>

<?php if(mysqli_num_rows($res)==0): ?>
<p>No orders yet.</p>
<?php endif; ?>

<?php while($r = mysqli_fetch_assoc($res)): ?>
<div style="border:1px solid #ccc;padding:12px;margin:12px;border-radius:8px">

<b>Order #<?= $r['order_id'] ?></b><br>
Buyer: <?= htmlspecialchars($r['buyer']) ?><br>
Product: <?= htmlspecialchars($r['product']) ?><br>
Qty: <?= $r['qty'] ?><br>
Price: Rs. <?= $r['price'] ?><br>
Status: <b><?= $r['status'] ?></b><br><br>

<a href="?oid=<?= $r['order_id'] ?>&status=Accepted">✅ Accept</a> |
<a href="?oid=<?= $r['order_id'] ?>&status=Rejected">❌ Reject</a> |
<a href="?oid=<?= $r['order_id'] ?>&status=Shipped">🚚 Shipped</a> |
<a href="?oid=<?= $r['order_id'] ?>&status=Delivered">📦 Delivered</a>

</div>
<?php endwhile; ?>
