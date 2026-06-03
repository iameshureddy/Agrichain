<?php
require_once("../includes/db.php");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
  INSERT INTO orders
  (blockchain_order_id, product_id, buyer_id, farmer_address, amount_wei, amount_inr, tx_hash)
  VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "iiisdss",
  $data['orderId'],
  $data['productId'],
  $_SESSION['user_id'],
  $data['farmer'],
  $data['wei'],
  $data['inr'],
  $data['tx']
);

$stmt->execute();
echo "OK";
