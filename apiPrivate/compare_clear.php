<?php
session_start();

unset($_SESSION['compare']);
unset($_SESSION['ai_compare_result']);

echo json_encode([
  'success' => true
]);
