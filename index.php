<?php
session_start();

require_once 'class/db.php';
require_once 'class/product.php';
require_once 'class/flash_sale_manager.php';
require_once 'class/product_image.php';
require_once 'class/category.php';

$flashSaleModel = new FlashSaleManager(); // Thay thế bằng tên model của bạn
$productImg = new ProductImage();

// Lấy flash sale đang diễn ra (active)
$activeFlashSale = $flashSaleModel->getAllFlashSales('active');

// Biến để lưu dữ liệu
$currentFlashSale = null;
$saleProducts = [];
$hasFlashSale = false;
$upcomingFlashSales = [];

// Kiểm tra và lấy dữ liệu
if (!empty($activeFlashSale)) {
  // Lấy flash sale đầu tiên đang diễn ra
  $currentFlashSale = $activeFlashSale[0];
  $saleProducts = $flashSaleModel->getProductsInSaleIndex($currentFlashSale['id']);
  $hasFlashSale = true;
} else {
  // Nếu không có flash sale đang diễn ra, lấy flash sale sắp diễn ra
  $upcomingFlashSales = $flashSaleModel->getAllFlashSales('upcoming');
}

//Products
// 1. Khởi tạo model sản phẩm
$productModel = new Product(); // Thay bằng class thực tế của bạn
$categoryModel = new Category();

// 2. Lấy sản phẩm mới nhất (giả sử có hàm getNewestProducts)
$newProducts = $productModel->getNewestProducts(10); // Lấy 10 sản phẩm

// 4. Chuẩn bị mảng sản phẩm cho JavaScript
$productsData = [];
foreach ($newProducts as $product) {
  // Lấy hình ảnh chính
  $mainImage = $productImg->getMainImage($product['id']);
  $imageUrl = $mainImage ? 'img/adminUP/products/' . $mainImage['image_url'] :
    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';

  // Lấy danh mục
  $category_if = $categoryModel->findById($product['category_id']);
  $categoryName = !empty($category_if['name']) ? $category_if['name'] : 'Không có danh mục';

  // Format giá
  $currentPrice = !empty($product['sale_price']) ? $product['sale_price'] : $product['regular_price'];
  $originalPrice = (!empty($product['sale_price']) && $product['sale_price'] < $product['regular_price']) ?
    $product['regular_price'] : null;

  // Lấy rating (nếu có)
  $rating = isset($product['rate']) ? round($product['rate'], 1) : 'Chưa có đánh giá';
  $reviewCount = isset($product['num_buy']) ? $product['num_buy'] : 'Chưa có lượt mua';

  $productsData[] = [
    'id' => $product['id'],
    'name' => $product['name_pr'],
    'category' => $categoryName,
    'current_price' => number_format($currentPrice, 0, ',', '.') . 'đ',
    'original_price' => $originalPrice ? number_format($originalPrice, 0, ',', '.') . 'đ' : null,
    'rating' => $rating,
    'reviews' => $reviewCount,
    'image' => $imageUrl,
    'slug' => $product['slug'] ?? ''
  ];
}
?>

<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shop Tech</title>
  <meta name="description" content="">

  <meta property="og:title" content="">
  <meta property="og:type" content="">
  <meta property="og:url" content="">
  <meta property="og:image" content="">
  <meta property="og:image:alt" content="">

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="icon.png">

  <link rel="manifest" href="site.webmanifest">
<!--  css-->
  <link rel="stylesheet" href="css/style.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>


