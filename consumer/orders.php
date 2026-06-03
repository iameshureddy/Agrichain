<?php
session_start();
require_once "../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* ================= FETCH ORDERS ================= */
$orders = $conn->query("
    SELECT *
    FROM consumer_orders
    WHERE consumer_id = $consumer_id
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Orders | AgriChain</title>

<style>
body{
  font-family:Segoe UI, sans-serif;
  background:#f5f7fa;
  margin:0;
}
.wrap{
  max-width:1100px;
  margin:30px auto;
}
.card{
  background:#fff;
  border-radius:18px;
  padding:22px;
  margin-bottom:28px;
  box-shadow:0 12px 26px rgba(0,0,0,.12);
}
.header{
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.price{
  font-size:20px;
  font-weight:bold;
}
.badge{
  padding:6px 14px;
  border-radius:14px;
  font-size:13px;
  font-weight:600;
}
.badge.PLACED{background:#fdecea;color:#c62828}
.badge.PACKED{background:#e3f2fd;color:#1565c0}
.badge.SHIPPED{background:#fff3cd;color:#856404}
.badge.DELIVERED{background:#e8f5e9;color:#2e7d32}
.badge.pending{background:#fff3cd;color:#856404}

.small{font-size:14px;color:#555}

hr{border:none;border-top:1px solid #eee;margin:18px 0}

/* TRACKING */
.track{
  display:flex;
  justify-content:space-between;
  margin:18px 0;
}
.step{
  flex:1;
  text-align:center;
  color:#aaa;
  position:relative;
  font-size:13px;
}
.step:before{
  content:'';
  width:18px;
  height:18px;
  border-radius:50%;
  background:#ccc;
  display:block;
  margin:0 auto 6px;
}
.step.active{
  color:#2e7d32;
  font-weight:600;
}
.step.active:before{
  background:#2e7d32;
}
.step:not(:last-child):after{
  content:'';
  position:absolute;
  top:8px;
  left:50%;
  width:100%;
  height:3px;
  background:#ccc;
  z-index:-1;
}
.step.active + .step:after{
  background:#2e7d32;
}

/* ITEMS */
.items{
  display:flex;
  gap:18px;
  flex-wrap:wrap;
}
.item{
  width:200px;
  background:#fafafa;
  padding:14px;
  border-radius:14px;
}
.item img{
  width:100%;
  height:120px;
  border-radius:10px;
  object-fit:cover;
}

/* BLOCKCHAIN CARD */
.bc-card{
  background:#f1f8e9;
  border-radius:14px;
  padding:16px;
}
.hash{
  font-family:monospace;
  font-size:13px;
  background:#fff;
  padding:10px;
  border-radius:10px;
  word-break:break-all;
  margin-top:8px;
}

/* REVIEW */
.review-btn{
  display:inline-block;
  margin-top:16px;
  padding:10px 20px;
  background:#ffb300;
  color:#000;
  text-decoration:none;
  border-radius:12px;
  font-weight:600;
}
</style>
</head>

<body>
<?php if (isset($_SESSION['review_success'])): ?>
<script>
  window.onload = function () {
    alert("<?= $_SESSION['review_success'] ?>");
  };
</script>
<?php unset($_SESSION['review_success']); endif; ?>

<div class="wrap">
<h2>📦 My Orders</h2>

<?php if ($orders->num_rows === 0): ?>
  <p>No orders found.</p>
<?php endif; ?>

<?php while ($o = $orders->fetch_assoc()): ?>

<?php
$steps = ['PLACED','PACKED','SHIPPED','DELIVERED'];
$currentIndex = array_search($o['status'], $steps);
?>

<div class="card">

  <!-- HEADER -->
  <div class="header">
    <div>
      <b>Order #<?= (int)$o['id'] ?></b><br>
      <span class="small">
        Payment: <?= htmlspecialchars($o['payment_method']) ?>
      </span>
    </div>
    <div style="text-align:right">
      <div class="price">₹<?= number_format((float)$o['total_amount'],2) ?></div>
      <span class="badge <?= htmlspecialchars($o['status']) ?>">
        <?= htmlspecialchars($o['status']) ?>
      </span>
    </div>
  </div>

  <hr>

  <!-- TRACKING -->
  <h4>📍 Order Tracking</h4>
  <div class="track">
    <?php foreach ($steps as $i => $s): ?>
      <div class="step <?= ($currentIndex !== false && $i <= $currentIndex) ? 'active' : '' ?>">
        <?= $s ?>
      </div>
    <?php endforeach; ?>
  </div>

  <hr>

  <!-- BLOCKCHAIN -->
  <h4>🔗 Blockchain Verification</h4>

  <div class="bc-card">
    <?php if (!empty($o['blockchain_tx'])): ?>
      <span class="badge DELIVERED">✅ Verified on Ganache</span>

      <div class="small" style="margin-top:10px">
        <b>Transaction Hash:</b><br>
        <?= htmlspecialchars($o['blockchain_tx']) ?><br><br>

        <b>Block Number:</b>
        <?= (int)$o['block_number'] ?>
      </div>

      <div class="hash">
        <?= htmlspecialchars($o['blockchain_hash']) ?>
      </div>
    <?php else: ?>
      <span class="badge pending">⏳ Not yet anchored on blockchain</span>
    <?php endif; ?>
  </div>

  <hr>

  <!-- PRODUCTS -->
  <h4>🛒 Ordered Products</h4>

  <div class="items">
  <?php
    $items = $conn->query("
      SELECT p.name, p.image, oi.quantity, oi.price
      FROM order_items oi
      JOIN products p ON p.id = oi.product_id
      WHERE oi.order_id = {$o['id']}
    ");
    while ($i = $items->fetch_assoc()):
  ?>
    <div class="item">
      <img src="../uploads/products/<?= $i['image'] ?: 'no-image.png' ?>">
      <b><?= htmlspecialchars($i['name']) ?></b><br>
      Qty: <?= (int)$i['quantity'] ?><br>
      Price: ₹<?= number_format((float)$i['price'],2) ?><br>
      <b>
        Subtotal: ₹<?= number_format($i['price'] * $i['quantity'],2) ?>
      </b>
    </div>
  <?php endwhile; ?>
  </div>

  <!-- REVIEW -->
  <?php if ($o['status'] === 'DELIVERED'): ?>
    <a class="review-btn"
       href="/agrichain/consumer/review_order.php?order_id=<?= (int)$o['id'] ?>">
       ⭐ Rate & Review Products / Farmer
    </a>
  <?php endif; ?>

</div>

<?php endwhile; ?>

<form method="POST" action="save_review.php">

  <!-- REQUIRED -->
  <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

  <?php foreach ($order_products as $p): ?>

    <div style="margin-bottom:20px">

      <!-- REQUIRED -->
      <input type="hidden" name="product_id[]" value="<?= $p['product_id'] ?>">

      <strong><?= htmlspecialchars($p['name']) ?></strong>

      <!-- RATING -->
      <select name="rating[]" required>
        <option value="">Select Rating</option>
        <option value="1">⭐ 1</option>
        <option value="2">⭐ 2</option>
        <option value="3">⭐ 3</option>
        <option value="4">⭐ 4</option>
        <option value="5">⭐ 5</option>
      </select>

      <!-- REVIEW -->
      <textarea
        name="review[]"
        placeholder="Write your review"
        rows="3"
      ></textarea>

    </div>

  <?php endforeach; ?>

  <button type="submit">Submit Review</button>
</form>

</div>
</body>
</html>
