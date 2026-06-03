<?php
require_once __DIR__ . "/includes/db.php";

$botToken = "8246014449:AAENQtJ7cM2z1kU9UR0RHagMrxyYCfU-PKY";

$url = "https://api.telegram.org/bot{$botToken}/getUpdates";

$response = file_get_contents($url);
$data = json_decode($response, true);

if (!isset($data['result'])) {
    exit("No updates");
}

foreach ($data['result'] as $update) {

    if (!isset($update['message'])) continue;

    $chat_id = $update['message']['chat']['id'];
    $username = $update['message']['from']['first_name'] ?? 'User';

    // store chat_id temporarily
    $stmt = $conn->prepare("
        INSERT IGNORE INTO telegram_logs (chat_id, name)
        VALUES (?,?)
    ");
    $stmt->bind_param("is", $chat_id, $username);
    $stmt->execute();
}

echo "Telegram updates synced successfully";
