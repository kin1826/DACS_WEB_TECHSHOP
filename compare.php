<?php
session_start();
require_once 'class/product.php';
require_once 'class/product_image.php';
require_once 'class/product_specification.php';
require_once 'class/brand.php';
require_once 'class/category.php';

$compare = $_SESSION['compare'] ?? null;

if (!$compare || empty($compare['products']) || count($compare['products']) < 2) {
  header('Location: products.php');
  exit;
}

$productIds = array_values(array_unique(array_map('intval', $compare['products'])));

$productModel      = new Product();
$imageModel        = new ProductImage();
$specModel         = new ProductSpecification();
$brandModel        = new Brand();
$categoryModel     = new Category();

$products = [];

// ===============================
// GOM DATA THEO TỪNG PRODUCT
// ===============================
foreach ($productIds as $id) {
  $product = $productModel->findById($id);

  if (!$product || $product['status'] !== 'published') {
    continue;
  }

  $brand    = $brandModel->findById($product['brand_id']);
  $category = $categoryModel->findById($product['category_id']);
  $images   = $imageModel->getByProductId($id);
  $specs    = $specModel->getByProductId($id);

  $images = $imageModel->getByProductId($id);

  foreach ($images as &$img) {
    if (!empty($img['image_url'])) {
      $img['image_url'] = 'img/adminUP/products/' . ltrim($img['image_url'], '/');
    }
  }
  unset($img); // QUAN TRỌNG


  $products[] = [
    // ================= BASIC =================
    'id'    => $product['id'],
    'sku'   => $product['sku'],
    'name'  => $product['name_pr'],
    'slug'  => $product['slug'],

    // ================= CONTENT =================
    'description'        => $product['description'],
    'short_description'  => $product['short_description'],

    // ================= PRICE =================
    'price' => [
      'regular' => (float)$product['regular_price'],
      'sale'    => (float)$product['sale_price'],
      'percent' => (float)$product['percent_reduce'],
    ],

    // ================= STOCK =================
    'stock' => [
      'quantity' => (int)$product['stock_quantity'],
      'status'   => $product['stock_status'],
    ],

    // ================= META =================
    'rating' => [
      'rate'     => (float)$product['rate'],
      'num_buy'  => (int)$product['num_buy'],
    ],

    // ================= BRAND =================
    'brand' => $brand ? [
      'id'   => $brand['id'],
      'name' => $brand['name'],
      'logo' => $brand['logo'],
    ] : null,

    // ================= CATEGORY =================
    'category' => $category ? [
      'id'   => $category['id'],
      'name' => $category['name'],
    ] : null,

    // ================= MEDIA =================
    'images' => $images,

    // ================= SPECS =================
    'specifications' => array_reduce($specs, function ($carry, $item) {
      $carry[$item['spec_name']] = $item['spec_value'];
      return $carry;
    }, []),
  ];
}

if (count($products) < 2) {
  header('Location: products.php');
  exit;
}


$products = array_slice($products, 0, 2);
$left  = $products[0];
$right = $products[1];

function price($p): string
{
  return number_format($p['price']['sale'] > 0 ? $p['price']['sale'] : $p['price']['regular']);
}

// Chuẩn bị dữ liệu cho AI
//$productDataForAI = [];
//foreach ($products as $p) {
//  $productDataForAI[] = [
//    'name' => $p['name'],
//    'brand' => $p['brand'],
//    'price' => $p['price'],
//    'description' => $p['description'],
//    'specifications' => $specMap[$p['id']]
//  ];
//}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>So sánh sản phẩm</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/compare.css">
</head>

