<?php
require_once "config.php";     // nếu cần DB
require_once "class/product.php";

$categoryIds = $_POST['category'] ?? [];

$sql = "SELECT * FROM products WHERE 1";

if (!empty($categoryIds)) {
  $in = implode(",", array_map("intval", $categoryIds));
  $sql .= " AND category_id IN ($in)";
}

$products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
  echo "<div class='product-item'>
            <h3>{$p['name']}</h3>
            <p>{$p['price']}₫</p>
          </div>";
}
?>
