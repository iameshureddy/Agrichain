<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/telegram.php";

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized");
}

$farmer_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$order_id = (int)($_POST['order_id'] ?? 0);
$status   = $_POST['status'] ?? '';

$allowed = ['PLACED','PACKED','SHIPPED','DELIVERED'];
if (!$order_id || !in_array($status, $allowed)) {
    die("Invalid status");
}

/* UPDATE STATUS */
$stmt = $conn->prepare("UPDATE consumer_orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

/* FETCH CONSUMER TELEGRAM */
$q = $conn->prepare("
    SELECT u.telegram_chat_id
    FROM consumer_orders co
    JOIN users u ON u.id = co.consumer_id
    WHERE co.id = ?
");
$q->bind_param("i", $order_id);
$q->execute();
$consumer = $q->get_result()->fetch_assoc();

/* SEND TELEGRAM */
if (!empty($consumer['telegram_chat_id'])) {
    sendTelegram(
        $consumer['telegram_chat_id'],
        "📦 <b>Order Update</b>\n\n".
        "🆔 Order ID: <b>$order_id</b>\n".
        "🚚 Status: <b>$status</b>"
    );
}

/* POPUP MESSAGE */
$_SESSION['popup'] = "✅ Order status updated to $status";

/* REDIRECT BACK */
header("Location: orders.php");
exit;
