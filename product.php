<?php
session_start();
require 'config.php';

$id = (int)($_GET['id'] ?? 0);

// Fetch product + farmer info + farm info
$res = mysqli_query($conn, "
    SELECT p.*, u.name AS farmer, f.farm_name, f.farm_location
    FROM products p
    JOIN users u ON p.farmer_id=u.user_id
    LEFT JOIN farms f ON p.farm_id=f.farm_id
    WHERE p.product_id=$id
");
if(!$row = mysqli_fetch_assoc($res)) { echo 'Product not found'; exit; }

// Fetch recommended products from same farmer
$recRes = mysqli_query($conn, "
    SELECT * FROM products 
    WHERE farmer_id={$row['farmer_id']} AND product_id<>$id 
    ORDER BY created_at DESC LIMIT 3
");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($row['name'])?></title>
<style>
/* BODY */
body{
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#ffffff; /* white background */
    color:#065f46;      /* dark green text */
}

/* CONTAINER */
.container{
    width:90%;
    max-width:1100px;
    margin:100px auto 60px;
    background:#dcfce7; /* soft green container */
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* BACK BUTTON */
.back-btn{
    display:inline-block;
    margin-bottom:25px;
    padding:10px 18px;
    border-radius:8px;
    background:#22c55e;
    color:white;
    text-decoration:none;
    font-weight:600;
}
.back-btn:hover{ background:#15803d; }

/* PRODUCT DETAILS */
.product-details{
    display:flex;
    gap:30px;
    flex-wrap:wrap;
    align-items:flex-start;
}
.product-details img{
    width:300px;
    height:220px;
    object-fit:cover;
    border-radius:14px;
}
.product-info{ flex:1; min-width:250px; }

.product-info h2{
    margin-top:0;
    font-size:32px;
    color:#065f46;
}
.product-info div{ margin:8px 0; }
.product-info p{ margin:15px 0; }

/* BUTTON */
.btn{
    display:inline-block;
    padding:10px 18px;
    background:#22c55e;
    color:white;
    border-radius:8px;
    border:none;
    font-weight:bold;
    cursor:pointer;
}
.btn:hover{ background:#15803d; }

/* INPUT */
.form-group input[type="number"]{
    width:80px;
    padding:8px;
    border-radius:6px;
    border:1px solid #22c55e;
}

/* STARS */
.stars label{ font-size:22px; color:#ccc; }
.stars-readonly label{ color:#facc15; }

/* RECOMMENDED */
.recommended{ margin-top:40px; }
.recommended h3{
    text-align:center;
    color:#065f46;
}

/* GRID */
.product-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}

/* CARD */
.product-card{
    background:#bbf7d0; /* light green card */
    padding:15px;
    border-radius:14px;
    transition:.3s;
    color:#065f46;
}
.product-card:hover{
    transform:scale(1.03);
    background:#a7f3d0;
}
.product-card img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:10px;
}
.product-card .price{
    color:#065f46;
    font-weight:600;
}

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    background:#15803d; /* dark green */
    color:white;
    text-align:center;
    padding:12px 0;
}
</style>
</head>
<body>

<div class="container">
    <a href="index.php" class="back-btn">← Home</a>

    <div class="product-details">
        <img src="<?= $row['image'] && file_exists("uploads/".$row['image']) ? "uploads/".htmlspecialchars($row['image']) : 'assets/no-image.png' ?>">

        <div class="product-info">
            <h2><?=htmlspecialchars($row['name'])?></h2>
            <div>Price: Rs. <?=htmlspecialchars($row['price'])?> / <?=htmlspecialchars($row['unit'])?></div>
            <div>Farmer: <?=htmlspecialchars($row['farmer'])?></div>
            <div>Farm: <?=htmlspecialchars($row['farm_name'] ?? '-')?></div>
            <div>Farm Location: <?=htmlspecialchars($row['farm_location'] ?? '-')?></div>
            <div>Available Quantity: <?=intval($row['quantity'])?></div>
            <p><?=htmlspecialchars($row['description'])?></p>

            <!-- Rating display -->
            <div id="avgRating" class="stars stars-readonly">
                <?php
                $ratingRes = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_votes FROM ratings WHERE product_id=$id");
                $ratingRow = mysqli_fetch_assoc($ratingRes);
                $avgRating = $ratingRow['avg_rating'] ? round($ratingRow['avg_rating'],1) : 0;
                $totalVotes = $ratingRow['total_votes'];
                $fullStars = floor($avgRating);
                for($i=1;$i<=5;$i++){
                    echo $i <= $fullStars ? '<label>★</label>' : '<label>☆</label>';
                }
                ?>
                (<?= $avgRating ?> / 5 from <?= $totalVotes ?> votes)
            </div>

            <?php if(isset($_SESSION['user_id']) && intval($row['quantity'])>0): ?>
            <!-- Add to cart -->
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?=$row['product_id']?>">
                <div class="form-group">
                   <input type="number" name="qty" value="1" min="0.1" step="0.1">
                </div>
                <button class="btn" type="submit">Add to Cart</button>
            </form>

            <!-- Rating submission -->
            <div style="margin-top:15px;">
                <div>Rate this product:</div>
                <div class="stars" id="rateStars">
                    <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recommended products -->
    <?php if(mysqli_num_rows($recRes)>0): ?>
    <div class="recommended">
        <h3>We also recommend</h3>
        <div class="product-grid">
            <?php while($rec = mysqli_fetch_assoc($recRes)): ?>
            <div class="product-card">
                <img src="<?= $rec['image'] && file_exists("uploads/".$rec['image']) ? "uploads/".htmlspecialchars($rec['image']) : 'assets/no-image.png' ?>">
                <h4><?=htmlspecialchars($rec['name'])?></h4>
                <p class="price">Rs. <?=$rec['price']?> / <?=$rec['unit']?></p>
                <p class="meta">
                    Quantity: <?=intval($rec['quantity'])?><br>
                    Category: <?=htmlspecialchars($rec['category_id'] ?? '-')?>
                </p>
                <a class="btn" href="product.php?id=<?=$rec['product_id']?>">View</a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<div class="footer">
    &copy; <?=date('Y')?> AgroWay. All rights reserved.
</div>

<!-- AJAX Rating Script -->
<script>
document.querySelectorAll('#rateStars input').forEach(radio=>{
    radio.addEventListener('change', function(){
        const rating = this.value;
        const productId = <?=$row['product_id']?>;
        fetch('submit_rating.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'product_id='+productId+'&rating='+rating
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                // Update rating stars
                let html='';
                const fullStars = Math.floor(data.avg_rating);
                for(let i=1;i<=5;i++){
                    html += i<=fullStars ? '★' : '☆';
                }
                document.getElementById('avgRating').innerHTML = html + ` (${data.avg_rating} / 5 from ${data.total_votes} votes)`;
            }else{
                alert('Failed to submit rating');
            }
        });
    });
});
</script>

</body>
</html>