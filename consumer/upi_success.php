<?php
session_start();
require_once "../includes/db.php";
require_once "../utils/telegram.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* ================= CREATE ORDER ================= */
$stmt = $conn->prepare("
    INSERT INTO consumer_orders
    (consumer_id, payment_method, payment_ref, status)
    VALUES (?, 'UPI', 'UPI_MANUAL', 'PLACED')
");
$stmt->bind_param("i", $consumer_id);
$stmt->execute();

$order_id = $stmt->insert_id;

/* ================= MOVE CART → ORDER ITEMS ================= */
$cart = $conn->query("
    SELECT c.product_id, c.quantity, p.price_inr, p.farmer_id
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.consumer_id = $consumer_id
");

$total = 0;
$farmer_id = null;

while ($c = $cart->fetch_assoc()) {
    $sub = $c['price_inr'] * $c['quantity'];
    $total += $sub;

    // single farmer per order (current design)
    $farmer_id = $c['farmer_id'];

    $stmtItem = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmtItem->bind_param(
        "iiid",
        $order_id,
        $c['product_id'],
        $c['quantity'],
        $c['price_inr']
    );
    $stmtItem->execute();
}

/* ================= UPDATE TOTAL ================= */
$stmt = $conn->prepare("
    UPDATE consumer_orders
    SET total_amount = ?
    WHERE id = ?
");
$stmt->bind_param("di", $total, $order_id);
$stmt->execute();

/* ================= CLEAR CART ================= */
$conn->query("DELETE FROM cart WHERE consumer_id = $consumer_id");

/* ================= FETCH TELEGRAM CHAT IDS ================= */

/* Consumer */
$consumer = $conn->query("
    SELECT name, telegram_chat_id
    FROM users
    WHERE id = $consumer_id
")->fetch_assoc();

/* Farmer */
$farmer = $conn->query("
    SELECT name, telegram_chat_id
    FROM users
    WHERE id = $farmer_id
")->fetch_assoc();

/* ================= TELEGRAM ALERTS ================= */

/* Consumer message */
if (!empty($consumer['telegram_chat_id'])) {
    sendTelegram(
        $consumer['telegram_chat_id'],
        "🛒 <b>Order Placed (UPI)</b>\n\n".
        "🆔 Order ID: <b>$order_id</b>\n".
        "💰 Amount: ₹".number_format($total,2)."\n".
        "📦 Status: PLACED\n\n".
        "Payment received via UPI ✅"
    );
}

/* Farmer message */
if (!empty($farmer['telegram_chat_id'])) {
    sendTelegram(
        $farmer['telegram_chat_id'],
        "🚜 <b>New UPI Order Received</b>\n\n".
        "🆔 Order ID: <b>$order_id</b>\n".
        "💰 Order Value: ₹".number_format($total,2)."\n".
        "📦 Payment: UPI\n\n".
        "Please prepare the order 🌱"
    );
}

/* ================= BLOCKCHAIN ================= */

/* SAME fingerprint format for all payments */
$fingerprint = $order_id . "|" . $consumer_id . "|" . $total;

$cmd = "node ../blockchain-api/storeOrder.js $order_id \"$fingerprint\"";
$res = json_decode(shell_exec($cmd), true);

/* Save blockchain data */
if (!empty($res['txHash'])) {
    $stmt = $conn->prepare("
        UPDATE consumer_orders
        SET blockchain_hash = ?, blockchain_tx = ?, block_number = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "ssii",
        $res['hash'],
        $res['txHash'],
        $res['blockNumber'],
        $order_id
    );
    $stmt->execute();
}

/* ================= POPUP + REDIRECT ================= */
$_SESSION['popup'] = "🎉 UPI Order placed successfully!";
header("Location: orders.php?new_order=$order_id");
exit;
