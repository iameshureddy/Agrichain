<?php
require_once __DIR__ . "/includes/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['message']['chat']['id'])) {
    exit;
}

$chat_id = $data['message']['chat']['id'];
$text    = trim($data['message']['text'] ?? '');

if ($text !== "/start") {
    exit;
}

/*
 Ask user to login and connect
*/
$msg = "👋 Welcome to AgriChain!

🔗 To connect Telegram with your account:
1️⃣ Login to AgriChain
2️⃣ Open Dashboard
3️⃣ Click *Connect Telegram*

You will now receive order alerts automatically 🌾";

$url = "https://api.telegram.org/bot" . TG_BOT_TOKEN . "/sendMessage";
file_get_contents($url . "?" . http_build_query([
    'chat_id' => $chat_id,
    'text' => $msg,
    'parse_mode' => 'Markdown'
]));
