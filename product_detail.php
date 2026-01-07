<?php
session_start();

$user_id = $_SESSION['user_id'] ?? null;

require_once 'class/product.php';
require_once 'class/product_image.php';
require_once 'class/product_variant.php';
require_once 'class/product_specification.php';
require_once 'class/category.php';
require_once 'class/product_attribute.php';
require_once 'class/attribute_value.php';
require_once 'class/variant_attribute.php';
require_once 'class/cart_product.php';
require_once 'class/review.php';
require_once 'class/user.php';

$productModel = new Product();
$productImgModel = new ProductImage();
$productVariantModel = new ProductVariant();
$productSpecModel = new ProductSpecification();
$categoryModel = new Category();
$productAttributeModel = new ProductAttribute();
$attributeValueModel = new AttributeValue();
$variantAttributeModel = new VariantAttribute();
$cartModel = new Cart();
$reviewModel = new Reviews();
$userModel = new User();

// Biến lưu thông tin sản phẩm
$product = null;
$product_images = [];
$product_variants = [];
$product_specs = [];
$category = null;
$related_products = [];
$product_attributes = [];
$selectedVariant = null;
$review_list = [];
$user_review = [];
$review_data = [];

// Khai báo các biến sẽ dùng ở phần HTML
$current_price = 0;
$regular_price = 0;
$has_discount = false;
$stock_count = 0;
$stock_status = 'out-of-stock';
$stock_text = 'Hết hàng';
$discount_percent = 0;
$product_id = 0;

// Format giá VND - ĐƯA RA NGOÀI try-catch
function formatPrice($price) {
  return number_format($price, 0, ',', '.') . '₫';
}

// Xử lý ID sản phẩm
if (!empty($_GET['id'])) {
  $product_id = (int)$_GET['id'];

  try {
    // Lấy thông tin sản phẩm chính
    $product = $productModel->findById($product_id);

    if (!$product) {
      header('Location: products.php');
      exit();
    }

    // Lấy hình ảnh sản phẩm
    $product_images = $productImgModel->getByProductId($product_id);

    // Lấy biến thể sản phẩm với đầy đủ attributes
    $product_variants = $productVariantModel->getVariantsWithAttributes($product_id);

    // Lấy thông số kỹ thuật
    $product_specs = $productSpecModel->getByProductId($product_id);

    // Lấy attributes của sản phẩm với thông tin variant
    $product_attributes = $productAttributeModel->getProductAttributesWithVariantInfo($product_id);

    $review_list = $reviewModel->getAllReviewOfProduct($product_id);

    foreach ($review_list as $review) {
      $user_review = $userModel->findById($review['user_id']);

      $review_data[] = [
        'id' => $review['id'],
        'product_id' => $review['product_id'],
        'username' => $user_review['username'],
        'avatar' => $user_review['avatar'],
        'rating' => $review['rating'],
        'comment' => $review['comment'],
        'created_at' => $review['created_at']
      ];
    }

    // Lấy thông tin danh mục
    if (!empty($product['id_cate'])) {
      $category = $categoryModel->getById($product['id_cate']);
    }

    // Lấy sản phẩm cùng danh mục (sản phẩm tương tự)
    if (!empty($product['id_cate'])) {
      $related_products = $productModel->getByCategory($product['id_cate'], $product_id, 4);
    }

    // Xác định variant hiện tại
    $selectedVariant = null;
    if (!empty($_GET['variant_id'])) {
      $selectedVariant = $productVariantModel->getVariantDetails($_GET['variant_id']);
    } elseif (!empty($product_variants[0])) {
      // Lấy variant mặc định (đầu tiên)
      $selectedVariant = $product_variants[0];
    }

    // Xử lý giá và stock dựa trên variant
    if ($selectedVariant) {
      $current_price = !empty($selectedVariant['sale_price']) ? $selectedVariant['sale_price'] : $selectedVariant['price'];
      $regular_price = $selectedVariant['price'];
      $has_discount = !empty($selectedVariant['sale_price']) && $selectedVariant['sale_price'] < $selectedVariant['price'];
      $stock_count = $selectedVariant['stock_quantity'] ?? 0;
    } else {
      // Nếu không có variant, dùng giá từ sản phẩm chính
      $current_price = !empty($product['sale_price']) ? $product['sale_price'] : $product['regular_price'];
      $regular_price = $product['regular_price'];
      $has_discount = !empty($product['sale_price']) && $product['sale_price'] < $product['regular_price'];
      $stock_count = $product['stock_quantity'] ?? 0;
    }

    // Tính phần trăm giảm giá
    $discount_percent = $has_discount && $regular_price > 0 ?
      round((($regular_price - $current_price) / $regular_price) * 100) : 0;

    // Kiểm tra trạng thái tồn kho - SỬA LỖI: dùng $stock_count đã được gán ở trên
    $stock_status = 'in-stock';
    $stock_text = 'Còn hàng';

    if ($stock_count <= 0) {
      $stock_status = 'out-of-stock';
      $stock_text = 'Hết hàng';
    } elseif ($stock_count <= 10) {
      $stock_status = 'low-stock';
      $stock_text = 'Sắp hết hàng';
    }

    // Chuẩn bị dữ liệu biến thể cho JavaScript
    $variants_data = [];
    foreach ($product_variants as $variant) {
      $attributes_key = [];
      $attributes_array = [];
      if (isset($variant['attributes']) && is_array($variant['attributes'])) {
        foreach ($variant['attributes'] as $attr) {
          $attributes_key[] = $attr['attribute_id'] . ':' . $attr['value_id'];
          $attributes_array[] = [
            'attribute_id' => $attr['attribute_id'],
            'value_id' => $attr['value_id']
//            'value' => $attr['value']
          ];
        }
      }
      sort($attributes_key);

      $variants_data[] = [
        'id' => $variant['id'],
        'price' => (float)$variant['price'],
        'sale_price' => isset($variant['sale_price']) && $variant['sale_price'] ? (float)$variant['sale_price'] : null,
        'stock_quantity' => (int)($variant['stock_quantity'] ?? 0),
        'sku' => $variant['sku'] ?? '',
        'weight' => isset($variant['weight']) && $variant['weight'] ? (float)$variant['weight'] : null,
        'image_id' => $variant['image_id'] ?? null,
        'image_url' => $variant['image_url'] ?? null,
        'attributes_key' => implode('|', $attributes_key),
        'attributes' => $attributes_array,
        'is_default' => (bool)($variant['is_default'] ?? false)
      ];
    }

    // Chuẩn bị dữ liệu attributes cho JavaScript
    $attributes_data = $product_attributes; // Đã có has_variant từ phương thức getProductAttributesWithVariantInfo

    // Lưu sản phẩm đã xem vào session
    if (!isset($_SESSION['recently_viewed'])) {
      $_SESSION['recently_viewed'] = [];
    }

    // Thêm sản phẩm vào đầu mảng
    array_unshift($_SESSION['recently_viewed'], $product_id);

    // Giới hạn tối đa 10 sản phẩm
    $_SESSION['recently_viewed'] = array_slice(array_unique($_SESSION['recently_viewed']), 0, 10);

  } catch (Exception $e) {
    error_log("Error loading product: " . $e->getMessage());
    $error = "Có lỗi xảy ra khi tải thông tin sản phẩm.";
  }
} else {
  header('Location: products.php');
  exit();
}

