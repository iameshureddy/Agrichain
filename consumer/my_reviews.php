<?php
session_start();
require_once "../includes/db.php";

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* FETCH REVIEWS */
$stmt = $conn->prepare("
    SELECT
        pr.rating,
        pr.review,
        pr.created_at,
        p.name AS product_name,
        p.image
    FROM product_reviews pr
    JOIN products p ON p.id = pr.product_id
    WHERE pr.consumer_id = ?
    ORDER BY pr.created_at DESC
");
$stmt->bind_param("i", $consumer_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Reviews | AgriChain</title>

<style>
body{
  font-family:Segoe UI, sans-serif;
  background:#f4f6f8;
}
.container{
  max-width:900px;
  margin:30px auto;
}
.card{
  background:#fff;
  padding:20px;
  border-radius:16px;
  margin-bottom:18px;
  box-shadow:0 10px 20px rgba(0,0,0,.08);
  display:flex;
  gap:16px;
}
.card img{
  width:90px;
  height:90px;
  object-fit:cover;
  border-radius:12px;
}
.stars{
  color:#f9a825;
  font-size:18px;
}
.small{
  color:#666;
  font-size:13px;
}
.empty{
  background:#fff;
  padding:30px;
  text-align:center;
  border-radius:16px;
}
</style>
</head>

<body>

<div class="container">
  <h2>⭐ My Reviews</h2>

  <?php if ($reviews->num_rows === 0): ?>
    <div class="empty">
      You haven’t written any reviews yet.
    </div>
  <?php endif; ?>

  <?php while ($r = $reviews->fetch_assoc()): ?>
    <div class="card">
      <img src="../uploads/products/<?= htmlspecialchars($r['image']) ?>">
      <div>
        <h3><?= htmlspecialchars($r['product_name']) ?></h3>
        <div class="stars">
          <?= str_repeat("★", $r['rating']) ?>
          <?= str_repeat("☆", 5 - $r['rating']) ?>
        </div>
        <p><?= nl2br(htmlspecialchars($r['review'])) ?></p>
        <div class="small">
          Reviewed on <?= date("d M Y", strtotime($r['created_at'])) ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>

</div>
</body>
</html>
