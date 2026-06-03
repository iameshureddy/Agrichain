<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

/* Fetch all farmer profile details */
$sql = "
SELECT
  farmer_id,
  owner_name,
  phone,
  address,
  village,
  district,
  state,
  pincode,
  upi_id,
  wallet_address,
  payment_enabled,
  status,
  created_at
FROM farm_profiles
ORDER BY created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Farmers | Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-body">

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
  <h2>AgriChain</h2>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a class="active" href="farmers.php">🚜 Farmers</a></li>
    <li><a href="products.php">📦 Products</a></li>
    <li><a href="orders.php">🛒 Orders</a></li>
    <li class="logout"><a href="logout.php">🚪 Logout</a></li>
  </ul>
</div>

<!-- ================= MAIN ================= -->
<div class="main-content">

  <div class="topbar">
    <h3>Farmers</h3>
    <span>Admin Panel</span>
  </div>

  <div class="content">
    <h1>Farmer Approval</h1>

    <?php if (!$result || $result->num_rows === 0): ?>
      <p>No farmers found.</p>
    <?php else: ?>

    <table class="admin-table">
      <thead>
        <tr>
          <th>Owner</th>
          <th>Phone</th>
          <th>Address</th>
          <th>UPI</th>
          <th>Wallet</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
      <?php while ($f = $result->fetch_assoc()): ?>
        <tr>

          <!-- OWNER -->
          <td><?= htmlspecialchars($f['owner_name']) ?></td>

          <!-- PHONE -->
          <td>
            <a href="tel:<?= htmlspecialchars($f['phone']) ?>">
              <?= htmlspecialchars($f['phone']) ?>
            </a>
          </td>

          <!-- ADDRESS -->
          <td>
            <?= htmlspecialchars($f['village']) ?>,
            <?= htmlspecialchars($f['district']) ?><br>
            <?= htmlspecialchars($f['state']) ?> - <?= htmlspecialchars($f['pincode']) ?>
          </td>

          <!-- UPI -->
          <td><?= $f['upi_id'] ?: '—' ?></td>

          <!-- WALLET -->
          <td class="mono">
            <?= $f['wallet_address'] ? substr($f['wallet_address'], 0, 10) . '...' : '—' ?>
          </td>

          <!-- PAYMENT -->
          <td>
            <?php if ($f['payment_enabled']): ?>
              <span class="status delivered">Enabled</span>
            <?php else: ?>
              <span class="status cancelled">Disabled</span>
            <?php endif; ?>
          </td>

          <!-- STATUS -->
          <td>
            <?php if ($f['status'] === 'approved'): ?>
              <span class="status delivered">Approved</span>
            <?php elseif ($f['status'] === 'rejected'): ?>
              <span class="status cancelled">Rejected</span>
            <?php else: ?>
              <span class="status pending">Pending</span>
            <?php endif; ?>
          </td>

          <!-- CREATED -->
          <td><?= date("d M Y", strtotime($f['created_at'])) ?></td>

          <!-- ACTION -->
          <td>
            <?php if ($f['status'] === 'pending'): ?>

              <form method="POST" action="farmer_action.php" style="display:inline">
                <input type="hidden" name="farmer_id" value="<?= $f['farmer_id'] ?>">
                <button name="approve" class="btn-approve">Approve</button>
              </form>

              <form method="POST" action="farmer_action.php" style="display:inline">
                <input type="hidden" name="farmer_id" value="<?= $f['farmer_id'] ?>">
                <button name="reject" class="btn-reject">Reject</button>
              </form>

            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
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
