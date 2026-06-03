<?php
header("Content-Type: application/json");

// Get user message
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = $data['message'] ?? '';

// 🔑 YOUR GEMINI API KEY (use new key later)
$apiKey = "AIzaSyBBIe1Q6n73VlbQrXTsgqU6nNN8gZty_KM";

// 🌾 SMART PROMPT
$prompt = "
You are an expert agricultural assistant for Indian farmers.

Give:
- step-by-step solutions
- low-cost practical advice
- fertilizer suggestions
- crop disease help

Support:
- Telugu
- Hindi
- English

Reply in user's language.

User: $userMessage
";

// ✅ UPDATED WORKING MODEL (IMPORTANT CHANGE)
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
$postData = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

// CURL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// ⚠️ SSL fix (for localhost only)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

// ❌ CURL ERROR
if ($response === false) {
    echo json_encode([
        "reply" => "⚠️ Network issue. Check internet or server."
    ]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

// 🔍 DEBUG (TEMP — helps fix your issue)
if (!isset($result['candidates'])) {
    echo json_encode([
        "reply" => "DEBUG: " . json_encode($result)
    ]);
    exit;
}

// ✅ SUCCESS RESPONSE
if (!empty($result['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = $result['candidates'][0]['content']['parts'][0]['text'];
} 
// 🔄 SMART FALLBACK
else {
    if (stripos($userMessage, "dying") !== false) {
        $reply = "Your plant may be dying due to overwatering, lack of nutrients, or disease. Check soil moisture, add fertilizer, and inspect for pests.";
    } elseif (stripos($userMessage, "potato") !== false) {
        $reply = "For potatoes, use NPK fertilizer (10:10:10), ensure well-drained soil, and avoid overwatering.";
    } elseif (stripos($userMessage, "fertilizer") !== false) {
        $reply = "Use NPK fertilizer along with organic compost for best results.";
    } else {
        $reply = "Please provide more details about your crop problem so I can help better.";
    }
}

// Send response
echo json_encode(["reply" => $reply]);