<?php include 'header.php'?>
<?php include 'cornerButton.php'?>
<body>
<!-- Hero Slider -->
<section class="hero-slider">
  <div class="slides">
    <div class="slide">
      <img src="img/slideintro/a27d24_82d036f8249d4d008426380b6163004c~mv2.png" alt="Fashion Collection">
      <div class="slide-content">
        <h2>Bộ Sưu Tập</h2>
        <p>Khám phá những xu hướng công nghệ mới nhất năm nay, hiệu năng cực đỉnh.</p>
        <a href="#" class="btn">Mua Ngay</a>
      </div>
    </div>
    <div class="slide">
      <img src="img/slideintro/antec_c8_2494f9b3a784455aa3e5d5e8eff54520.jpg" alt="New Arrivals">
      <div class="slide-content">
        <h2>Sản Phẩm Mới Về</h2>
        <p>Cập nhật những mẫu thiết kế mới nhất, phong cách và thời thượng</p>
        <a href="#" class="btn">Khám Phá</a>
      </div>
    </div>
    <div class="slide">
      <img src="img/slideintro/man-hinh-laptop-4k-uhd-la-gi-2.png" alt="Sale Off">
      <div class="slide-content">
        <h2>Giảm Giá Lên Đến 50%</h2>
        <p>Ưu đãi đặc biệt trong tháng này, đừng bỏ lỡ cơ hội mua sắm với giá tốt nhất</p>
        <a href="#flashsale" class="btn">Xem Ngay</a>
      </div>
    </div>
    <div class="slide">
      <img src="img/slideintro/1765974319_6942a12fa5b7e.jpg" alt="Limited Edition">
      <div class="slide-content">
        <h2>Tự Build cho mình 1 bộ PC ngay tại shop</h2>
        <p>Shop hỗ trợ hướng dẫn, build PC ngay tại quán, hãy đến thử ngay nhé</p>
        <a href="contact.php" class="btn">Tìm Hiểu Thêm</a>
      </div>
    </div>
  </div>

<!--  <div class="slides">-->
<!--    --><?php
//    $slideDir = __DIR__ . '/img/slideintro/';
//    $slideUrl = 'img/slideintro/';
//
//    $images = glob($slideDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
//
//    foreach ($images as $img):
//      $fileName = basename($img);
//      ?>
<!--      <div class="slide">-->
<!--        <img src="--><?php //= $slideUrl . $fileName ?><!--" alt="Slide">-->
<!--        <div class="slide-content">-->
<!--          <h2>Slide giới thiệu</h2>-->
<!--          <p>Nội dung mô tả cho slide</p>-->
<!--          <a href="#" class="btn">Xem thêm</a>-->
<!--        </div>-->
<!--      </div>-->
<!--    --><?php //endforeach; ?>
<!--  </div>-->

  <div class="slider-nav">
    <div class="slider-dot active"></div>
    <div class="slider-dot"></div>
    <div class="slider-dot"></div>
    <div class="slider-dot"></div>
  </div>
  <div class="slider-arrows">
    <div class="slider-arrow prev"><i class="fas fa-chevron-left"></i></div>
    <div class="slider-arrow next"><i class="fas fa-chevron-right"></i></div>
  </div>
</section>