?>

<!doctype html>
<html class="no-js" lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($product['name_pr'] ?? 'Chi tiết sản phẩm'); ?> - Shop Tech</title>
  <meta name="description" content="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo htmlspecialchars($product['name_pr'] ?? ''); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>">
  <meta property="og:image" content="<?php echo !empty($product_images[0]['image_url']) ? 'img/adminUP/products/' . $product_images[0]['image_url'] : ''; ?>">
  <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">

  <!-- Favicon -->
  <link rel="icon" href="favicon.ico" sizes="any">

  <!-- CSS -->
  <link rel="stylesheet" href="css/product_detail.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">
</head>

<?php
const PAGE_CONTEXT = 'product_detail';
define('CURRENT_PRODUCT_NAME', $product['name_pr']); // nếu cần

include 'header.php';
include 'cornerButton.php'?>

<body>

<!-- product_detail.php -->
<div class="product-detail-page">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <div class="container">
      <nav>
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="products.php">Sản phẩm</a>
        <?php if ($category): ?>
          <span>/</span>
          <a href="products.php?category=<?php echo $category['id']; ?>">
            <?php echo htmlspecialchars($category['name']); ?>
          </a>
        <?php endif; ?>
        <span>/</span>
        <a href="product_detail.php?id=<?php echo $product_id; ?>" class="active">
          <?php echo htmlspecialchars($product['name_pr']); ?>
        </a>
      </nav>
    </div>
  </div>

  <div class="container">
    <!-- Product Main Section -->
    <div class="product-main">
      <!-- Product Gallery -->
      <div class="product-gallery">
        <!-- Main Image -->
        <div class="main-image">
          <div class="image-container" id="mainImageContainer">
            <?php if (!empty($product_images[0]['image_url'])): ?>
              <img src="img/adminUP/products/<?php echo $product_images[0]['image_url']; ?>"
                   alt="<?php echo htmlspecialchars($product['name_pr']); ?>"
                   id="mainImage"
                   onerror="this.src='img/default-product.jpg'">
            <?php else: ?>
              <img src="img/default-product.jpg"
                   alt="<?php echo htmlspecialchars($product['name_pr']); ?>"
                   id="mainImage">
            <?php endif; ?>

            <!-- 3D View Button (optional) -->
            <?php if (count($product_images) >= 4): ?>
              <button class="view-3d-btn" id="view3dBtn">
                <i class="fas fa-cube"></i>
                Xem 360°
              </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Thumbnail Gallery -->
        <?php if (count($product_images) > 1): ?>
          <div class="thumbnail-gallery">
            <div class="thumbnails" id="thumbnails">
              <?php foreach ($product_images as $index => $image): ?>
                <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                     data-image="img/adminUP/products/<?php echo $image['image_url']; ?>">
                  <img src="img/adminUP/products/<?php echo $image['image_url']; ?>"
                       alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name_pr'] . ' - Ảnh ' . ($index + 1)); ?>"
                       onerror="this.src='img/default-product.jpg'">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Product Tags -->
        <div class="product-tags">
          <?php if ($has_discount): ?>
            <span class="tag sale">Giảm <?php echo $discount_percent; ?>%</span>
          <?php endif; ?>

          <?php if ($product['featured'] == 1): ?>
            <span class="tag featured">Nổi bật</span>
          <?php endif; ?>

          <?php if (isset($product['created_at']) && $product['created_at'] >= date('Y-m-d', strtotime('-30 days'))): ?>
            <span class="tag new">Mới</span>
          <?php endif; ?>

          <span class="tag <?php echo $stock_status; ?>">
                        <?php echo $stock_text; ?>
                    </span>
        </div>
      </div>

      <!-- Product Info -->
      <div class="product-info">
        <div class="product-header">
          <button id="addToCompareBtn"
                  data-id="<?= $product['id'] ?>"
                  data-category="<?= $product['category_id'] ?>"
                  class="compare_btn btn">
            <i class="fa-solid fa-code-compare"></i>  So sánh
          </button>

          <h1 class="product-title"><?php echo htmlspecialchars($product['name_pr']); ?></h1>
          <div class="product-sku">
            SKU: <span><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></span>
          </div>
          <div class="product-stock">
            <?php if ($stock_status == 'in-stock'): ?>
              <i class="fas fa-check-circle"></i>
            <?php elseif ($stock_status == 'low-stock'): ?>
              <i class="fas fa-exclamation-triangle"></i>
            <?php else: ?>
              <i class="fas fa-times-circle"></i>
            <?php endif; ?>
            <span class="stock-status <?php echo $stock_status; ?>">
                            <?php echo $stock_text; ?>
                        </span>
            <?php if ($stock_status == 'in-stock' && $stock_count > 0): ?>
              <span class="stock-count">(<?php echo $stock_count; ?> sản phẩm)</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Rating -->
        <?php
        $avg_rating = $product['rate'] ?? 0;
        $review_count = $product['num_buy'] ?? 0;
        $full_stars = floor($avg_rating);
        $has_half_star = ($avg_rating - $full_stars) >= 0.5;
        ?>
        <div class="product-rating">
          <div class="stars">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if ($i <= $full_stars): ?>
                <i class="fas fa-star"></i>
              <?php elseif ($has_half_star && $i == $full_stars + 1): ?>
                <i class="fas fa-star-half-alt"></i>
              <?php else: ?>
                <i class="far fa-star"></i>
              <?php endif; ?>
            <?php endfor; ?>
            <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
          </div>
          <span class="review-count">(<?php echo $review_count; ?> Lượt mua)</span>
        </div>

        <!-- Product Price -->
        <div class="product-price" id="productPrice">
          <div class="current-price" id="currentPrice"><?php echo formatPrice($current_price); ?></div>
          <?php if ($has_discount): ?>
            <div class="original-price" id="originalPrice"><?php echo formatPrice($regular_price); ?></div>
            <div class="discount-percent" id="discountPercent">Tiết kiệm <?php echo $discount_percent; ?>%</div>
          <?php endif; ?>
        </div>

        <!-- Dynamic Attributes Selection -->
        <?php if (!empty($attributes_data)): ?>
          <?php foreach ($attributes_data as $attribute): ?>
            <div class="option-section attribute-section" data-attribute-id="<?php echo $attribute['id']; ?>">
              <h4><?php echo htmlspecialchars($attribute['name']); ?></h4>

              <?php if (($attribute['type'] ?? 'text') == 'color'): ?>
                <!-- Color options -->
                <div class="color-options">
                  <?php foreach ($attribute['values'] as $value): ?>
                    <?php
                    $is_available = $value['has_variant'] ?? false;
                    $is_selected = false;

                    // Kiểm tra xem giá trị này có trong selected variant không
                    if ($selectedVariant && !empty($selectedVariant['attributes'])) {
                      foreach ($selectedVariant['attributes'] as $attr) {
                        if (isset($attr['value_id']) && $attr['value_id'] == $value['id']) {
                          $is_selected = true;
                          break;
                        }
                      }
                    }
                    ?>
                    <label class="color-option <?php echo $is_selected ? 'active' : ''; ?>
                                                  <?php echo !$is_available ? 'disabled' : ''; ?>"
                           data-attribute-id="<?php echo $attribute['id']; ?>"
                           data-value-id="<?php echo $value['id']; ?>"
                           data-value="<?php echo htmlspecialchars($value['value']); ?>"
                           title="<?php echo htmlspecialchars($value['value']); ?>">
                      <input type="radio"
                             name="attribute_<?php echo $attribute['id']; ?>"
                             value="<?php echo $value['id']; ?>"
                        <?php echo $is_selected ? 'checked' : ''; ?>
                        <?php echo !$is_available ? 'disabled' : ''; ?>>
                      <?php if (!empty($value['color_code'])): ?>
                        <span class="color-circle" style="background: <?php echo $value['color_code']; ?>"></span>
                      <?php else: ?>
                        <span class="color-circle" style="background: #<?php echo dechex(crc32($value['value']) & 0xffffff); ?>"></span>
                      <?php endif; ?>
                      <span class="color-name"><?php echo htmlspecialchars($value['value']); ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>

              <?php else: ?>
                <!-- Text/Select options -->
                <div class="attribute-options">
                  <?php foreach ($attribute['values'] as $value): ?>
                    <?php
                    $is_available = $value['has_variant'] ?? false;
                    $is_selected = false;

                    if ($selectedVariant && !empty($selectedVariant['attributes'])) {
                      foreach ($selectedVariant['attributes'] as $attr) {
                        if (isset($attr['value_id']) && $attr['value_id'] == $value['id']) {
                          $is_selected = true;
                          break;
                        }
                      }
                    }
                    ?>
                    <label class="attribute-option <?php echo $is_selected ? 'active' : ''; ?>
                                                  <?php echo !$is_available ? 'disabled' : ''; ?>"
                           data-attribute-id="<?php echo $attribute['id']; ?>"
                           data-value-id="<?php echo $value['id']; ?>"
                           data-value="<?php echo htmlspecialchars($value['value']); ?>">
                      <input type="radio"
                             name="attribute_<?php echo $attribute['id']; ?>"
                             value="<?php echo $value['id']; ?>"
                        <?php echo $is_selected ? 'checked' : ''; ?>
                        <?php echo !$is_available ? 'disabled' : ''; ?>>
                      <span class="option-box">
                                                <?php echo htmlspecialchars($value['value']); ?>
                                            </span>
                    </label>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <!-- Hidden field để lưu variant_id -->
          <input type="hidden" name="variant_id" id="selectedVariantId"
                 value="<?php echo $selectedVariant['id'] ?? ''; ?>">

          <!-- Hiển thị thông tin variant hiện tại -->
          <div class="selected-variant-info" id="selectedVariantInfo"
               style="<?php echo $selectedVariant ? '' : 'display: none;'; ?>">
            <div class="variant-stock">
              <i class="fas <?php echo $stock_status == 'in-stock' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
              <span class="stock-status <?php echo $stock_status; ?>"><?php echo $stock_text; ?></span>
              <?php if ($stock_status == 'in-stock' && $stock_count > 0): ?>
                <span class="stock-count">(<?php echo $stock_count; ?> sản phẩm)</span>
              <?php endif; ?>
            </div>

            <?php if ($selectedVariant && !empty($selectedVariant['sku'])): ?>
              <div class="variant-sku">
                Mã: <span><?php echo htmlspecialchars($selectedVariant['sku']); ?></span>
              </div>
            <?php endif; ?>

            <?php if ($selectedVariant && !empty($selectedVariant['weight'])): ?>
              <div class="variant-weight">
                Trọng lượng: <span><?php echo $selectedVariant['weight']; ?> kg</span>
              </div>
            <?php endif; ?>
          </div>

        <?php endif; ?>

        <!-- Quantity -->
        <div class="option-section">
          <h4>Số lượng</h4>
          <div class="quantity-controls">
            <button class="quantity-btn minus" type="button"
              <?php echo $stock_status == 'out-of-stock' ? 'disabled' : ''; ?>>-</button>
            <input type="number" class="quantity-input" id="variantQuantity"
                   value="1" min="1"
                   max="<?php echo $stock_count; ?>"
              <?php echo $stock_status == 'out-of-stock' ? 'disabled' : ''; ?>>
            <button class="quantity-btn plus" type="button"
              <?php echo $stock_status == 'out-of-stock' ? 'disabled' : ''; ?>>+</button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn-add-to-cart"
            <?php echo $stock_status == 'out-of-stock' ? 'disabled' : ''; ?>
                  data-product-id="<?php echo $product_id; ?>"
                  onclick="addToCart(<?= $product_id?>, <?= $selectedVariant['id']?>)">
            <i class="fas fa-shopping-cart"></i>
            <?php echo $stock_status == 'out-of-stock' ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
          </button>

          <button class="btn-buy-now"
            <?php echo $stock_status == 'out-of-stock' ? 'disabled' : ''; ?>
                  data-product-id="<?php echo $product_id; ?>">
            <i class="fas fa-bolt"></i>
            Mua ngay
          </button>

          <button type="button"
                  class="btn-wishlist <?= $productModel->checkIsWishList($user_id, $product_id) ? 'active' : '' ?>"
                  onclick="toggleWish(<?= $product_id ?>, this)">
            <i class="<?= $productModel->checkIsWishList($user_id, $product_id) ? 'fas' : 'far' ?> fa-heart"></i>
          </button>

        </div>

        <!-- Quick Features -->
        <div class="quick-features">
          <div class="feature">
            <i class="fas fa-shipping-fast"></i>
            <span>Giao hàng miễn phí</span>
          </div>
          <div class="feature">
            <i class="fas fa-shield-alt"></i>
            <span>Bảo hành 12 tháng</span>
          </div>
          <div class="feature">
            <i class="fas fa-undo"></i>
            <span>Đổi trả 30 ngày</span>
          </div>
          <div class="feature">
            <i class="fas fa-credit-card"></i>
            <span>Trả góp 0%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Tabs -->
    <div class="product-tabs">
      <nav class="tab-nav">
        <button class="tab-btn active" data-tab="description">Mô tả sản phẩm</button>
        <button class="tab-btn" data-tab="specifications">Thông số kỹ thuật</button>
        <button class="tab-btn" data-tab="reviews">Đánh giá & Nhận xét</button>
        <?php if (!empty($related_products)): ?>
          <button class="tab-btn" data-tab="comparison">Sản phẩm tương tự</button>
        <?php endif; ?>
      </nav>

      <!-- Description Tab -->
      <div class="tab-content active" id="description">
        <div class="description-content">
          <?php if (!empty($product['description'])): ?>
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
          <?php else: ?>
            <p>Đang cập nhật mô tả sản phẩm...</p>
          <?php endif; ?>

          <?php if (!empty($product['features'])): ?>
            <div class="features-grid">
              <?php
              $features = json_decode($product['features'] ?? '[]', true);
              if (is_array($features)):
                foreach ($features as $feature): ?>
                  <div class="feature-item">
                    <?php if (!empty($feature['icon'])): ?>
                      <i class="<?php echo htmlspecialchars($feature['icon']); ?>"></i>
                    <?php endif; ?>
                    <?php if (!empty($feature['title'])): ?>
                      <h4><?php echo htmlspecialchars($feature['title']); ?></h4>
                    <?php endif; ?>
                    <?php if (!empty($feature['description'])): ?>
                      <p><?php echo htmlspecialchars($feature['description']); ?></p>
                    <?php endif; ?>
                  </div>
                <?php endforeach;
              endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Specifications Tab -->
      <div class="tab-content" id="specifications">
        <div class="specs-table">
          <?php if (!empty($product_specs)): ?>
            <?php foreach ($product_specs as $spec): ?>
              <div class="spec-row">
                <div class="spec-name"><?php echo htmlspecialchars($spec['spec_name']); ?></div>
                <div class="spec-value"><?php echo htmlspecialchars($spec['spec_value']); ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Đang cập nhật thông số kỹ thuật...</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Reviews Tab -->
      <div class="tab-content" id="reviews">
        <div class="reviews-header">
          <div class="rating-summary">
            <div class="overall-rating">
              <div class="rating-score"><?php echo number_format($avg_rating, 1); ?>/5</div>
              <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <?php if ($i <= $full_stars): ?>
                    <i class="fas fa-star"></i>
                  <?php elseif ($has_half_star && $i == $full_stars + 1): ?>
                    <i class="fas fa-star-half-alt"></i>
                  <?php else: ?>
                    <i class="far fa-star"></i>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>
              <div class="rating-count"><?php echo $review_count; ?> đánh giá</div>
            </div>

          </div>
        </div>

        <!-- Reviews list -->
        <div class="reviews-list">
          <?php if (!empty($review_data)): ?>
            <?php foreach ($review_data as $review): ?>
              <div class="review-item">
                <div class="review-header">
                  <div class="reviewer">
                    <img
                      src="<?= !empty($review['avatar']) ? $review['avatar'] : 'img/no-avatar.png' ?>"
                      alt="User"
                    >
                    <div>
                      <div class="reviewer-name">
                        <?= htmlspecialchars($review['username']) ?>
                      </div>
                      <div class="review-date">
                        <?= htmlspecialchars($review['created_at'])?>
                      </div>
                    </div>
                  </div>

                  <div class="review-rating">
                    <div class="stars" data-rating="<?= (float)$review['rating'] ?>"></div>
                  </div>
                </div>

                <div class="review-content">
                  <h4><?= htmlspecialchars($review['title'] ?? '') ?></h4>
                  <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>

          <?php else: ?>
            <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Comparison Tab -->
      <?php if (!empty($related_products)): ?>
        <div class="tab-content" id="comparison">
          <div class="related-products-tab">
            <h3>Sản phẩm cùng danh mục</h3>
            <div class="products-grid">
              <?php foreach ($related_products as $related_product): ?>
                <?php
                $related_images = $productImgModel->getByProductId($related_product['id']);
                $related_image = !empty($related_images[0]['image_url']) ?
                  'img/adminUP/products/' . $related_images[0]['image_url'] :
                  'img/default-product.jpg';
                $related_price = !empty($related_product['sale_price']) ?
                  $related_product['sale_price'] : $related_product['regular_price'];
                ?>
                <div class="product-card">
                  <div class="product-image">
                    <a href="product_detail.php?id=<?php echo $related_product['id']; ?>">
                      <img src="<?php echo $related_image; ?>"
                           alt="<?php echo htmlspecialchars($related_product['name_pr']); ?>"
                           onerror="this.src='img/default-product.jpg'">
                    </a>
                  </div>
                  <div class="product-info">
                    <h4>
                      <a href="product_detail.php?id=<?php echo $related_product['id']; ?>">
                        <?php echo htmlspecialchars($related_product['name_pr']); ?>
                      </a>
                    </h4>
                    <div class="product-price">
                      <div class="current-price"><?php echo formatPrice($related_price); ?></div>
                      <?php if (!empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['regular_price']): ?>
                        <div class="original-price"><?php echo formatPrice($related_product['regular_price']); ?></div>
                      <?php endif; ?>
                    </div>
                    <a href="product_detail.php?id=<?php echo $related_product['id']; ?>" class="btn-view-detail">
                      Xem chi tiết
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
      <section class="related-products">
        <div class="section-header">
          <h2>Sản Phẩm Tương Tự</h2>
          <p>Những sản phẩm bạn có thể quan tâm</p>
        </div>
        <div class="products-grid" id="relatedProductsGrid">
          <?php foreach ($related_products as $related_product): ?>
            <?php
            $related_images = $productImgModel->getByProductId($related_product['id']);
            $related_image = !empty($related_images[0]['image_url']) ?
              'img/adminUP/products/' . $related_images[0]['image_url'] :
              'img/default-product.jpg';
            $related_price = !empty($related_product['sale_price']) ?
              $related_product['sale_price'] : $related_product['regular_price'];
            ?>
            <div class="product-card">
              <div class="product-image">
                <a href="product_detail.php?id=<?php echo $related_product['id']; ?>">
                  <img src="<?php echo $related_image; ?>"
                       alt="<?php echo htmlspecialchars($related_product['name_pr']); ?>"
                       onerror="this.src='img/default-product.jpg'">
                </a>
              </div>
              <div class="product-info">
                <h4>
                  <a href="product_detail.php?id=<?php echo $related_product['id']; ?>">
                    <?php echo htmlspecialchars($related_product['name_pr']); ?>
                  </a>
                </h4>
                <div class="product-price">
                  <div class="current-price"><?php echo formatPrice($related_price); ?></div>
                  <?php if (!empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['regular_price']): ?>
                    <div class="original-price"><?php echo formatPrice($related_product['regular_price']); ?></div>
                  <?php endif; ?>
                </div>
                <a href="product_detail.php?id=<?php echo $related_product['id']; ?>" class="btn-view-detail">
                  Xem chi tiết
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Recently Viewed Section -->
    <?php if (isset($_SESSION['recently_viewed']) && count($_SESSION['recently_viewed']) > 1): ?>
      <section class="recently-viewed">
        <div class="section-header">
          <h2>Sản Phẩm Vừa Xem</h2>
        </div>
        <div class="products-grid" id="recentlyViewedGrid">
          <?php
          $viewed_count = 0;
          foreach ($_SESSION['recently_viewed'] as $viewed_id):
            if ($viewed_id == $product_id || $viewed_count >= 4) continue;

            $viewed_product = $productModel->findById($viewed_id);
            if ($viewed_product):
              $viewed_images = $productImgModel->getByProductId($viewed_id);
              $viewed_image = !empty($viewed_images[0]['image_url']) ?
                'img/adminUP/products/' . $viewed_images[0]['image_url'] :
                'img/default-product.jpg';
              $viewed_price = !empty($viewed_product['sale_price']) ?
                $viewed_product['sale_price'] : $viewed_product['regular_price'];
              $viewed_count++;
              ?>
              <div class="product-card">
                <div class="product-image">
                  <a href="product_detail.php?id=<?php echo $viewed_id; ?>" class="product-name">
                    <img src="<?php echo $viewed_image; ?>"
                         alt="<?php echo htmlspecialchars($viewed_product['name_pr']); ?>"
                         onerror="this.src='img/default-product.jpg'">
                  </a>
                </div>
                <div class="product-info">
                  <h4><?php echo htmlspecialchars($viewed_product['name_pr']); ?>
