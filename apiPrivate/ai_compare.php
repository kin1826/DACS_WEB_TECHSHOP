<?php
session_start();
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['force'])) {
  unset($_SESSION['ai_compare_result']);
}

if (isset($_SESSION['ai_compare_result'])) {
  echo json_encode([
    'cached' => true,
    'result' => $_SESSION['ai_compare_result']
  ]);
  exit;
}

// ================== INPUT ==================
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
  echo json_encode(['error' => 'No prompt']);
  exit;
}

// ================== API KEY ==================
$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
  echo json_encode(['error' => 'Missing OPENAI_API_KEY']);
  exit;
}

// ================== PAYLOAD ==================
$data = [
  'model' => 'gpt-4.1-mini',
  'input' => [
    [
      'role' => 'system',
      'content' => [
        [
          'type' => 'input_text',
          'text' => 'Bạn là trợ lý so sánh sản phẩm.'
        ]
      ]
    ],
    [
      'role' => 'user',
      'content' => [
        [
          'type' => 'input_text',
          'text' => $prompt
        ]
      ]
    ]
  ]
];


// ================== CURL ==================
$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
  ],
  CURLOPT_POSTFIELDS => json_encode($data),
//  CURLOPT_TIMEOUT => 30,

  CURLOPT_SSL_VERIFYPEER => false, // 👈 ADD
  CURLOPT_SSL_VERIFYHOST => false  // 👈 ADD
]);

$response = curl_exec($ch);

if ($response === false) {
  echo json_encode(['error' => curl_error($ch)]);
  exit;
}

curl_close($ch);

$res = json_decode($response, true);

// ================== ERROR FROM OPENAI ==================
if (isset($res['error'])) {
  echo json_encode([
    'error' => $res['error']['message'],
    'raw' => $res
  ]);
  exit;
}

// ================== GET TEXT ==================
$text = '';

foreach ($res['output'] as $item) {
  if ($item['type'] === 'message') {
    foreach ($item['content'] as $c) {
      if ($c['type'] === 'output_text') {
        $text .= $c['text'];
      }
    }
  }
}

$_SESSION['ai_compare_result'] = $text;

echo json_encode([
  'success' => true,
  'result' => $text,
  'raw' => $res
]);
