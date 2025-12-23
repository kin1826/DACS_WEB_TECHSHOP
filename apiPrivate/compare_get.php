<?php

session_start();
echo json_encode($_SESSION['compare'] ?? [
  'category_id' => null,
  'products' => []
]);
