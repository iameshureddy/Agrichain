<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$wallet = $data['wallet_address'];
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
  UPDATE users SET wallet_address=? WHERE id=?
");
$stmt->bind_param("si", $wallet, $userId);
$stmt->execute();

echo json_encode(["status" => "saved"]);