<section class="flash-sale" id="flashsale">
  <div class="fl container">
    <?php if ($hasFlashSale): ?>
      <!-- Hiển thị khi có flash sale đang diễn ra -->
      <div class="sale-header">
        <h2 class="sale-title">
          <i class="fa-solid fa-bolt fa-bounce" style="color: #e6b400; margin-right: 20px"></i>
          FLASH SALE
        </h2>

        <!-- Countdown sẽ được cập nhật bằng JavaScript -->
        <div class="countdown" id="countdown">
          <div class="countdown-item">
            <span class="countdown-number" id="hours">00</span>
            <span class="countdown-label">Giờ</span>
          </div>
          <div class="countdown-item">
            <span class="countdown-number" id="minutes">00</span>
            <span class="countdown-label">Phút</span>
          </div>
          <div class="countdown-item">
            <span class="countdown-number" id="seconds">00</span>
            <span class="countdown-label">Giây</span>
          </div>
        </div>

        <p style="font-size: 24px; color: #ffffff;">Ưu đãi đặc biệt chỉ diễn ra trong thời gian giới hạn. Nhanh tay kẻo lỡ!</p>

        <!-- Thông tin thời gian -->
        <div class="sale-time-info" style="margin-top: 10px; font-size: 24px; color: #ffffff;">
          <i class="far fa-clock"></i>
          Thời gian:
          <?php echo date('H:i d/m/Y', strtotime($currentFlashSale['time_start'])); ?>
          -
          <?php echo date('H:i d/m/Y', strtotime($currentFlashSale['time_end'])); ?>
        </div>
      </div>

      <div class="sale-container">
        <div class="sale-nav prev" id="salePrev">
          <i class="fas fa-chevron-left"></i>
        </div>

        <div class="sale-products" id="saleProducts">
          <?php if (!empty($saleProducts)): ?>
            <?php foreach ($saleProducts as $product): ?>
              <div class="sale-product">
                <div class="product-img">
                  <?php
                  $productImage = $productImg->getMainImage($product['product_id'])
                  ?>
                  <img src="<?php echo 'img/adminUP/products/' . $productImage['image_url']; ?>"
                       alt="<?php echo htmlspecialchars($productImage['alt_text']); ?>">
                  <span class="sale-badge">-<?php echo $product['discount_percent']; ?>%</span>
                </div>
                <div class="product-info">
                  <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                  <div class="sale-price">
                    <?php
                    $salePrice = $product['calculated_sale_price'];
                    $originalPrice = $product['original_price'];
                    ?>
                    <span class="discount-price">
                                            <?php echo number_format($salePrice, 0, ',', '.'); ?>đ
                                        </span>
                    <span class="original-price">
                                            <?php echo number_format($originalPrice, 0, ',', '.'); ?>đ
                                        </span>
                    <span class="discount-percent">-<?php echo $product['discount_percent']; ?>%</span>
                  </div>
                  <button class="add-to-cart"
                          data-product-id="<?php echo $product['product_id']; ?>">
                    <i class="fas fa-bolt"></i>
                    Mua Ngay
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-products">
              <p>Hiện chưa có sản phẩm nào trong đợt flash sale này.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="sale-nav next" id="saleNext">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>

      <div class="sale-dots" id="saleDots">
        <!-- Dots sẽ được tạo bằng JavaScript -->
      </div>

      <!-- Ẩn dữ liệu thời gian kết thúc để JavaScript sử dụng -->
      <input type="hidden" id="flashSaleEndTime"
             value="<?php echo $currentFlashSale['time_end']; ?>">

    <?php elseif (!empty($upcomingFlashSales)): ?>
      <!-- Hiển thị khi có flash sale sắp diễn ra -->
      <div class="sale-header">
        <h2 class="sale-title">
          <i class="fa-solid fa-clock fa-spin" style="color: #007bff; margin-right: 20px"></i>
          FLASH SALE SẮP DIỄN RA
        </h2>
        <p>Chương trình flash sale tiếp theo sẽ bắt đầu vào
          <strong><?php echo date('H:i d/m/Y', strtotime($upcomingFlashSales[0]['time_start'])); ?></strong>
        </p>
      </div>
      <div class="upcoming-message">
        <p>Hãy quay lại sau để không bỏ lỡ ưu đãi!</p>
      </div>

    <?php else: ?>
      <!-- Hiển thị khi không có flash sale nào -->
      <div class="no-flash-sale">
        <p>Hiện không có chương trình flash sale nào đang diễn ra.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Newest Products -->
