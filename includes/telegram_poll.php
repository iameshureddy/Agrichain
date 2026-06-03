<?php
require_once __DIR__ . "/db.php";

$botToken = "8246014449:AAENQtJ7cM2z1kU9UR0RHagMrxyYCfU-PKY";
$url = "https://api.telegram.org/bot$botToken/getUpdates";

$response = json_decode(file_get_contents($url), true);

if (!$response['ok']) {
    exit("Telegram API error");
}

foreach ($response['result'] as $update) {
    if (!isset($update['message'])) continue;

    $chat_id = $update['message']['chat']['id'];
    $text    = trim($update['message']['text'] ?? '');

    if ($text !== "/start") continue;

    // Store chat_id temporarily
    $stmt = $conn->prepare("
        INSERT IGNORE INTO telegram_logs (chat_id)
        VALUES (?)
    ");
    $stmt->bind_param("i", $chat_id);
    $stmt->execute();

    // Reply to user
    $msg = "✅ Telegram connected!

Now go back to AgriChain and click:
👉 Connect Telegram";

    file_get_contents(
        "https://api.telegram.org/bot$botToken/sendMessage?" .
        http_build_query([
            'chat_id' => $chat_id,
            'text' => $msg
        ])
    );
}
