<?php
session_start();
require_once __DIR__ . "/includes/db.php";

/*
 User must be logged in
*/
if (!isset($_SESSION['user_id'])) {
    die("❌ Please login first");
}

$user_id = (int)$_SESSION['user_id'];
$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id || !is_numeric($chat_id)) {
    die("❌ Invalid Telegram Chat ID");
}

/* Save Telegram Chat ID */
$stmt = $conn->prepare("UPDATE users SET telegram_chat_id=? WHERE id=?");
$stmt->bind_param("ii", $chat_id, $user_id);
$stmt->execute();

echo "✅ Telegram connected successfully! You will now receive AgriChain alerts.";