<!--                    <a href="product_detail.php?id=--><?php //echo $viewed_id; ?><!--">-->
<!--                      -->
<!--                    </a>-->
                  </h4>
                  <div class="product-price">
                    <div class="current-price"><?php echo formatPrice($viewed_price); ?></div>
                    <?php if (!empty($viewed_product['sale_price']) && $viewed_product['sale_price'] < $viewed_product['regular_price']): ?>
                      <div class="original-price"><?php echo formatPrice($viewed_product['regular_price']); ?></div>
                    <?php endif; ?>
                  </div>
                  <a href="product_detail.php?id=<?php echo $viewed_id; ?>" class="btn-view-detail">
                    Xem lại
                  </a>
                </div>
              </div>
            <?php
            endif;
          endforeach;
          ?>
        </div>
      </section>
    <?php endif; ?>
  </div>

  <div id="toast-container"></div>
</div>



<script>
  // Dữ liệu từ PHP
  const variantsData = <?php echo json_encode($variants_data ?? []); ?>;
  const attributesData = <?php echo json_encode($attributes_data ?? []); ?>;
  const productImages = <?php echo json_encode($product_images ?? []); ?>;

  document.addEventListener('DOMContentLoaded', function() {
    // Map để truy xuất nhanh variant theo attributes key
    const variantsMap = {};
    variantsData.forEach(variant => {
      variantsMap[variant.attributes_key] = variant;
    });

    // Map ảnh theo ID
    const imagesMap = {};
    productImages.forEach(img => {
      imagesMap[img.id] = {
        url: 'img/adminUP/products/' + img.image_url,
        alt: img.alt_text || '<?php echo htmlspecialchars($product['name_pr']); ?>'
      };
    });

    // Lưu các lựa chọn hiện tại - KHỞI TẠO TỪ PHP TRƯỚC
    let selectedAttributes = {};

    // KHỞI TẠO selectedAttributes từ variant đang được chọn trong PHP
    const initialVariantId = <?php echo json_encode($selectedVariant['id'] ?? 0); ?>;
    const initialVariant = variantsData.find(v => v.id === initialVariantId);
    if (initialVariant && initialVariant.attributes) {
      initialVariant.attributes.forEach(attr => {
        selectedAttributes[attr.attribute_id] = attr.value_id;
      });
    }

    console.log('Initial selected attributes:', selectedAttributes);

    // ==== Thumbnail Gallery ====
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');

    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', function() {
        const currentVariantId = document.getElementById('selectedVariantId').value;
        const currentVariant = variantsData.find(v => v.id === currentVariantId);

        if (!currentVariant || !currentVariant.image_id) {
          thumbnails.forEach(t => t.classList.remove('active'));
          this.classList.add('active');
          mainImage.src = this.getAttribute('data-image');
        }
      });
    });

    // ==== Attribute Selection ====
    function setupAttributeSelection() {
      document.querySelectorAll('.attribute-option input, .color-option input')
        .forEach(input => {
          input.addEventListener('change', function() {

            let parent = this.closest('.attribute-option');
            if (!parent) parent = this.closest('.color-option');

            if (!parent) {
              console.error("Không tìm thấy attribute element.");
              return;
            }

            const attributeId = parent.dataset.attributeId;
            const valueId = this.value;

            console.log("Chọn:", attributeId, valueId);

            selectedAttributes[attributeId] = valueId;

            findMatchingVariant();
          });
        });
    }

    // ==== Tìm variant phù hợp ====
    function findMatchingVariant() {
      console.log('Finding variant for selectedAttributes:', selectedAttributes);

      if (Object.keys(selectedAttributes).length === 0) {
        const defaultVariant = variantsData.find(v => v.is_default) || variantsData[0];
        updateSelectedVariant(defaultVariant);
        return;
      }

      // Tạo key từ selected attributes
      const selectedPairs = [];
      Object.keys(selectedAttributes).sort().forEach(attrId => {
        selectedPairs.push(`${attrId}:${selectedAttributes[attrId]}`);
      });
      const searchKey = selectedPairs.join('|');

      console.log('Searching for variant with key:', searchKey);

      // Tìm variant chính xác
      let foundVariant = variantsMap[searchKey];

      // Nếu không tìm thấy chính xác
      if (!foundVariant) {
        // Tìm variant có chứa các attributes đã chọn
        for (const key in variantsMap) {
          const variantKeyParts = key.split('|');
          let allSelectedInVariant = true;

          // Kiểm tra xem tất cả selected attributes có trong variant không
          for (const pair of selectedPairs) {
            if (!variantKeyParts.includes(pair)) {
              allSelectedInVariant = false;
              break;
            }
          }

          if (allSelectedInVariant) {
            foundVariant = variantsMap[key];
            break;
          }
        }
      }

      if (foundVariant) {
        console.log('Found variant:', foundVariant);
        updateSelectedVariant(foundVariant);
        updateAttributeOptions(foundVariant);
      } else {
        console.log('No variant found');
        // Vẫn cập nhật UI dựa trên các lựa chọn hiện tại
        updateAttributeOptions(null);
      }
    }

    // ==== Cập nhật UI của attribute options ====
    function updateAttributeOptions(activeVariant) {
      console.log('Updating attribute options. Active variant:', activeVariant);
      console.log('Current selectedAttributes:', selectedAttributes);

      attributesData.forEach(attribute => {
        const attrId = attribute.id;

        // Lấy tất cả options của attribute này
        const options = document.querySelectorAll(
          `.attribute-option[data-attribute-id="${attrId}"],
           .color-option[data-attribute-id="${attrId}"]`
        );

        console.log(`Attribute ${attrId} has ${options.length} options`);

        // Reset tất cả options của attribute này
        options.forEach(option => {
          const input = option.querySelector('input');

          // Xóa active class
          option.classList.remove('active');

          // Nếu có input, uncheck
          if (input) {
            input.checked = false;
          }
        });

        // Xác định xem option nào đang được chọn
        const selectedValueId = selectedAttributes[attrId];
        console.log(`Selected value for attribute ${attrId}:`, selectedValueId);

        if (selectedValueId) {
          // Tìm và active option đang được chọn
          options.forEach(option => {
            const valueId = option.dataset.valueId;
            const input = option.querySelector('input');

            if (valueId === selectedValueId) {
              option.classList.add('active');
              if (input) {
                input.checked = true;
              }
              console.log(`Activated option with value ${valueId}`);
            }
          });
        }

        // Cập nhật availability của các options
        options.forEach(option => {
          const valueId = option.dataset.valueId;
          const input = option.querySelector('input');

          // Kiểm tra xem option này có khả dụng không
          let isAvailable = checkOptionAvailability(attrId, valueId, activeVariant);

          // Update trạng thái
          option.classList.toggle('disabled', !isAvailable);
          if (input) {
            input.disabled = !isAvailable;
          }
        });
      });
    }

    // ==== Kiểm tra availability của option ====
    function checkOptionAvailability(attrId, valueId, activeVariant) {
      if (!activeVariant) {
        // Nếu không có active variant, kiểm tra xem có variant nào chứa option này không
        for (const variant of variantsData) {
          if (variant.attributes && variant.attributes.some(attr =>
            attr.attribute_id === attrId && attr.value_id === valueId)) {
            return true;
          }
        }
        return false;
      }

      // Tạo attributes để test
      const testAttributes = {...selectedAttributes};
      testAttributes[attrId] = valueId;

      const testPairs = [];
      Object.keys(testAttributes).sort().forEach(aId => {
        testPairs.push(`${aId}:${testAttributes[aId]}`);
      });

      // Kiểm tra xem có variant nào match không
      for (const key in variantsMap) {
        const variantKeyParts = key.split('|');
        let allInVariant = true;

        for (const pair of testPairs) {
          if (!variantKeyParts.includes(pair)) {
            allInVariant = false;
            break;
          }
        }

        if (allInVariant) {
          return true;
        }
      }

      return false;
    }

    // ==== Cập nhật thông tin variant đã chọn ====
    function updateSelectedVariant(variant) {
      if (!variant) return;

      console.log('Updating to variant:', variant);

      // Cập nhật hidden field
      document.getElementById('selectedVariantId').value = variant.id;

      // Cập nhật giá
      updatePrice(variant);

      // Cập nhật stock và thông tin variant
      updateStockInfo(variant);

      // Cập nhật quantity controls
      // updateQuantityControls(variant);

      // Cập nhật action buttons
      updateActionButtons(variant);

      // Cập nhật ảnh nếu variant có ảnh riêng
      updateVariantImage(variant);
    }

    function updatePrice(variant) {
      const currentPrice = variant.sale_price || variant.price;
      const originalPrice = variant.sale_price ? variant.price : null;
      const discountPercent = originalPrice ?
        Math.round(((originalPrice - currentPrice) / originalPrice) * 100) : 0;

      document.getElementById('currentPrice').textContent = formatPrice(currentPrice);

      const originalPriceEl = document.getElementById('originalPrice');
      const discountPercentEl = document.getElementById('discountPercent');

      if (originalPrice && originalPrice > currentPrice) {
        originalPriceEl.textContent = formatPrice(originalPrice);
        discountPercentEl.textContent = `Tiết kiệm ${discountPercent}%`;
        originalPriceEl.style.display = 'block';
        discountPercentEl.style.display = 'block';
      } else {
        originalPriceEl.style.display = 'none';
        discountPercentEl.style.display = 'none';
      }
    }

    function updateStockInfo(variant) {
      const stockCount = variant.stock_quantity || 0;
      const stockStatus = stockCount > 0 ? 'in-stock' : 'out-of-stock';
      const stockText = stockCount > 0 ? 'Còn hàng' : 'Hết hàng';

      const variantInfo = document.getElementById('selectedVariantInfo');
      if (variantInfo) {
        variantInfo.innerHTML = `
          <div class="variant-stock">
            <i class="fas ${stockCount > 0 ? 'fa-check-circle' : 'fa-times-circle'}"></i>
            <span class="stock-status ${stockStatus}">${stockText}</span>
            ${stockCount > 0 ? `<span class="stock-count">(${stockCount} sản phẩm)</span>` : ''}
          </div>
          ${variant.sku ? `<div class="variant-sku">Mã: <span>${variant.sku}</span></div>` : ''}
          ${variant.weight ? `<div class="variant-weight">Trọng lượng: <span>${variant.weight} kg</span></div>` : ''}
        `;
        variantInfo.style.display = 'block';
      }
    }

    function updateQuantityControls(variant) {
      const quantityInput = document.getElementById('variantQuantity');
      const stockCount = variant.stock_quantity || 0;
      const maxQuantity = Math.min(stockCount, 99);

      if (quantityInput) {
        quantityInput.max = maxQuantity;
        quantityInput.disabled = stockCount <= 0;

        if (parseInt(quantityInput.value) > maxQuantity) {
          quantityInput.value = maxQuantity;
        }
      }

      document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.disabled = stockCount <= 0;
      });
    }

    function updateActionButtons(variant) {
      const stockCount = variant.stock_quantity || 0;

      document.querySelectorAll('.btn-add-to-cart, .btn-buy-now').forEach(btn => {
        btn.disabled = stockCount <= 0;
        if (btn.classList.contains('btn-add-to-cart')) {
          const icon = btn.querySelector('i');
          if (icon && icon.nextSibling) {
            icon.nextSibling.textContent = stockCount <= 0 ? ' Hết hàng' : ' Thêm vào giỏ hàng';
          }
        }
      });
    }

    function updateVariantImage(variant) {
      // Nếu variant có ảnh riêng
      if (variant.image_id && imagesMap[variant.image_id]) {
        const imageData = imagesMap[variant.image_id];

        // Cập nhật ảnh chính
        mainImage.src = imageData.url;

        // Tìm và kích hoạt thumbnail tương ứng
        let foundThumbnail = null;
        thumbnails.forEach(thumb => {
          if (thumb.getAttribute('data-image') === imageData.url) {
            foundThumbnail = thumb;
          }
        });

        if (foundThumbnail) {
          thumbnails.forEach(t => t.classList.remove('active'));
          foundThumbnail.classList.add('active');
        }
      }
    }

    // ==== Format price ====
    function formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        minimumFractionDigits: 0
      }).format(price) + '₫';
    }

    // ==== Khởi tạo ====
    function init() {
      console.log('Initializing...');
      console.log('Variants data:', variantsData);
      console.log('Attributes data:', attributesData);

      setupAttributeSelection();

      // Khởi tạo với variant hiện tại
      const currentVariantId = document.getElementById('selectedVariantId').value;
      console.log('Current variant ID:', currentVariantId);

      if (currentVariantId) {
        const currentVariant = variantsData.find(v => v.id === currentVariantId);
        if (currentVariant) {
          updateSelectedVariant(currentVariant);
          updateAttributeOptions(currentVariant);
        }
      } else {
        // Dùng variant mặc định
        const defaultVariant = variantsData.find(v => v.is_default) || variantsData[0];
        if (defaultVariant) {
          // Cập nhật selectedAttributes từ variant mặc định
          if (defaultVariant.attributes) {
            defaultVariant.attributes.forEach(attr => {
              selectedAttributes[attr.attribute_id] = attr.value_id;
            });
          }
          updateSelectedVariant(defaultVariant);
          updateAttributeOptions(defaultVariant);
        }
      }

      console.log('Initialization complete. Selected attributes:', selectedAttributes);
    }

    // Chạy khởi tạo
    init();
  });

  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      tabBtns.forEach(b => b.classList.remove('active'));
      tabContents.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      document.getElementById(tabId).classList.add('active');
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    const quantityInput = document.querySelector('.quantity-input');
    const btnMinus = document.querySelector('.quantity-btn.minus');
    const btnPlus = document.querySelector('.quantity-btn.plus');

    btnMinus.addEventListener('click', () => {
      let current = parseInt(quantityInput.value);
      const min = parseInt(quantityInput.min) || 1;

      if (current > min) {
        quantityInput.value = current - 1;
      }
    });

    btnPlus.addEventListener('click', () => {
      let current = parseInt(quantityInput.value);
      const max = parseInt(quantityInput.max);

      if (current < max) {
        quantityInput.value = current + 1;
      }
    });
  });

