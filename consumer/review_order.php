<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

/* VERIFY ORDER */
$order = $conn->query("
  SELECT id FROM consumer_orders
  WHERE id=$order_id AND consumer_id=$consumer_id AND status='DELIVERED'
");
if ($order->num_rows === 0) die("Invalid order");

/* FETCH PRODUCTS */
$items = $conn->query("
  SELECT p.id, p.name, p.image
  FROM order_items oi
  JOIN products p ON p.id=oi.product_id
  WHERE oi.order_id=$order_id
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Rate Order</title>
<style>
body{font-family:Segoe UI;background:#f5f7fa}
.wrap{max-width:700px;margin:40px auto;background:#fff;padding:30px;border-radius:16px}
.prod{display:flex;gap:15px;margin-bottom:20px}
.prod img{width:90px;height:90px;border-radius:10px;object-fit:cover}
textarea{width:100%;padding:8px;border-radius:8px}
select{padding:6px}
button{padding:12px 24px;background:#ffb300;border:none;border-radius:10px;font-weight:bold}
</style>
</head>
<body>

<div class="wrap">
<h2>⭐ Rate Your Order</h2>

<form method="post" action="save_review.php">
<input type="hidden" name="order_id" value="<?= $order_id ?>">

<?php while($p=$items->fetch_assoc()): ?>
<div class="prod">
  <img src="../uploads/products/<?= $p['image'] ?>">
  <div>
    <b><?= htmlspecialchars($p['name']) ?></b><br>
    Rating:
    <select name="rating[<?= $p['id'] ?>]">
      <option value="5">★★★★★</option>
      <option value="4">★★★★</option>
      <option value="3">★★★</option>
      <option value="2">★★</option>
      <option value="1">★</option>
    </select><br><br>
    <textarea name="review[<?= $p['id'] ?>]" placeholder="Write review..."></textarea>
  </div>
</div>
<?php endwhile; ?>

<button>Submit Review</button>
</form>
</div>

</body>
</html>
