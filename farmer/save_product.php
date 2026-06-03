<?php
// =====================
// FARMER DASHBOARD
// =====================

session_start();
require_once "../config/db.php";

// ---------- AUTH CHECK ----------
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized Access");
}

$farmer_id = $_SESSION['user_id'];

// ---------- DASHBOARD METRICS ----------

// Total products
$totalProducts = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM products WHERE farmer_id = $farmer_id");
if ($res) {
    $row = $res->fetch_assoc();
    $totalProducts = $row['c'];
}

// Active products
$activeProducts = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM products WHERE farmer_id = $farmer_id AND active = 1");
if ($res) {
    $row = $res->fetch_assoc();
    $activeProducts = $row['c'];
}

// Earnings (₹)
$earnings = 0.00;
$res = $conn->query(
    "SELECT IFNULL(SUM(amount_inr),0) AS total FROM earnings WHERE farmer_id = $farmer_id"
);
if ($res) {
    $row = $res->fetch_assoc();
    $earnings = $row['total'];
}


// Orders count
$ordersCount = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE farmer_id = $farmer_id");
if ($res) {
    $row = $res->fetch_assoc();
    $ordersCount = $row['c'];
}

// Blockchain transactions
$txCount = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM blockchain_records WHERE farmer_id = $farmer_id");
if ($res) {
    $row = $res->fetch_assoc();
    $txCount = $row['c'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Farmer Dashboard | AgriChain</title>
  <link rel="stylesheet" href="../assets/css/farmer.css">
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
  <h2>🌾 AgriChain</h2>

  <nav>
    <a class="active" href="dashboard.php">📊 Dashboard</a>
    <a href="add_product.php">➕ Add Product</a>
    <a href="my_products.php">📦 My Products</a>
    <a href="orders.php">🧾 Orders</a>
    <a href="earnings.php">💰 Earnings</a>
    <a href="profile.php">📍 Farm Profile</a>
    <a href="blockchain.php">🔗 Blockchain</a>
    <a href="../auth/logout.php" class="logout">🚪 Logout</a>
  </nav>
</aside>

<!-- ========== MAIN CONTENT ========== -->
<main class="main">

  <!-- HEADER -->
  <header class="header">
    <h1>Welcome Farmer 👋</h1>
    <p>Manage your products, orders & earnings</p>
  </header>

  <!-- STATS -->
  <section class="stats">

    <div class="stat-card green">
      <h4>Total Products</h4>
      <span><?= $totalProducts ?></span>
    </div>

    <div class="stat-card blue">
      <h4>Active Products</h4>
      <span><?= $activeProducts ?></span>
    </div>

    <div class="stat-card gold">
      <h4>Total Earnings</h4>
      <span>₹<?= number_format($earnings, 2) ?></span>
    </div>

    <div class="stat-card purple">
      <h4>Total Orders</h4>
      <span><?= $ordersCount ?></span>
    </div>

    <div class="stat-card dark">
      <h4>Blockchain TX</h4>
      <span><?= $txCount ?></span>
    </div>

  </section>

  <!-- QUICK ACTIONS -->
  <section class="actions">
    <a href="add_product.php" class="btn primary">➕ Add New Product</a>
    <a href="my_products.php" class="btn outline">📦 Manage Products</a>
    <a href="orders.php" class="btn outline">🧾 View Orders</a>
  </section>

  <!-- ANALYTICS -->
  <section class="analytics">
    <h2>📈 Business Analytics</h2>
    <p>
      Sales trends, product performance, demand insights and earnings charts
      will appear here.
    </p>

    <div class="chart-placeholder">
      📊 Analytics Charts (Next Step)
    </div>
  </section>

</main>

</body>
</html>
