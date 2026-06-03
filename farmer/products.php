<?php
require "../includes/auth_farmer.php";
require "../includes/db.php";

$res = $conn->query("
  SELECT p.*, u.name farmer
  FROM products p
  JOIN users u ON p.farmer_id=u.id
  WHERE p.active=1
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>All Products</title>
  <link rel="stylesheet" href="assets/css/farmer.css">
</head>
<body>

<div class="layout">
<?php include "sidebar.php"; ?>

<main class="content">
<h2>🛒 Marketplace</h2>

<div class="grid">
<?php while($p = $res->fetch_assoc()): ?>
  <div class="product-card">
    <img src="assets/uploads/<?= $p['image'] ?>">
    <h3><?= $p['name'] ?></h3>
    <p>By <?= $p['farmer'] ?></p>
    <strong>₹<?= $p['price_inr'] ?></strong>
  </div>
<?php endwhile; ?>
</div>

</main>
</div>
</body>
</html>
