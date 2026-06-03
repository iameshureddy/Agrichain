<?php
session_start();
require_once "../includes/db.php";

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../auth/login.php");
    exit;
}

$farmer_id = (int)$_SESSION['user_id'];

/* ================= TOTAL EARNINGS ================= */
$totalSql = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity * oi.price),0) AS total
    FROM consumer_orders co
    JOIN order_items oi ON oi.order_id = co.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.farmer_id = ?
      AND (
          (co.payment_method = 'COD' AND co.status = 'DELIVERED')
          OR
          (co.payment_method IN ('UPI','RAZORPAY') AND co.status = 'PLACED')
      )
");
$totalSql->bind_param("i", $farmer_id);
$totalSql->execute();
$total = $totalSql->get_result()->fetch_assoc()['total'];

/* ================= COD PENDING ================= */
$pendingSql = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity * oi.price),0) AS pending
    FROM consumer_orders co
    JOIN order_items oi ON oi.order_id = co.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.farmer_id = ?
      AND co.payment_method = 'COD'
      AND co.status != 'DELIVERED'
");
$pendingSql->bind_param("i", $farmer_id);
$pendingSql->execute();
$pending = $pendingSql->get_result()->fetch_assoc()['pending'];
?>
<!DOCTYPE html>
<html>
<head>
<title>My Earnings | AgriChain</title>
<style>
body{font-family:Segoe UI;background:#f4f6f8}
.wrap{max-width:1100px;margin:30px auto}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px}
.card{background:#fff;padding:20px;border-radius:14px;box-shadow:0 10px 20px rgba(0,0,0,.12)}
h2{margin-bottom:20px}
.amount{font-size:26px;font-weight:700}
.green{color:#2e7d32}
.orange{color:#ef6c00}
table{width:100%;border-collapse:collapse;margin-top:30px}
th,td{padding:12px;border-bottom:1px solid #ddd;text-align:left}
</style>
</head>
<body>

<div class="wrap">
<h2>💰 Earnings Overview</h2>

<div class="cards">
  <div class="card">
    <h4>Total Earnings</h4>
    <div class="amount green">₹<?= number_format($total,2) ?></div>
  </div>

  <div class="card">
    <h4>COD Pending</h4>
    <div class="amount orange">₹<?= number_format($pending,2) ?></div>
  </div>
</div>

<h2>🧾 Earnings History</h2>

<table>
<tr>
  <th>Order ID</th>
  <th>Payment</th>
  <th>Status</th>
  <th>Amount</th>
  <th>Date</th>
</tr>

<?php
$orders = $conn->prepare("
    SELECT co.id, co.payment_method, co.status,
           SUM(oi.quantity * oi.price) AS amount,
           co.created_at
    FROM consumer_orders co
    JOIN order_items oi ON oi.order_id = co.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.farmer_id = ?
    GROUP BY co.id
    ORDER BY co.id DESC
");
$orders->bind_param("i", $farmer_id);
$orders->execute();
$res = $orders->get_result();

while($o = $res->fetch_assoc()):
?>
<tr>
  <td>#<?= $o['id'] ?></td>
  <td><?= $o['payment_method'] ?></td>
  <td><?= $o['status'] ?></td>
  <td>₹<?= number_format($o['amount'],2) ?></td>
  <td><?= date("d M Y", strtotime($o['created_at'])) ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>
</body>
</html>
