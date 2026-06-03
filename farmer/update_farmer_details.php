<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: /agrichain/auth/login.php");
    exit;
}

$farmerId = $_SESSION['user_id'];

$phone    = $_POST['phone'] ?? '';
$village  = $_POST['village'] ?? '';
$district = $_POST['district'] ?? '';
$state    = $_POST['state'] ?? '';
$pincode  = $_POST['pincode'] ?? '';
$upi_id   = $_POST['upi_id'] ?? '';
$upi_name = $_POST['upi_name'] ?? '';

$upi_qr = null;

/* HANDLE QR UPLOAD */
if (!empty($_FILES['upi_qr']['name'])) {
    $dir = "../uploads/upi/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $upi_qr = time() . "_" . basename($_FILES['upi_qr']['name']);
    move_uploaded_file($_FILES['upi_qr']['tmp_name'], $dir . $upi_qr);
}

/* UPDATE QUERY */
if ($upi_qr) {
    $stmt = $conn->prepare("
      UPDATE users
      SET phone=?, village=?, district=?, state=?, pincode=?,
          upi_id=?, upi_name=?, upi_qr=?
      WHERE id=?
    ");
    $stmt->bind_param(
        "ssssssssi",
        $phone, $village, $district, $state, $pincode,
        $upi_id, $upi_name, $upi_qr, $farmerId
    );
} else {
    $stmt = $conn->prepare("
      UPDATE users
      SET phone=?, village=?, district=?, state=?, pincode=?,
          upi_id=?, upi_name=?
      WHERE id=?
    ");
    $stmt->bind_param(
        "sssssssi",
        $phone, $village, $district, $state, $pincode,
        $upi_id, $upi_name, $farmerId
    );
}

$stmt->execute();
header("Location: dashboard.php");
exit;