<?php include 'header.php'?>
<?php include 'cornerButton.php'?>
<body>
<div class="container">
  <div class="top-content">
    <h1>So sánh sản phẩm</h1>
    <p>So sánh chi tiết hai sản phẩm để đưa ra lựa chọn tốt nhất</p>

    <div class="compare-actions-div">
      <button class="btn btn-outline" onclick="window.location.href='products.php'">
        <i class="fas fa-arrow-left"></i> Quay lại sản phẩm
      </button>
      <button class="btn btn-danger" onclick="clearComparison()">
        <i class="fas fa-trash"></i> Xóa so sánh
      </button>
    </div>
  </div>

  <div class="compare-grid">
    <!-- Sản phẩm bên trái -->
    <div class="product-column">
      <div class="product-header">
        <h2 class="product-name"><?php echo htmlspecialchars($left['name']); ?></h2>
        <div class="product-sku">Mã: <?php echo htmlspecialchars($left['sku']); ?></div>

        <div class="product-price">
          <?php echo price($left); ?>₫
          <?php if ($left['price']['sale'] > 0 && $left['price']['sale'] < $left['price']['regular']): ?>
            <del><?php echo number_format($left['price']['regular']); ?>₫</del>
            <span class="discount-badge">-<?php echo $left['price']['percent']; ?>%</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="product-image-section">
        <div class="main-image-container" id="main-image-left">
          <?php if (!empty($left['images'])): ?>
            <img src="<?php echo htmlspecialchars($left['images'][0]['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($left['name']); ?>"
                 class="main-image" id="current-image-left">
          <?php else: ?>
            <img src="https://via.placeholder.com/400x300?text=No+Image"
                 alt="No image" class="main-image">
          <?php endif; ?>
        </div>

        <div class="thumbnail-container" id="thumbnail-left">
          <?php if (!empty($left['images'])): ?>
            <?php foreach ($left['images'] as $index => $image): ?>
              <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                   onclick="changeImage('left', <?php echo $index; ?>)">
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>"
                     alt="Thumbnail <?php echo $index + 1; ?>">
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="product-info">
        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>

          <div class="info-row">
            <span class="info-label">Thương hiệu:</span>
            <span class="info-value">
                                <?php if ($left['brand']): ?>
                                  <div class="brand-info">
                                        <?php if (!empty($left['brand']['logo'])): ?>
                                          <div class="brand-logo">
                                                <img src="<?php echo htmlspecialchars($left['brand']['logo']); ?>"
                                                     alt="<?php echo htmlspecialchars($left['brand']['name']); ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php echo htmlspecialchars($left['brand']['name']); ?>
                                    </div>
                                <?php else: ?>
                                  Không xác định
                                <?php endif; ?>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Danh mục:</span>
            <span class="info-value">
                                <?php echo $left['category'] ? htmlspecialchars($left['category']['name']) : 'Không xác định'; ?>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Tình trạng:</span>
            <span class="info-value">
                                <?php
                                $stockStatus = $left['stock']['status'];
                                $stockClass = ($stockStatus === 'in_stock') ? 'in-stock' : 'out-of-stock';
                                ?>
                                <span class="stock-status <?php echo $stockClass; ?>">
                                    <?php
                                    echo ($stockStatus === 'in_stock')
                                      ? 'Còn hàng (' . $left['stock']['quantity'] . ')'
                                      : 'Hết hàng';
                                    ?>
                                </span>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Đánh giá:</span>
            <span class="info-value">
                                <span class="rating-stars">
                                    <?php
                                    $rating = $left['rating']['rate'];
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                                    for ($i = 1; $i <= 5; $i++) {
                                      if ($i <= $fullStars) {
                                        echo '<i class="fas fa-star"></i>';
                                      } elseif ($hasHalfStar && $i == $fullStars + 1) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                      } else {
                                        echo '<i class="far fa-star"></i>';
                                      }
                                    }
                                    ?>
                                </span>
                                <span class="rating-count">
                                    (<?php echo $left['rating']['rate']; ?>/5 từ <?php echo $left['rating']['num_buy']; ?> lượt mua)
                                </span>
                            </span>
          </div>
        </div>

        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-file-alt"></i> Mô tả ngắn</h3>
          <p><?php echo nl2br(htmlspecialchars($left['short_description'])); ?></p>
        </div>

        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-list"></i> Thông số kỹ thuật</h3>

          <?php if (!empty($left['specifications'])): ?>
            <table class="specifications-table">
              <?php foreach ($left['specifications'] as $name => $value): ?>
                <tr>
                  <td class="spec-name"><?php echo htmlspecialchars($name); ?>:</td>
                  <td class="spec-value"><?php echo htmlspecialchars($value); ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php else: ?>
            <p>Không có thông số kỹ thuật.</p>
          <?php endif; ?>
        </div>

        <div class="product-actions">
          <button class="btn btn-primary btn-small" onclick="viewProduct('<?php echo $left['id']; ?>')">
            <i class="fas fa-eye"></i> Xem chi tiết
          </button>
        </div>
      </div>
    </div>

    <!-- Cột AI ở giữa -->
    <div class="ai-column">
      <div class="particles" id="ai-particles"></div>

      <div class="ai-header">
        <div class="ai-icon">
          <i class="fas fa-robot"></i>
        </div>
        <h3>AI So sánh</h3>
        <p>Phân tích thông minh từ hệ thống AI</p>
      </div>

      <div class="ai-content">