<section class="products-container">
  <div class="container">
    <div class="section-title">
      <h2>Sản Phẩm</h2>
    </div>
    <div class="products-scroll">
      <div class="products-track" id="newProductsTrack">
        <?php foreach ($productsData as $product_ec): ?>
          <div class="product-card">
            <div class="product-img">
              <img src="<?php echo $product_ec['image']?>" alt="">
            </div>
            <div class="product-info">
              <div class="product-category"><?php echo $product_ec['category']?></div>
              <div class="product-name"><?php echo $product_ec['name']?></div>
              <div class="product-price">
                <span class="current-price"><?php echo $product_ec['current_price']?></span>
                <span class="original-price"><?php echo $product_ec['original_price']?></span>
              </div>
              <div class="product-rating">
                <div class="stars">
                  <?php echo $product_ec['rating']?>
                </div>
                <span class="rating-count">(<?php echo $product_ec['reviews']?>)</span>
              </div>
              <a class="add-to-cart" href="product_detail.php?id=<?php echo $product_ec['id']; ?>">
                Xem thêm
                <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="section-title">
      <a class="btn btn-see-more" href="products.php">Xem tất cả sản phẩm <i class="fa-solid fa-arrow-right"></i></a>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
  <div class="container">
    <div class="section-title">
      <h2>Sản Phẩm Nổi Bật</h2>
    </div>

    <!-- Best Seller -->
    <div class="featured-item">
      <div class="featured-img">
        <img src="https://cafefcdn.com/203337114487263232/2025/7/27/iphone-17-pro-orange-1753607288074-17536072883691184565064.jpg" alt="Best Seller">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Bán Chạy Nhất</span>
        <h3>Điện thoại IPhone 17 Pro Max</h3>
        <p>iPhone 17 Pro Max tại Việt Nam có màn hình Super Retina XDR 6,9 inch, chip A19 Pro 6 nhân, RAM 12GB, bộ nhớ trong từ 256GB đến 2TB, hệ thống 3 camera sau 48MP và pin dung lượng lớn. Thiết bị có thiết kế khung nhôm nguyên khối, mặt kính Ceramic Shield 2, và các tùy chọn màu sắc như Bạc, Cam Vũ Trụ, Xanh Đậm.</p>
        <p>Đã bán: 1 sản phẩm</p>
        <a href="#" class="btn">Mua Ngay</a>
      </div>
    </div>

    <!-- Newest -->
    <div class="featured-item reverse">
      <div class="featured-img">
        <img src="https://product.hstatic.net/1000187560/product/tai-nghe-bluetooth-chup-tai-havit-h667bt-den-1_3423bc0d67db4c5e92a5e561b8482cca_large.jpg" alt="Newest">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Mới Nhất</span>
        <h3>Tai nghe Bluetooth Headphone Havit H667BT Pin 20 tiếng | Kết Nối 2 Thiết Bị | BT 5.3</h3>
        <p>Tai nghe Bluetooth Headphone Havit H667BT mang kiểu dáng năng động, gam màu thanh lịch, khả năng kết nối linh hoạt có dây và không dây, tích hợp mic thoại,... đáp ứng tốt nhu cầu sử dụng cơ bản của người dùng phổ thông.</p>
        <p>Phiên bản giới hạn 2025</p>
        <a href="#" class="btn">Mua Ngay</a>
      </div>
    </div>

    <!-- Rare -->
    <div class="featured-item">
      <div class="featured-img">
        <img src="https://tintuc.dienthoaigiakho.vn/wp-content/uploads/2025/05/iphone-18-khi-nao-ra-mat.3.jpg" alt="Rare">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Độc Quyền</span>
        <h3>Điện thoại IPhone 18 Pro Max </h3>
        <p>Hé lộ bí mật từ nhà táo với phiên bản IPhone 18 series với những hứa hẹn về nâng cấp về mọi mặt.</p>
        <p>Số lượng cực kỳ hạn chế</p>
        <a href="#" class="btn">Mua ngay</a>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="newsletter">
  <div class="container">
    <h2>Đăng Ký Nhận Tin</h2>
    <p>Nhận thông tin về sản phẩm mới, khuyến mãi đặc biệt và xu hướng thời trang mới nhất</p>
    <form class="newsletter-form">
      <input type="email" placeholder="Nhập email của bạn...">
      <button type="submit">Đăng Ký</button>
    </form>
  </div>
</section>

