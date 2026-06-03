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
$payment_id  = $_GET['payment_id'] ?? null;

if (!$payment_id) {
    die("Invalid payment");
}

/* ================= CREATE ORDER ================= */
$stmt = $conn->prepare("
    INSERT INTO consumer_orders
    (consumer_id, payment_method, payment_ref, status)
    VALUES (?, 'RAZORPAY', ?, 'PLACED')
");
$stmt->bind_param("is", $consumer_id, $payment_id);
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
    $farmer_id = $c['farmer_id']; // single farmer per order (current design)

    $conn->query("
        INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES ($order_id, {$c['product_id']}, {$c['quantity']}, {$c['price_inr']})
    ");
}

/* ================= CLEAR CART ================= */
$conn->query("DELETE FROM cart WHERE consumer_id = $consumer_id");

/* ================= FETCH TELEGRAM CHAT IDs ================= */

/* Consumer */
$res = $conn->query("
    SELECT name, telegram_chat_id
    FROM users
    WHERE id = $consumer_id
");
$consumer = $res->fetch_assoc();
$consumerChatId = $consumer['telegram_chat_id'];

/* Farmer */
$res = $conn->query("
    SELECT name, telegram_chat_id
    FROM users
    WHERE id = $farmer_id
");
$farmer = $res->fetch_assoc();
$farmerChatId = $farmer['telegram_chat_id'];

/* ================= TELEGRAM ALERTS ================= */

/* Consumer Message */
sendTelegram(
    $consumerChatId,
    "🛒 <b>Order Placed Successfully</b>\n\n".
    "🆔 Order ID: <b>$order_id</b>\n".
    "💰 Amount: ₹".number_format($total,2)."\n".
    "📦 Status: PLACED\n\n".
    "Thank you for shopping with AgriChain 🌾"
);

/* Farmer Message */
sendTelegram(
    $farmerChatId,
    "🚜 <b>New Order Received</b>\n\n".
    "🆔 Order ID: <b>$order_id</b>\n".
    "💰 Order Value: ₹".number_format($total,2)."\n".
    "📦 Status: PLACED\n\n".
    "Please prepare the order 🌱"
);

/* ================= BLOCKCHAIN ================= */

/* Fingerprint */
$fingerprint = $order_id . "|" . $consumer_id . "|" . $total;

/* Send to Ganache */
$cmd = "node ../blockchain-api/storeOrder.js $order_id \"$fingerprint\"";
$output = shell_exec($cmd);
$res = json_decode($output, true);

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
$_SESSION['popup'] = "🎉 Order placed successfully!";

header("Location: orders.php?new_order=$order_id");
exit;