<!--        <div class="ai-message">-->
<!--          <h4><i class="fas fa-lightbulb"></i> Phân tích chung</h4>-->
<!--          <p>Hệ thống AI đang phân tích và so sánh hai sản phẩm dựa trên thông số kỹ thuật, giá cả và đánh giá người dùng.</p>-->
<!--        </div>-->
        <div class="ai-features">
          <button onclick="sendToAI(true)" class="ai-feature btn">
            <i class="fa-solid fa-arrow-rotate-right"></i>Phân tích lại
          </button>
        </div>

<!--        <div class="ai-loading" id="ai-loading">-->
<!--          <div class="spinner"><i class="fa-solid fa-spinner fa-spin spinner"></i></div>-->
<!--          <p>Đang phân tích sản phẩm...</p>-->
<!--        </div>-->

        <div class="ai-loading" id="ai-loading">
          <div class="ai-spinner">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <p>AI đang phân tích sản phẩm…</p>
        </div>

        <div id="ai-results" style="display: none;">
          <!-- Kết quả AI sẽ được hiển thị ở đây -->
          <div class="ai-message">
            <h4><i class="fas fa-chart-line"></i>Gợi ý so sánh</h4>
            <p class="ai-typing" id="aiResult">Vui lòng đợi AI phân tích</p>
          </div>