<!-- Customer Reviews Section -->
<section class="customer-reviews">
  <div class="container">
    <div class="section-title">
      <h2>Đánh Giá Từ Khách Hàng</h2>
    </div>
    <div class="reviews-container">
      <div class="review-card">
        <div class="review-header">
          <div class="review-avatar">
            <img src="https://media.baoquangninh.vn/dataimages/202006/original/images1398180_viet2.jpg" alt="Customer Avatar">
          </div>
          <div class="review-info">
            <h4>Nguyễn Thị Minh</h4>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
        <div class="review-content">
          "Chiếc điện thoại này thực sự vượt xa kỳ vọng với hiệu năng xử lý cực kỳ mượt mà ngay cả khi chơi các game đồ họa nặng, cùng với thời lượng pin ấn tượng giúp tôi thoải mái sử dụng trọn vẹn một ngày làm việc. Hệ thống camera kép cũng cho ra những bức ảnh sắc nét và màu sắc trung thực đáng ngạc nhiên."
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="review-avatar">
            <img src="https://jbagy.me/wp-content/uploads/2025/03/Hinh-anh-avatar-anime-nu-cute-2.jpg" alt="Customer Avatar">
          </div>
          <div class="review-info">
            <h4>Trần Văn Hùng</h4>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
              <i class="fa-regular fa-star"></i>
            </div>
          </div>
        </div>
        <div class="review-content">
          "Tôi hơi thất vọng về chất liệu của chiếc đồng hồ thông minh này, dây đeo có vẻ kém bền so với mức giá cao cấp, tuy nhiên, các tính năng theo dõi sức khỏe và GPS lại hoạt động cực kỳ chính xác và là công cụ hỗ trợ tập luyện không thể thiếu của tôi."
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="review-avatar">
            <img src="https://png.pngtree.com/thumb_back/fh260/background/20221021/pngtree-happy-asian-woman-cooking-in-the-kitchen-housewife-food-vietnamese-photo-image_39055990.jpg" alt="Customer Avatar">
          </div>
          <div class="review-info">
            <h4>Lê Thị Hương</h4>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
        <div class="review-content">
          "Loa Bluetooth này có thiết kế nhỏ gọn và đẹp mắt, rất tiện mang theo, nhưng âm bass lại hơi yếu và bị rè nhẹ khi mở âm lượng tối đa. Tuy nhiên, ở mức âm lượng vừa phải, chất âm vẫn trong trẻo, phù hợp để nghe nhạc nền trong phòng làm việc."
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Tech News Section -->
<section class="tech-news">
  <div class="container">
    <div class="section-title">
      <h2>Tin Tức Công Nghệ</h2>
    </div>
    <div class="news-grid">
      <div class="news-card">
        <div class="news-image">
          <img src="https://cdn.tgdd.vn/Files/2023/09/15/1547384/5copy-150923-103044.jpg" alt="Tech News">
        </div>
        <div class="news-content">
          <span class="news-category">Tin Mới</span>
          <h3 class="news-title">iPhone 15 Series Chính Thức Ra Mắt Với Tính Năng Đột Phá</h3>
          <p class="news-excerpt">Apple vừa chính thức trình làng dòng iPhone 15 với camera 48MP, chip A17 Pro và cổng USB-C đầu tiên...</p>
          <div class="news-meta">
            <span><i class="far fa-clock"></i> 2 tháng trước</span>
            <span><i class="far fa-eye"></i> 1.1K lượt xem</span>
          </div>
        </div>
      </div>
      <div class="news-card">
        <div class="news-image">
          <img src="https://vitinhtrangia.com/wp-content/uploads/2022/08/pc-gaming-i5-12600k-rtx-3060-12gb-1.jpg" alt="Guide">
        </div>
        <div class="news-content">
          <span class="news-category">Hướng Dẫn</span>
          <h3 class="news-title">Hướng Dẫn Build PC Gaming Tối Ưu Với Ngân Sách 20 Triệu</h3>
          <p class="news-excerpt">Tự lắp ráp PC gaming không khó với hướng dẫn chi tiết này. Tối ưu hiệu năng chơi game với mức ngân sách hợp lý...</p>
          <div class="news-meta">
            <span><i class="far fa-clock"></i> 2 ngày trước</span>
            <span><i class="far fa-eye"></i> 3.4K lượt xem</span>
          </div>
        </div>
      </div>
      <div class="news-card">
        <div class="news-image">
          <img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Tips">
        </div>
        <div class="news-content">
          <span class="news-category">Mẹo Hay</span>
          <h3 class="news-title">5 Mẹo Tăng Hiệu Suất PC Gaming Mà Bạn Cần Biết</h3>
          <p class="news-excerpt">Khám phá những mẹo đơn giản nhưng hiệu quả để tối ưu hóa hiệu suất PC gaming của bạn, từ cài đặt driver đến tinh chỉnh hệ thống...</p>
          <div class="news-meta">
            <span><i class="far fa-clock"></i> 3 ngày trước</span>
            <span><i class="far fa-eye"></i> 5.1K lượt xem</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- PC Combos Section -->
