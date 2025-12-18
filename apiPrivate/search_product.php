<?php
require_once '../class/product.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if ($q === '') {
  echo json_encode([]);
  exit;
}

$productModel = new Product();
$results = $productModel->getBySearch($q);

$data = [];

foreach ($results as $r) {
  $item = [
    'id'    => $r['id'],
    'title' => $r['title'],
    'type'  => $r['type'],
    'image' => $r['image']
      ? 'img/adminUP/products/' . $r['image']
      : null,
    'price' => $r['sale_price']
  ];

  // URL theo loại
  if ($r['type'] === 'product') {
    $item['url'] = 'product_detail.php?id=' . $r['id'];
  } elseif ($r['type'] === 'brand') {
    $item['url'] = 'products.php?brand=' . $r['id'];
  } else {
    $item['url'] = 'products.php?category=' . $r['id'];
  }

  $data[] = $item;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