<!--          <div class="ai-message">-->
<!--            <h4><i class="fas fa-award"></i> Đề xuất</h4>-->
<!--            <p>Nếu bạn quan tâm đến --><?php //echo ($left['rating']['rate'] > $right['rating']['rate']) ? htmlspecialchars($left['name']) : htmlspecialchars($right['name']); ?><!--, đây là lựa chọn được đánh giá cao hơn từ người dùng.</p>-->
<!--          </div>-->
<!---->
<!--          <div class="ai-features">-->
<!--            <div class="ai-feature">-->
<!--              <i class="fas fa-bolt"></i> Phân tích nhanh-->
<!--            </div>-->
<!--            <div class="ai-feature">-->
<!--              <i class="fas fa-chart-bar"></i> So sánh chi tiết-->
<!--            </div>-->
<!--            <div class="ai-feature">-->
<!--              <i class="fas fa-shield-alt"></i> Đề xuất khách quan-->
<!--            </div>-->
<!--          </div>-->
        </div>
      </div>
    </div>

    <!-- Sản phẩm bên phải -->
    <div class="product-column">
      <div class="product-header">
        <h2 class="product-name"><?php echo htmlspecialchars($right['name']); ?></h2>
        <div class="product-sku">Mã: <?php echo htmlspecialchars($right['sku']); ?></div>

        <div class="product-price">
          <?php echo price($right); ?>₫
          <?php if ($right['price']['sale'] > 0 && $right['price']['sale'] < $right['price']['regular']): ?>
            <del><?php echo number_format($right['price']['regular']); ?>₫</del>
            <span class="discount-badge">-<?php echo $right['price']['percent']; ?>%</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="product-image-section">
        <div class="main-image-container" id="main-image-right">
          <?php if (!empty($right['images'])): ?>
            <img src="<?php echo htmlspecialchars($right['images'][0]['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($right['name']); ?>"
                 class="main-image" id="current-image-right">
          <?php else: ?>
            <img src="https://via.placeholder.com/400x300?text=No+Image"
                 alt="No image" class="main-image">
          <?php endif; ?>
        </div>

        <div class="thumbnail-container">
          <?php if (!empty($right['images'])): ?>
            <?php foreach ($right['images'] as $index => $image): ?>
              <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                   onclick="changeImage('right', <?php echo $index; ?>)">
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>"
                     alt="Thumbnail <?php echo $index + 1; ?>">
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="product-info">
        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>

          <div class="info-row">
            <span class="info-label">Thương hiệu:</span>
            <span class="info-value">
                                <?php if ($right['brand']): ?>
                                  <div class="brand-info">
                                        <?php if (!empty($right['brand']['logo'])): ?>
                                          <div class="brand-logo">
                                                <img src="<?php echo htmlspecialchars($right['brand']['logo']); ?>"
                                                     alt="<?php echo htmlspecialchars($right['brand']['name']); ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php echo htmlspecialchars($right['brand']['name']); ?>
                                    </div>
                                <?php else: ?>
                                  Không xác định
                                <?php endif; ?>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Danh mục:</span>
            <span class="info-value">
                                <?php echo $right['category'] ? htmlspecialchars($right['category']['name']) : 'Không xác định'; ?>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Tình trạng:</span>
            <span class="info-value">
                                <?php
                                $stockStatus = $right['stock']['status'];
                                $stockClass = ($stockStatus === 'in_stock') ? 'in-stock' : 'out-of-stock';
                                ?>
                                <span class="stock-status <?php echo $stockClass; ?>">
                                    <?php
                                    echo ($stockStatus === 'in_stock')
                                      ? 'Còn hàng (' . $right['stock']['quantity'] . ')'
                                      : 'Hết hàng';
                                    ?>
                                </span>
                            </span>
          </div>

          <div class="info-row">
            <span class="info-label">Đánh giá:</span>
            <span class="info-value">
                                <span class="rating-stars">
                                    <?php
                                    $rating = $right['rating']['rate'];
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                                    for ($i = 1; $i <= 5; $i++) {
                                      if ($i <= $fullStars) {
                                        echo '<i class="fas fa-star"></i>';
                                      } elseif ($hasHalfStar && $i == $fullStars + 1) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                      } else {
                                        echo '<i class="far fa-star"></i>';
                                      }
                                    }
                                    ?>
                                </span>
                                <span class="rating-count">
                                    (<?php echo $right['rating']['rate']; ?>/5 từ <?php echo $right['rating']['num_buy']; ?> lượt mua)
                                </span>
                            </span>
          </div>
        </div>

        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-file-alt"></i> Mô tả ngắn</h3>
          <p><?php echo nl2br(htmlspecialchars($right['short_description'])); ?></p>
        </div>

        <div class="info-section">
          <h3 class="section-title"><i class="fas fa-list"></i> Thông số kỹ thuật</h3>

          <?php if (!empty($right['specifications'])): ?>
            <table class="specifications-table">
              <?php foreach ($right['specifications'] as $name => $value): ?>
                <tr>
                  <td class="spec-name"><?php echo htmlspecialchars($name); ?>:</td>
                  <td class="spec-value"><?php echo htmlspecialchars($value); ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php else: ?>
            <p>Không có thông số kỹ thuật.</p>
          <?php endif; ?>
        </div>

        <div class="product-actions">
          <button class="btn btn-primary btn-small" onclick="viewProduct('<?php echo $right['id']; ?>')">
            <i class="fas fa-eye"></i> Xem chi tiết
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Dữ liệu ảnh cho hai sản phẩm
  const productImages = {
    left: <?php echo json_encode($left['images'] ?? []); ?>,
    right: <?php echo json_encode($right['images'] ?? []); ?>
  };

  // Hàm chuyển đổi ảnh khi click vào thumbnail
  function changeImage(side, index) {
    const images = productImages[side];
    if (!images || !images[index]) return;

    // Cập nhật ảnh chính
    const mainImage = document.getElementById(`current-image-${side}`);
    mainImage.src = images[index].image_url;
    mainImage.alt = `Product image ${index + 1}`;

    // Cập nhật trạng thái active của thumbnail
    const thumbnails = document.querySelectorAll(`#main-image-${side}`).parentElement.nextElementSibling.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, i) => {
      if (i === index) {
        thumb.classList.add('active');
      } else {
        thumb.classList.remove('active');
      }
    });
  }

  // Hàm xem chi tiết sản phẩm
  function viewProduct(id) {
    window.location.href = `product_detail.php?id=${id}`;
  }

  // Hàm xóa so sánh
  function clearComparison() {
    if (confirm('Bạn có chắc chắn muốn xóa so sánh này?')) {
      // Gửi yêu cầu xóa so sánh
      fetch('apiPrivate/compare_clear.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'products.php';
          } else {
            alert('Có lỗi xảy ra khi xóa so sánh.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Có lỗi xảy ra khi xóa so sánh.');
        });
    }
  }

  // Mô phỏng quá trình AI phân tích
  document.addEventListener('DOMContentLoaded', () => {
    sendToAI(false);
  });

  // Tạo hiệu ứng particles cho cột AI
  function createParticles() {
    const container = document.getElementById('ai-particles');
    if (!container) return;

    // Xóa particles cũ nếu có
    container.innerHTML = '';

    // Tạo 15 particles
    for (let i = 0; i < 100; i++) {
      const particle = document.createElement('div');
      particle.classList.add('particle');

      // Random kích thước
      const size = Math.random() * 10 + 25;
      particle.style.width = `${size}px`;
      particle.style.height = `${size}px`;

      // Random vị trí
      particle.style.left = `${Math.random() * 100}%`;
      particle.style.top = `${Math.random() * 100}%`;

      // Random độ trễ animation
      particle.style.animationDelay = `${Math.random() * 30}s`;

      // Random thời gian animation
      const duration = Math.random() * 10 + 10;
      particle.style.animationDuration = `${duration}s`;

      container.appendChild(particle);
    }
  }

  // Khởi tạo particles khi trang load
  document.addEventListener('DOMContentLoaded', function() {
    createParticles();

    // Tạo lại particles mỗi 15 giây để đa dạng
    setInterval(createParticles, 10000);
  });
