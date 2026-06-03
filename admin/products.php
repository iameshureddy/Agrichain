<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

/* Fetch all products with farmer info */
$sql = "
SELECT 
  p.id,
  p.name,
  p.price_inr,
  p.status,
  fp.farm_name
FROM products p
LEFT JOIN farm_profiles fp ON fp.farmer_id = p.farmer_id
ORDER BY p.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Moderation | AgriChain</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-body">

<div class="sidebar">
  <h2>AgriChain</h2>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a href="farmers.php">🚜 Farmers</a></li>
    <li><a class="active" href="products.php">📦 Products</a></li>
    <li><a href="orders.php">🛒 Orders</a></li>
    <li class="logout"><a href="logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">

  <div class="topbar">
    <h3>Product Moderation</h3>
    <span>Admin Panel</span>
  </div>

  <div class="content">
    <h1>Products</h1>

    <?php if (!$result || $result->num_rows === 0): ?>
      <p>No products found</p>
    <?php else: ?>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Farm</th>
          <th>Price (₹)</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['farm_name'] ?? '—') ?></td>
          <td><?= number_format($row['price_inr'], 2) ?></td>
          <td>
            <?= $row['status'] === 'active'
              ? '<span style="color:green;font-weight:bold">Active</span>'
              : '<span style="color:red;font-weight:bold">Inactive</span>' ?>
          </td>
          <td>
            <form method="POST" action="product_action.php">
              <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
              <?php if ($row['status'] === 'active'): ?>
                <button name="disable" class="btn-reject">Disable</button>
              <?php else: ?>
                <button name="enable" class="btn-approve">Enable</button>
              <?php endif; ?>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>

      </tbody>
    </table>

    <?php endif; ?>
  </div>

</div>

</body>
</html>
