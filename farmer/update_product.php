<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized");
}

$farmer_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$id = (int)$_POST['id'];
$name = trim($_POST['name']);
$price = (float)$_POST['price'];
$quantity = (int)$_POST['quantity'];
$unit = $_POST['unit'];
$available_from = $_POST['available_from'];
$expiry_date = $_POST['expiry_date'];
$description = trim($_POST['description']);

/* ===== AUTO STATUS ===== */
$today = date("Y-m-d");
if ($today < $available_from) {
    $status = "upcoming";
} elseif ($today > $expiry_date) {
    $status = "expired";
} else {
    $status = "active";
}

/* ===== IMAGE (OPTIONAL) ===== */
$imageSQL = "";
if (!empty($_FILES['image']['name'])) {

    $uploadDir = __DIR__ . "/../uploads/products/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $imageName = time() . "_" . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);

    $imageSQL = ", image = '$imageName'";
}

/* ===== UPDATE ===== */
$conn->query("
    UPDATE products SET
        name = '$name',
        price_inr = $price,
        quantity = $quantity,
        unit = '$unit',
        available_from = '$available_from',
        expiry_date = '$expiry_date',
        description = '$description',
        status = '$status'
        $imageSQL
    WHERE id = $id AND farmer_id = $farmer_id
");

/* ===== REDIRECT ===== */
header("Location: my_products.php");
exit;