</script>

<div class="debug-info" id="debugInfo" style="display: none;">
  <div><strong>Debug Info:</strong></div>
  <div id="debugContent"></div>
</div>

<script>
  // Hàm để hiển thị debug info
  function showDebugInfo(message) {
    const debugInfo = document.getElementById('debugInfo');
    const debugContent = document.getElementById('debugContent');

    if (debugInfo && debugContent) {
      debugInfo.style.display = 'block';
      debugContent.innerHTML = message;

      // Tự động ẩn sau 5 giây
      setTimeout(() => {
        debugInfo.style.display = 'none';
      }, 5000);
    }
  }

  function toggleWish(productId, btn) {
    fetch("toggle_wishlist.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "product_id=" + productId
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "add") {
          btn.classList.add("active");
          showToast("Đã thêm vào yêu thích", "success");
        } else {
          btn.classList.remove("active");
          showToast("Đã xóa khỏi yêu thích", "success");
        }
      });
  }

  function showToast(message, type = "success") {
    const container = document.getElementById("toast-container");

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <span class="close-btn" onclick="this.parentElement.remove()">×</span>
    `;

    container.appendChild(toast);

    // Hiển thị
    setTimeout(() => {
      toast.classList.add("show");
    }, 50);

    // Tự ẩn sau 4 giây
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }
</script>

<script>
  function addToCart(productId, variantId) {

    // const productId = document.getElementById("productId").value;
    // const variantId = document.getElementById("selectedVariantId").value;
    const qty = document.querySelector(".quantity-input").value;

    console.log("SEND:", productId, variantId, qty);

    fetch("add_to_cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "product_id=" + productId +
        "&variant_id=" + variantId +
        "&quantity=" + qty
    })
      .then(res => res.json())
      .then(data => {

        console.log("RESPONSE:", data);

        if (data.status === "success") {
          showToast("Đã thêm vào giỏ hàng!", "success");

          // cập nhật số lượng giỏ hàng
          const countEl = document.querySelector(".cart-count");
          if (countEl) countEl.textContent = data.count;
        } else {
          showToast(data.msg, "error");
        }
      })
      .catch(err => {
        console.error("Fetch error:", err);
        showToast("Không thể kết nối server", "error");
      });
  }

  function renderReviews(list) {
    const container = document.getElementById('reviewList');

    if (!list || list.length === 0) {
      container.innerHTML = '<p>Chưa có đánh giá nào.</p>';
      return;
    }

    container.innerHTML = list.map(renderReviewItem).join('');
  }


  function renderStars(rating) {
    rating = Math.max(0, Math.min(5, rating));
    let html = '';

    for (let i = 1; i <= 5; i++) {
      if (rating >= i) {
        html += '<i class="fas fa-star"></i>';
      } else if (rating >= i - 0.5) {
        html += '<i class="fas fa-star-half-stroke"></i>';
      } else {
        html += '<i class="far fa-star"></i>';
      }
    }
    return html;
  }

  document.querySelectorAll('.stars[data-rating]').forEach(el => {
    const rating = parseFloat(el.dataset.rating);
    el.innerHTML = renderStars(rating);
  });
</script>

<script>
  document.getElementById('addToCompareBtn').addEventListener('click', () => {
    const productId = parseInt(addToCompareBtn.dataset.id);
    const categoryId = parseInt(addToCompareBtn.dataset.category);

    fetch('/apiPrivate/compare_save.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ productId, categoryId })
    })
      .then(res => res.json())
      .then(res => {
        if (!res.success) {
          alert(res.message);
          return;
        }
        window.history.back();
      });
  });
</script>

<?php include 'footer.php'; ?>

</body>
</html>
