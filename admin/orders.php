<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

/* Fetch orders (FIXED QUERY) */
$sql = "
SELECT 
  o.id,
  o.total_amount,
  o.status,
  o.created_at,
  u.name AS consumer_name
FROM consumer_orders o
JOIN users u ON u.id = o.consumer_id
ORDER BY o.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Orders | Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-body">

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>AgriChain</h2>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a href="farmers.php">🚜 Farmers</a></li>
    <li><a href="products.php">📦 Products</a></li>
    <li><a class="active" href="orders.php">🛒 Orders</a></li>
    <li class="logout"><a href="logout.php">🚪 Logout</a></li>
  </ul>
</div>

<!-- MAIN -->
<div class="main-content">

  <div class="topbar">
    <h3>Orders</h3>
    <span>Admin Panel</span>
  </div>

  <div class="content">
    <h1>All Orders</h1>

    <?php if (!$result || $result->num_rows === 0): ?>
      <p>No orders found.</p>
    <?php else: ?>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Consumer</th>
          <th>Total (₹)</th>
          <th>Status</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['consumer_name']) ?></td>
          <td>₹<?= number_format($row['total_amount'], 2) ?></td>
          <td>
            <span class="status placed">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
          <td>
            <form method="POST" action="order_action.php">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <select name="status" required>
                <option value="PLACED">PLACED</option>
                <option value="CONFIRMED">CONFIRMED</option>
                <option value="SHIPPED">SHIPPED</option>
                <option value="DELIVERED">DELIVERED</option>
                <option value="CANCELLED">CANCELLED</option>
              </select>
              <button class="btn-approve">Update</button>
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
