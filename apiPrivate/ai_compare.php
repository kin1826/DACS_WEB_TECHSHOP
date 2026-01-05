<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
  echo json_encode(['error' => 'No prompt']);
  exit;
}

$apiKey = 'OPENAI_API_KEY_CUA_BAN';

$data = [
  'model' => 'gpt-4.1-mini',
  'messages' => [
    ['role' => 'system', 'content' => 'Bạn là trợ lý so sánh sản phẩm.'],
    ['role' => 'user', 'content' => $prompt]
  ]
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
  ],
  CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

echo json_encode([
  'result' => $res['choices'][0]['message']['content'] ?? 'AI không trả kết quả'
]);