</script>

<script>
  const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>;

  function buildComparePrompt(products) {
    if (!Array.isArray(products) || products.length !== 2) {
      throw new Error('Cần đúng 2 sản phẩm để so sánh');
    }

    const [p1, p2] = products;

    return `
      Bạn là chuyên gia tư vấn mua sắm công nghệ.

      Hãy so sánh CHI TIẾT 2 sản phẩm sau và đưa ra kết luận rõ ràng.

      YÊU CẦU:
      - So sánh dựa trên: giá, thông số kỹ thuật, tình trạng kho, đánh giá người dùng
      - Chỉ ra ưu / nhược điểm từng sản phẩm
      - Kết luận: nên chọn sản phẩm nào và vì sao
      - Trả lời bằng tiếng Việt, ngắn gọn dễ hiểu, không dùng markdown

      ====================
      SẢN PHẨM 1
      ====================
      Tên: ${p1.name}
      Thương hiệu: ${p1.brand?.name ?? 'Không rõ'}
      Giá niêm yết: ${p1.price.regular}
      Giá bán: ${p1.price.sale > 0 ? p1.price.sale : p1.price.regular}
      Tồn kho: ${p1.stock.quantity} (${p1.stock.status})
      Đánh giá: ${p1.rating.rate}/5 (${p1.rating.num_buy} lượt mua)
      Mô tả ngắn: ${p1.short_description}

      Thông số kỹ thuật:
      ${Object.entries(p1.specifications)
            .map(([k, v]) => `- ${k}: ${v}`)
            .join('\n')}

      ====================
      SẢN PHẨM 2
      ====================
      Tên: ${p2.name}
      Thương hiệu: ${p2.brand?.name ?? 'Không rõ'}
      Giá niêm yết: ${p2.price.regular}
      Giá bán: ${p2.price.sale > 0 ? p2.price.sale : p2.price.regular}
      Tồn kho: ${p2.stock.quantity} (${p2.stock.status})
      Đánh giá: ${p2.rating.rate}/5 (${p2.rating.num_buy} lượt mua)
      Mô tả ngắn: ${p2.short_description}

      Thông số kỹ thuật:
      ${Object.entries(p2.specifications)
            .map(([k, v]) => `- ${k}: ${v}`)
            .join('\n')}

      ====================
      KẾT LUẬN CUỐI:
      Sản phẩm nào phù hợp hơn với người dùng phổ thông? Vì sao?
      `.trim();
  }

  async function sendToAI(force = false) {
    try {
      // (optional) show loading
      showAILoading();

      const prompt = buildComparePrompt(products);
      console.log(prompt);

      const res = await fetch('apiPrivate/ai_compare.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          prompt: prompt,
          force: force // 👈 key quan trọng
        })
      });

      const data = await res.json();
      console.log('AI RESPONSE:', data);

      if (data.error) {
        document.getElementById('aiResult').innerText =
          'Lỗi AI: ' + data.error;
        return;
      }

      // document.getElementById('aiResult').innerText = data.result;
      showAIResult();

      const el = document.getElementById('aiResult');
      typeText(el, data.result);

      // (optional) thông báo cache
      if (data.cached) {
        console.log('Dùng kết quả AI từ session');
      } else {
        console.log('AI vừa phân tích mới');
      }

    } catch (err) {
      document.getElementById('aiResult').innerText =
        'Không thể kết nối AI';
      console.error(err);
    }
  }

  function typeText(element, text, speed = 5) {
    let i = 0;
    element.textContent = '';
    element.classList.add('cursor');

    const typing = setInterval(() => {
      element.textContent += text.charAt(i);
      i++;

      if (i >= text.length) {
        clearInterval(typing);
        element.classList.remove('cursor');
      }
    }, speed);
  }

  function showAILoading() {
    document.getElementById('ai-loading').style.display = 'flex';
    document.getElementById('ai-results').style.display = 'none';
  }

  function showAIResult() {
    document.getElementById('ai-loading').style.display = 'none';
    document.getElementById('ai-results').style.display = 'block';
  }

</script>
</body>

<?php include 'footer.php'?>
</html>
