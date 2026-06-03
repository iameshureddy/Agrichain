<?php
session_start();
require_once "../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["verified"=>false]);
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(["verified"=>false]);
    exit;
}

/* FETCH ORDER */
$o = $conn->query("
  SELECT blockchain_tx
  FROM consumer_orders
  WHERE id=$order_id
")->fetch_assoc();

if (!$o || !$o['blockchain_tx']) {
    echo json_encode(["verified"=>false]);
    exit;
}

/* ASK NODE TO VERIFY */
$cmd = "node ../blockchain-api/readTx.js {$o['blockchain_tx']}";
$out = shell_exec($cmd);
$res = json_decode($out, true);

if (!$res || empty($res['block'])) {
    echo json_encode(["verified"=>false]);
    exit;
}

echo json_encode([
  "verified" => true,
  "tx"       => $o['blockchain_tx'],
  "block"    => $res['block'],
  "gas"      => $res['gas'],
  "time"     => date("d M Y, h:i A", $res['timestamp'])
]);
