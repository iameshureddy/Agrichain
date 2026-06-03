<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: products.php");
  exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
  header("Location: products.php");
  exit;
}

if (isset($_POST['enable'])) {
  $stmt = $conn->prepare(
    "UPDATE products SET status='active' WHERE id=?"
  );
}
elseif (isset($_POST['disable'])) {
  $stmt = $conn->prepare(
    "UPDATE products SET status='inactive' WHERE id=?"
  );
}
else {
  header("Location: products.php");
  exit;
}

$stmt->bind_param("i", $productId);
$stmt->execute();

header("Location: products.php");
exit;
