<?php
session_start();
require_once "../includes/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$order_id = (int)($data['order_id'] ?? 0);
$tx       = $data['tx_hash'] ?? null;
$block    = (int)($data['block_number'] ?? 0);

if (!$order_id || !$tx || !$block) {
    http_response_code(400);
    exit;
}

$conn->query("
  UPDATE consumer_orders
  SET blockchain_tx='$tx', block_number=$block
  WHERE id=$order_id
");
