<?php
session_start();
require_once "../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../auth/login.php");
    exit;
}

$farmer_id = (int)$_SESSION['user_id'];

/* ================= FETCH REVIEWS ================= */
$stmt = $conn->prepare("
SELECT
  r.rating,
  r.review,
  r.created_at,

  p.name AS product_name,
  p.image,

  u.name AS consumer_name,

  r.order_id

FROM reviews r
JOIN products p ON p.id = r.product_id
JOIN users u ON u.id = r.consumer_id

WHERE p.farmer_id = ?
ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Customer Reviews | AgriChain</title>

<style>
body{
  font-family:Segoe UI, sans-serif;
  background:#f5f7fa;
  margin:0;
}
.wrap{
  max-width:1000px;
  margin:30px auto;
}
.card{
  background:#fff;
  border-radius:16px;
  padding:20px;
  margin-bottom:22px;
  box-shadow:0 10px 25px rgba(0,0,0,.12);
  display:flex;
  gap:18px;
}
.card img{
  width:120px;
  height:120px;
  border-radius:12px;
  object-fit:cover;
}
.content{
  flex:1;
}
.stars{
  color:#fbc02d;
  font-size:18px;
}
.small{
  font-size:14px;
  color:#555;
}
.review-box{
  background:#f9f9f9;
  padding:12px;
  border-radius:10px;
  margin-top:10px;
}
.badge{
  display:inline-block;
  padding:4px 10px;
  border-radius:12px;
  font-size:12px;
  background:#e3f2fd;
  color:#1565c0;
}
.empty{
  background:#fff;
  padding:40px;
  border-radius:16px;
  text-align:center;
  color:#777;
}
</style>
</head>

<body>

<div class="wrap">
<h2>⭐ Customer Reviews</h2>

<?php if ($reviews->num_rows === 0): ?>
  <div class="empty">
    No reviews yet. Reviews will appear once customers rate delivered orders.
  </div>
<?php endif; ?>

<?php while($r = $reviews->fetch_assoc()): ?>

<div class="card">

  <img src="../uploads/products/<?= $r['image'] ?: 'no-image.png' ?>">

  <div class="content">

    <b><?= htmlspecialchars($r['product_name']) ?></b><br>

    <span class="small">
      Reviewed by <?= htmlspecialchars($r['consumer_name']) ?>
      • Order #<?= $r['order_id'] ?>
    </span>

    <div class="stars">
      <?php
        for($i=1;$i<=5;$i++){
          echo $i <= $r['rating'] ? "★" : "☆";
        }
      ?>
    </div>

    <?php if (!empty($r['review'])): ?>
      <div class="review-box">
        <?= nl2br(htmlspecialchars($r['review'])) ?>
      </div>
    <?php else: ?>
      <div class="review-box small">
        No written review provided.
      </div>
    <?php endif; ?>

    <div class="small" style="margin-top:8px">
      <?= date("d M Y, h:i A", strtotime($r['created_at'])) ?>
    </div>

  </div>

</div>

<?php endwhile; ?>

</div>
</body>
</html>