<section class="pc-combos">
  <div class="container">
    <div class="section-title">
      <h2>Combo PC & Laptop Gợi Ý</h2>
    </div>
    <div class="combo-grid">
      <div class="combo-card">
        <div class="combo-header">
          <h3 class="combo-title">PC Gaming Budget</h3>
          <div class="combo-price">12.990.000đ</div>
          <p>Phù hợp cho game thủ mới bắt đầu</p>
        </div>
        <div class="combo-features">
          <ul>
            <li><i class="fas fa-check"></i> CPU: Intel Core i5-12400F</li>
            <li><i class="fas fa-check"></i> GPU: NVIDIA GeForce RTX 3060</li>
            <li><i class="fas fa-check"></i> RAM: 16GB DDR4 3200MHz</li>
            <li><i class="fas fa-check"></i> SSD: 512GB NVMe M.2</li>
          </ul>
          <div class="combo-specs">
            <div class="spec-item">
              <span>Hiệu năng gaming:</span>
              <span>★★★★☆</span>
            </div>
            <div class="spec-item">
              <span>Độ ồn:</span>
              <span>★★★☆☆</span>
            </div>
            <div class="spec-item">
              <span>Nâng cấp:</span>
              <span>★★★★☆</span>
            </div>
          </div>
          <a href="#" class="combo-btn">Chọn Combo Này</a>
        </div>
      </div>
      <div class="combo-card">
        <div class="combo-header">
          <h3 class="combo-title">Laptop Văn Phòng</h3>
          <div class="combo-price">15.490.000đ</div>
          <p>Hoàn hảo cho công việc và học tập</p>
        </div>
        <div class="combo-features">
          <ul>
            <li><i class="fas fa-check"></i> Laptop: Dell Inspiron 15</li>
            <li><i class="fas fa-check"></i> CPU: Intel Core i7-1165G7</li>
            <li><i class="fas fa-check"></i> RAM: 16GB DDR4</li>
            <li><i class="fas fa-check"></i> SSD: 1TB + Office 365</li>
          </ul>
          <div class="combo-specs">
            <div class="spec-item">
              <span>Hiệu năng:</span>
              <span>★★★★★</span>
            </div>
            <div class="spec-item">
              <span>Pin:</span>
              <span>★★★★☆</span>
            </div>
            <div class="spec-item">
              <span>Màn hình:</span>
              <span>★★★★☆</span>
            </div>
          </div>
          <a href="#" class="combo-btn">Chọn Combo Này</a>
        </div>
      </div>
      <div class="combo-card">
        <div class="combo-header">
          <h3 class="combo-title">Combo Gaming Gear</h3>
          <div class="combo-price">2.990.000đ</div>
          <p>Trọn bộ phụ kiện gaming chất lượng</p>
        </div>
        <div class="combo-features">
          <ul>
            <li><i class="fas fa-check"></i> Bàn phím cơ Keychron K8</li>
            <li><i class="fas fa-check"></i> Chuột gaming Logitech G502</li>
            <li><i class="fas fa-check"></i> Tai nghe HyperX Cloud II</li>
            <li><i class="fas fa-check"></i> Mousepad extended</li>
          </ul>
          <div class="combo-specs">
            <div class="spec-item">
              <span>Chất lượng:</span>
              <span>★★★★★</span>
            </div>
            <div class="spec-item">
              <span>Độ bền:</span>
              <span>★★★★☆</span>
            </div>
            <div class="spec-item">
              <span>Thẩm mỹ:</span>
              <span>★★★★★</span>
            </div>
          </div>
          <a href="#" class="combo-btn">Chọn Combo Này</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<?php include 'footer.php'?>

<script src="js/indexJS.js"></script>

</html>
