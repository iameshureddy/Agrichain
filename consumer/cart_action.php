<?php
session_start();
require_once "../includes/db.php";

header("Content-Type: application/json; charset=UTF-8");
error_reporting(0); // prevent HTML warnings

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$consumer_id = (int) $_SESSION['user_id'];
$action      = $_POST['action'] ?? '';
$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity   = (int) ($_POST['quantity'] ?? 1);

if ($product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product"]);
    exit;
}

if ($quantity < 1) {
    $quantity = 1;
}

/* FETCH PRODUCT SAFELY */
$stmt = $conn->prepare("
    SELECT 
      quantity,
      available_from,
      expiry_date,
      CASE
        WHEN CURDATE() < available_from THEN 'upcoming'
        WHEN CURDATE() > expiry_date THEN 'expired'
        ELSE 'active'
      END AS status
    FROM products
    WHERE id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

if ($product['status'] !== 'active') {
    echo json_encode(["success" => false, "message" => "Product unavailable"]);
    exit;
}

if ($quantity > $product['quantity']) {
    $quantity = $product['quantity'];
}

/* ADD TO CART */
if ($action === "add") {

    $stmt = $conn->prepare("
        INSERT INTO cart (consumer_id, product_id, quantity)
        VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");
    $stmt->bind_param("iii", $consumer_id, $product_id, $quantity);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Added to cart"]);
    exit;
}

/* UPDATE */
if ($action === "update") {

    $stmt = $conn->prepare("
        UPDATE cart
        SET quantity = ?
        WHERE consumer_id = ? AND product_id = ?
    ");
    $stmt->bind_param("iii", $quantity, $consumer_id, $product_id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Cart updated"]);
    exit;
}

/* REMOVE */
if ($action === "remove") {

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE consumer_id = ? AND product_id = ?
    ");
    $stmt->bind_param("ii", $consumer_id, $product_id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Item removed"]);
    exit;
}

/* FALLBACK */
echo json_encode(["success" => false, "message" => "Invalid action"]);
exit;
