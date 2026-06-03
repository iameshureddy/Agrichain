<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: orders.php");
  exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';

$allowed = ['PLACED','CONFIRMED','SHIPPED','DELIVERED','CANCELLED'];

if ($orderId <= 0 || !in_array($status, $allowed)) {
  header("Location: orders.php");
  exit;
}

/* Update order */
$stmt = $conn->prepare(
  "UPDATE consumer_orders SET status=? WHERE id=?"
);
$stmt->bind_param("si", $status, $orderId);
$stmt->execute();

/* Track order history */
$track = $conn->prepare(
  "INSERT INTO order_tracking (order_id, status) VALUES (?, ?)"
);
$track->bind_param("is", $orderId, $status);
$track->execute();

header("Location: orders.php");
exit;
