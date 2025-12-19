<?php
session_start();

unset($_SESSION['compare']);

echo json_encode([
  'success' => true
]);
