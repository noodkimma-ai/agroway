<?php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Buyer info
$buyerRes = mysqli_query($conn,"SELECT * FROM users WHERE user_id='$buyer_id'");
$buyer = mysqli_fetch_assoc($buyerRes);

// -------------------
// Sold Products (Total quantity added)
$soldRes = mysqli_query($conn,"
    SELECT 
        p.product_id,
        p.name AS product_name,
        p.quantity AS total_qty,
        p.farmer_id,
        p.season,
        p.created_at
    FROM products p
    WHERE p.farmer_id=$buyer_id
");

$soldProducts = [];
$soldLabels = [];
$soldData = [];
$seasonQty = [];
$farmQty = [];

while($row = mysqli_fetch_assoc($soldRes)){
    $soldProducts[] = $row;
    $soldLabels[] = $row['product_name'];
    $soldData[] = $row['total_qty'];

    // Season-wise
    $season = $row['season'] ?: 'Unknown';
    if(!isset($seasonQty[$season])) $seasonQty[$season] = 0;
    $seasonQty[$season] += (float)$row['total_qty'];

    // Farm-wise using farmer_id as farm number
    $farm = "Farm ".$row['farmer_id'];
    $farmQty[$farm] = ($farmQty[$farm] ?? 0) + (float)$row['total_qty'];
}

$bestSeason = array_keys($seasonQty, max($seasonQty))[0];
$bestFarm = array_keys($farmQty, max($farmQty))[0];

// Farm chart data
$farmLabels = array_keys($farmQty);
$farmData = array_values($farmQty);

// -------------------
// Purchased Products (spending)
$buyRes = mysqli_query($conn,"
    SELECT p.name AS product_name, SUM(oi.qty*oi.price) AS amount
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.buyer_id=$buyer_id
    GROUP BY p.name
");

$buyLabels = [];
$buyData = [];
while($row = mysqli_fetch_assoc($buyRes)){
    $buyLabels[] = $row['product_name'];
    $buyData[] = $row['amount'];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Buyer Profile - AgroWay</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Poppins, sans-serif;
}

/* BODY */
body{
    background:#f0fdf4; /* light green background */
    color:#065f46;       /* dark green text */
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 30px;
    background:#bce3c9; /* dark green */
    color:white;
    z-index:1000;
}
.header h2{ font-size:26px; 
color:#346739;}
.header a{
    font-size:16px;
    padding:8px 12px;
    border-radius:6px;
    background: #346739;
    text-decoration:none;
    color:white;
}
.header a:hover{ background:rgba(255,255,255,0.35); }

/* NAVBAR */
.navbar{
    position:fixed;
    top:75px;   /* ✔ correct spacing below header */
    left:0;
    width:100%;
    display:flex;
    justify-content:center;
    gap:15px;
    padding:25px;
    background:#E8F5BD;
    z-index:999;
}
.navbar a{
    color:white;
    text-decoration:none;
    padding:10px 14px;
    border-radius:8px;
    background:#A2CB8B;
}
.navbar a.active{ background:#14532d; } /* dark green */
.navbar a:hover{ background:#166534; }

/* CONTAINER */
.container{
    max-width:1200px;
    margin:150px auto 80px;
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}

/* INFO BOX */
.info-box{
    flex:1 1 100%;
    background:#dcfce7; /* soft green card */
    padding:40px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* CHART BOX */
.chart-box{
    flex:1 1 45%;
    background:#dcfce7; /* soft green card */
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* CHART TITLES */
.chart-box h3, .info-box h3{
    margin-bottom:12px;
    color:#A1BC98;
}

/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    width:100%;
    text-align:center;
    padding:12px;
    color:white;
    background:#bce3c9; /* dark green */
    z-index: 1000;
}

/* RESPONSIVE */
@media(max-width:768px){
    .chart-box{
        flex:1 1 100%;
    }
}
</style>
</head>
<body>

<div class="header">
    <h2>🌾 AgroWay</h2>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="navbar">
    <a href="index.php">🏠 Home</a>
    <a href="buyer_add_product.php">➕ Add</a>
    <a href="buyer_manage_products.php" >📦 Manage</a>
    <a href="buyer_orders.php">📋 Orders</a>
    <a href="buyer_profile.php" class="active">👤 Profile</a>
    <a href="buyer_history.php">🕘 History</a>
    <a href="buyer_recommendation.php" >🌱 Recommendation</a>
</div>

<div class="container">

    <div class="info-box">
        <h3>👤 Buyer Info</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($buyer['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($buyer['email']) ?></p>
        <p><strong>Total Quantity of Products Added:</strong> <?= array_sum(array_column($soldProducts,'total_qty')) ?></p>
        <p><strong>Total Spent on Purchased Products:</strong> Rs. <?= array_sum($buyData) ?: 0 ?></p>
    </div>

    <div class="chart-box">
        <h3>📊 Sold Products (Quantity per Product)</h3>
        <canvas id="soldPieChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>📊 Season-wise Comparison</h3>
        <canvas id="seasonChart"></canvas>
        <p style="margin-top:10px;">🌟 Suggestion: <?= $bestSeason ?> is the most productive season.</p>
    </div>

    <div class="chart-box">
        <h3>📊 Farm-wise Comparison</h3>
        <canvas id="farmChart"></canvas>
        <p style="margin-top:10px;">🌟 Suggestion: <?= $bestFarm ?> contributes most quantity.</p>
    </div>

    <div class="chart-box" style="flex:1 1 45%;">
        <h3>🛒 Purchased Products</h3>
        <canvas id="buyChart"></canvas>
    </div>

</div>

<div class="footer">© AgroWay <?= date('Y') ?></div>

<script>
const soldPieChart = new Chart(document.getElementById('soldPieChart'), {
    type:'pie',
    data:{
        labels: <?= json_encode($soldLabels) ?>,
        datasets:[{
            data: <?= json_encode($soldData) ?>,
            backgroundColor: ['#22c55e','#2563eb','#7c3aed','#16a34a','#dc2626','#ca8a04','#f97316','#eab308']
        }]
    },
    options:{
        plugins:{
            datalabels:{
                color:'white',
                formatter:(value,ctx)=>ctx.chart.data.labels[ctx.dataIndex]+': '+value,
                font:{weight:'bold',size:12}
            },
            legend:{position:'bottom', labels:{color:'white'}}
        }
    },
    plugins:[ChartDataLabels]
});

const seasonChart = new Chart(document.getElementById('seasonChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode(array_keys($seasonQty)) ?>,
        datasets:[{
            label:'Total Quantity',
            data: <?= json_encode(array_values($seasonQty)) ?>,
            backgroundColor:['#22c55e','#2563eb','#7c3aed','#16a34a']
        }]
    },
    options:{
        plugins:{ legend:{display:false} },
        scales:{ y:{ beginAtZero:true } }
    }
});

const farmChart = new Chart(document.getElementById('farmChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode($farmLabels) ?>,
        datasets:[{
            label:'Total Quantity',
            data: <?= json_encode($farmData) ?>,
            backgroundColor:'#2563eb'
        }]
    },
    options:{
        plugins:{ legend:{display:false} },
        scales:{ y:{ beginAtZero:true } }
    }
});

const buyChart = new Chart(document.getElementById('buyChart'), {
    type:'pie',
    data:{
        labels: <?= json_encode($buyLabels) ?>,
        datasets:[{
            data: <?= json_encode($buyData) ?>,
            backgroundColor:['#22c55e','#2563eb','#7c3aed','#16a34a','#dc2626','#ca8a04','#f97316','#eab308']
        }]
    },
    options:{
        plugins:{
            datalabels:{
                color:'white',
                formatter:(value,ctx)=>ctx.chart.data.labels[ctx.dataIndex]+': Rs '+value,
                font:{weight:'bold',size:12}
            },
            legend:{position:'bottom', labels:{color:'white'}}
        }
    },
    plugins:[ChartDataLabels]
});
</script>
<div class="footer">
    © AgroWay <?=date('Y')?>
</div>
</body>
</html>