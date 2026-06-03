<?php
require_once __DIR__ . "/includes/admin_auth.php";
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: farmers.php");
  exit;
}

$farmId = (int)($_POST['farm_id'] ?? 0);
if ($farmId <= 0) {
  header("Location: farmers.php");
  exit;
}

if (isset($_POST['approve'])) {
  $stmt = $conn->prepare(
    "UPDATE farm_profiles SET status='approved' WHERE id=?"
  );
} elseif (isset($_POST['reject'])) {
  $stmt = $conn->prepare(
    "UPDATE farm_profiles SET status='rejected' WHERE id=?"
  );
} else {
  header("Location: farmers.php");
  exit;
}

$stmt->bind_param("i", $farmId);
$stmt->execute();

header("Location: farmers.php");
exit;
