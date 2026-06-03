<?php
define("TG_BOT_TOKEN", "8246014449:AAENQtJ7cM2z1kU9UR0RHagMrxyYCfU-PKY");

function sendTelegramToUser($conn, $user_id, $message)
{
    $stmt = $conn->prepare("SELECT telegram_chat_id FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (empty($res['telegram_chat_id'])) {
        return false; // user not connected
    }

    $chat_id = $res['telegram_chat_id'];

    $url = "https://api.telegram.org/bot" . TG_BOT_TOKEN . "/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    file_get_contents($url . "?" . http_build_query($data));
    return true;
}
