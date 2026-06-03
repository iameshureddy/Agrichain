<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

/* ================= BASIC COUNTS ================= */
$totalFarmers = $conn->query("SELECT COUNT(*) c FROM farm_profiles")->fetch_assoc()['c'];
$pendingFarmers = $conn->query("SELECT COUNT(*) c FROM farm_profiles WHERE status='pending'")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) c FROM consumer_orders")->fetch_assoc()['c'];

/* ================= MONTHLY ANALYTICS (LAST 12 MONTHS) ================= */
$monthQ = $conn->query("
  SELECT DATE_FORMAT(created_at,'%b %Y') m, COUNT(*) c
  FROM consumer_orders
  GROUP BY YEAR(created_at), MONTH(created_at)
  ORDER BY created_at DESC
  LIMIT 12
");

$monthLabels = [];
$monthCounts = [];
while ($m = $monthQ->fetch_assoc()) {
  $monthLabels[] = $m['m'];
  $monthCounts[] = $m['c'];
}

/* ================= ORDER STATUS (PIE) ================= */
$statusQ = $conn->query("
  SELECT status, COUNT(*) c
  FROM consumer_orders
  GROUP BY status
");

$statusLabels = [];
$statusCounts = [];
while ($s = $statusQ->fetch_assoc()) {
  $statusLabels[] = $s['status'];
  $statusCounts[] = $s['c'];
}

/* ================= BLOCKCHAIN LOGS ================= */
$blockQ = $conn->query("
  SELECT id, blockchain_tx, status, created_at
  FROM consumer_orders
  WHERE blockchain_tx IS NOT NULL AND blockchain_tx != ''
  ORDER BY created_at DESC
  LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | AgriChain</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== RESET ===== */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"Segoe UI",system-ui,sans-serif; }

/* ===== LAYOUT ===== */
.admin-body {
  display:flex;
  min-height:100vh;
  background:#f4f6f8;
}

/* ===== SIDEBAR ===== */
.sidebar {
  width:240px;
  background:linear-gradient(180deg,#1b5e20,#2e7d32);
  color:#fff;
  padding:24px 16px;
}

.sidebar h2 {
  text-align:center;
  margin-bottom:30px;
}

.sidebar a {
  display:block;
  padding:12px 16px;
  margin-bottom:10px;
  color:#fff;
  text-decoration:none;
  border-radius:10px;
}

.sidebar a:hover,
.sidebar a.active {
  background:rgba(255,255,255,0.25);
}

.sidebar .logout {
  margin-top:30px;
  background:rgba(255,255,255,0.15);
}

/* ===== MAIN ===== */
.main {
  flex:1;
  padding:30px;
}

/* ===== TOPBAR ===== */
.topbar {
  background:#fff;
  padding:18px 24px;
  margin-bottom:24px;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* ===== STATS ===== */
.stats {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
  gap:20px;
}

.stat {
  background:#fff;
  padding:22px;
  border-radius:16px;
  box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.stat h4 { color:#666; margin-bottom:8px; }
.stat p { font-size:28px; font-weight:bold; color:#1b5e20; }

/* ===== CHARTS ===== */
.charts {
  display:grid;
  grid-template-columns:2fr 1fr;
  gap:20px;
  margin-top:30px;
}

.chart-box {
  background:#fff;
  padding:24px;
  border-radius:16px;
  box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* ===== TABLE ===== */
.admin-table {
  width:100%;
  margin-top:30px;
  border-collapse:collapse;
  background:#fff;
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.admin-table th,
.admin-table td {
  padding:14px 16px;
  text-align:left;
}

.admin-table th {
  background:#f1f5f9;
}
</style>
</head>

<body class="admin-body">

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <h2>AgriChain</h2>
  <a class="active" href="dashboard.php">📊 Dashboard</a>
  <a href="farmers.php">👨‍🌾 Farmers</a>
  <a href="products.php">📦 Products</a>
  <a href="orders.php">🛒 Orders</a>
  <a class="logout" href="logout.php">🚪 Logout</a>
</div>

<!-- ===== MAIN ===== -->
<div class="main">

  <div class="topbar">
    <h3>Admin Dashboard</h3>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat"><h4>Total Farmers</h4><p><?= $totalFarmers ?></p></div>
    <div class="stat"><h4>Pending Farmers</h4><p><?= $pendingFarmers ?></p></div>
    <div class="stat"><h4>Total Products</h4><p><?= $totalProducts ?></p></div>
    <div class="stat"><h4>Total Orders</h4><p><?= $totalOrders ?></p></div>
  </div>

  <!-- CHARTS -->
  <div class="charts">

    <!-- BAR GRAPH -->
    <div class="chart-box">
      <h3>Monthly Orders (Last 12 Months)</h3>
      <canvas id="monthlyBar"></canvas>
    </div>

    <!-- PIE CHART -->
    <div class="chart-box">
      <h3>Order Status Distribution</h3>
      <canvas id="statusPie"></canvas>
    </div>

  </div>

  <!-- BLOCKCHAIN LOGS -->
  <h3 style="margin-top:30px;">Blockchain Logs</h3>
  <table class="admin-table">
    <tr>
      <th>Order ID</th>
      <th>Blockchain TX</th>
      <th>Status</th>
      <th>Date</th>
    </tr>

    <?php while($b = $blockQ->fetch_assoc()): ?>
    <tr>
      <td>#<?= $b['id'] ?></td>
      <td><?= substr($b['blockchain_tx'],0,14) ?>...</td>
      <td><?= $b['status'] ?></td>
      <td><?= date("d M Y",strtotime($b['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>

</div>

<script>
/* BAR GRAPH */
new Chart(document.getElementById("monthlyBar"), {
  type:"bar",
  data:{
    labels:<?= json_encode(array_reverse($monthLabels)) ?>,
    datasets:[{
      data:<?= json_encode(array_reverse($monthCounts)) ?>,
      backgroundColor:"#2e7d32",
      borderRadius:10,
      barThickness:40
    }]
  },
  options:{
    plugins:{ legend:{ display:false }},
    scales:{
      y:{ beginAtZero:true },
      x:{ grid:{ display:false }}
    },
    animation:{ duration:1200 }
  }
});

/* PIE CHART */
new Chart(document.getElementById("statusPie"), {
  type:"pie",
  data:{
    labels:<?= json_encode($statusLabels) ?>,
    datasets:[{
      data:<?= json_encode($statusCounts) ?>,
      backgroundColor:[
        "#ff9800","#2196f3","#673ab7","#4caf50","#f44336"
      ]
    }]
  }
});
</script>

</body>
</html>
