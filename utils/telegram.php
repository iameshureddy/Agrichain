<?php
define('TG_BOT_TOKEN', '8246014449:AAENQtJ7cM2z1kU9UR0RHagMrxyYCfU-PKY');

function sendTelegram($chatId, $message)
{
    if (!$chatId) return;

    $url = "https://api.telegram.org/bot" . TG_BOT_TOKEN . "/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    file_get_contents($url . "?" . http_build_query($data));
}
