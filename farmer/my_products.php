<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized access");
}

$farmer_id = (int) $_SESSION['user_id'];

/* ===== FETCH PRODUCTS ===== */
$res = $conn->query(
    "SELECT * FROM products 
     WHERE farmer_id = $farmer_id 
     ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products | AgriChain</title>

    <!-- Only My Products CSS -->
    <link rel="stylesheet" href="../assets/css/my_products.css">
</head>
<body>

<div class="page-container">

    <header class="page-header">
        <h1>🌾 My Products</h1>
        <p>All products listed by you</p>
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </header>

    

      <div class="products-grid">

<?php while ($row = $res->fetch_assoc()): ?>

  <?php
    $today = date("Y-m-d");
    if ($today < $row['available_from']) {
        $status = "upcoming";
    } elseif ($today > $row['expiry_date']) {
        $status = "expired";
    } else {
        $status = "active";
    }
  ?>

  <div class="product-card">

    <span class="status <?= $status ?>">
      <?= strtoupper($status) ?>
    </span>

    <img src="../uploads/products/<?= htmlspecialchars($row['image']) ?>" alt="Product">

    <div class="card-body">

      <h3><?= htmlspecialchars($row['name']) ?></h3>

      <p class="price">
        ₹<?= number_format($row['price_inr'],2) ?> / <?= htmlspecialchars($row['unit']) ?>
      </p>

      <p class="qty">
        Available Quantity: <?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?>
      </p>

      <!-- ✅ DESCRIPTION -->
      <?php if (!empty($row['description'])): ?>
        <p class="description">
          <?= nl2br(htmlspecialchars($row['description'])) ?>
        </p>
      <?php endif; ?>

      <p class="dates">
        <strong>From:</strong> <?= $row['available_from'] ?><br>
        
      </p>

      <!-- ✅ ACTION BUTTONS -->
      <div class="actions">
        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn edit">
          ✏️ Edit
        </a>

        <a href="delete_product.php?id=<?= $row['id'] ?>"
           class="btn delete"
           onclick="return confirm('Are you sure you want to delete this product?');">
          🗑 Delete
        </a>
      </div>

    </div>
  </div>

<?php endwhile; ?>

</div>

</body>
</html>
