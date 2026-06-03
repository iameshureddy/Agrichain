<?php
session_start();
require_once "../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

/* ================= VALIDATE PRODUCT ID ================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product");
}

$product_id = (int)$_GET['id'];

/* ================= FETCH PRODUCT (ONLY FARMER DATA) ================= */
$sql = "
SELECT
  p.id,
  p.name,
  p.price_inr,
  p.unit,
  p.quantity,
  p.image,
  p.category,
  p.description,
  p.status,
  p.available_from,
  p.expiry_date,

  f.farm_name,
  f.owner_name,
  f.phone,
  f.village,
  f.district,
  f.state,
  f.latitude,
  f.longitude

FROM products p
JOIN farm_profiles f ON p.farmer_id = f.farmer_id
WHERE p.id = $product_id
LIMIT 1
";

$res = $conn->query($sql);

if ($res->num_rows === 0) {
    die("Product not found");
}

$p = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($p['name']) ?> | AgriChain</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../assets/css/product_detail.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="pd-header">
  <a href="dashboard.php" class="back-btn">
    ← Back to Products
  </a>
</header>

<!-- ================= MAIN ================= -->
<main class="pd-container">

<!-- LEFT : IMAGE -->
<div class="pd-image">
  <img src="../uploads/products/<?= $p['image'] ?: 'no-image.png' ?>">
</div>

<!-- RIGHT : DETAILS -->
<div class="pd-details">

  <h1><?= htmlspecialchars($p['name']) ?></h1>

  <p class="pd-category">
    Category: <?= ucfirst($p['category']) ?>
  </p>

  <div class="pd-status <?= $p['status'] ?>">
    <?= strtoupper($p['status']) ?>
  </div>

  <p class="pd-price">
    ₹<?= $p['price_inr'] ?> / <?= $p['unit'] ?>
  </p>

  <p class="pd-stock">
    Available Stock: <b><?= $p['quantity'] ?> <?= $p['unit'] ?></b>
  </p>

  <p class="pd-desc">
    <?= nl2br(htmlspecialchars($p['description'])) ?>
  </p>

  <!-- FARM INFO -->
  <div class="farm-box">
    <h3>🧑‍🌾 Farmer Details</h3>

    <p><b>Farm:</b> <?= htmlspecialchars($p['farm_name']) ?></p>
    <p><b>Owner:</b> <?= htmlspecialchars($p['owner_name']) ?></p>
    <p><b>Location:</b>
      <?= htmlspecialchars($p['village']) ?>,
      <?= htmlspecialchars($p['district']) ?>,
      <?= htmlspecialchars($p['state']) ?>
    </p>
    <p><b>Contact:</b> <?= htmlspecialchars($p['phone']) ?></p>
  </div>

  <!-- BUY ACTION -->
  <div class="buy-box">
    <div class="qty-box">
      <button onclick="decQty()">−</button>
      <span id="qty">1</span>
      <button onclick="incQty()">+</button>
    </div>

    <button
      class="buy-btn"
      <?= $p['status'] !== 'active' ? 'disabled' : '' ?>
      onclick="addToCart(<?= $p['id'] ?>)">
      <?= $p['status'] === 'active' ? 'Add to Cart' : 'Unavailable' ?>
    </button>
  </div>

</div>
</main>

<div id="popup"></div>

<script src="../assets/js/product_detail.js"></script>
</body>
</html>
