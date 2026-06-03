<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    die("Unauthorized");
}

$consumer_id = (int)$_SESSION['user_id'];

/* ================= VALIDATE ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (!isset($_POST['order_id'], $_POST['rating'], $_POST['review'])) {
    die("Missing data");
}

$order_id = (int)$_POST['order_id'];
$ratings  = $_POST['rating'];   // array product_id => rating
$reviews  = $_POST['review'];   // array product_id => text

/* ================= SAVE REVIEWS ================= */
$stmt = $conn->prepare("
    INSERT INTO reviews
    (order_id, product_id, consumer_id, rating, review)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($ratings as $product_id => $rating) {
    $rating = (int)$rating;
    $text   = trim($reviews[$product_id] ?? '');

    if ($rating < 1 || $rating > 5) continue;

    $stmt->bind_param(
        "iiiis",
        $order_id,
        $product_id,
        $consumer_id,
        $rating,
        $text
    );
    $stmt->execute();
}

/* ================= POPUP MESSAGE ================= */
$_SESSION['review_success'] = "⭐ Thank you! Your review has been submitted successfully.";

/* ================= REDIRECT ================= */
header("Location: orders.php");
exit;
