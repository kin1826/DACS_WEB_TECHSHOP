<?php
session_start();
header('Content-Type: application/json');

// ================== READ INPUT ==================
$input = json_decode(file_get_contents('php://input'), true);
$prompt = trim($input['prompt'] ?? '');

if ($prompt === '') {
  echo json_encode([
    'success' => false,
    'error' => 'No prompt provided'
  ]);
  exit;
}

// ================== API KEY ==================
$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
  echo json_encode([
    'success' => false,
    'error' => 'Missing OPENAI_API_KEY'
  ]);
  exit;
}

// ================== OPENAI PAYLOAD ==================
$data = [
  'model' => 'gpt-4.1-mini',
  'input' => $prompt
];

// ================== CURL CALL ==================
$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
  ],
  CURLOPT_POSTFIELDS => json_encode($data),
  CURLOPT_TIMEOUT => 30,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);

if ($response === false) {
  echo json_encode([
    'success' => false,
    'error' => curl_error($ch)
  ]);
  curl_close($ch);
  exit;
}

curl_close($ch);

// ================== PARSE RESPONSE ==================
$res = json_decode($response, true);

$text = '';

if (!empty($res['output'][0]['content'])) {
  foreach ($res['output'][0]['content'] as $item) {
    if ($item['type'] === 'output_text') {
      $text .= $item['text'];
    }
  }
}

$text = trim($text);

// ================== HANDLE EMPTY ==================
if ($text === '') {
  echo json_encode([
    'success' => false,
    'error' => 'AI returned empty response',
    'raw' => $res // 👈 giữ lại để debug, xóa cũng được
  ]);
  exit;
}


// ================== FINAL RESPONSE ==================
echo json_encode([
  'success' => true,
  'result' => $text
]);
