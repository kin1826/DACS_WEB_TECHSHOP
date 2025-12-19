<?php
session_start();
require_once 'class/product.php';
require_once 'class/product_image.php';
require_once 'class/product_specification.php';

$compare = $_SESSION['compare'] ?? null;

if (
  !$compare ||
  empty($compare['products']) ||
  count($compare['products']) < 2
) {
  header('Location: products.php');
  exit;
}

$productIds = array_map('intval', $compare['products']);
$idList = implode(',', $productIds);

/**
 * Lấy thông tin sản phẩm
 * Bảng products: id, name, price, image, category_id, brand, description
 */
$sqlProducts = "
  SELECT id, name, price, image, brand, description, category_id
  FROM products
  WHERE id IN ($idList)
";
$products = $db->query($sqlProducts)->fetchAll(PDO::FETCH_ASSOC);

if (count($products) < 2) {
  header('Location: products.php');
  exit;
}

/**
 * Lấy thông số kỹ thuật (theo category)
 * product_specs: product_id, spec_key, spec_value
 */
$sqlSpecs = "
  SELECT product_id, spec_key, spec_value
  FROM product_specs
  WHERE product_id IN ($idList)
";
$specRows = $db->query($sqlSpecs)->fetchAll(PDO::FETCH_ASSOC);

/**
 * Gom spec theo product_id
 */
$specMap = [];
$allSpecKeys = [];

foreach ($specRows as $row) {
  $pid = $row['product_id'];
  $key = $row['spec_key'];
  $value = $row['spec_value'];

  $specMap[$pid][$key] = $value;
  $allSpecKeys[$key] = true;
}

$allSpecKeys = array_keys($allSpecKeys);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>So sánh sản phẩm</title>
  <link rel="stylesheet" href="css/compare-page.css">
</head>
<body>

<div class="compare-page">

  <h2>So sánh sản phẩm</h2>

  <div class="compare-actions-top">
    <a href="products.php" class="btn">← Quay lại</a>
    <button id="clearCompareBtn" class="btn danger">Xoá so sánh</button>
  </div>

  <table class="compare-table">
    <thead>
    <tr>
      <th>Tiêu chí</th>
      <?php foreach ($products as $p): ?>
        <th>
          <div class="compare-product">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="">
            <div class="name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="price"><?= number_format($p['price']) ?>₫</div>
          </div>
        </th>
      <?php endforeach; ?>
    </tr>
    </thead>

    <tbody>
    <tr>
      <td>Thương hiệu</td>
      <?php foreach ($products as $p): ?>
        <td><?= htmlspecialchars($p['brand']) ?></td>
      <?php endforeach; ?>
    </tr>

    <tr>
      <td>Mô tả</td>
      <?php foreach ($products as $p): ?>
        <td><?= nl2br(htmlspecialchars($p['description'])) ?></td>
      <?php endforeach; ?>
    </tr>

    <?php foreach ($allSpecKeys as $specKey): ?>
      <tr>
        <td><?= htmlspecialchars($specKey) ?></td>
        <?php foreach ($products as $p): ?>
          <td>
            <?= htmlspecialchars($specMap[$p['id']][$specKey] ?? '—') ?>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>

</div>

<script>
  document.getElementById('clearCompareBtn').addEventListener('click', () => {
    fetch('/apiPrivate/compare_clear.php')
      .then(() => window.location.href = 'products.php');
  });
</script>

</body>
</html>
