<?php
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
  <link rel="stylesheet" href="css/product_detail.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>

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
        <span>/</span>
        <a href="products.php?category=smartphone">Điện thoại</a>
        <span>/</span>
        <a href="product_detail.php" class="active">iPhone 15 Pro Max</a>
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
            <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
                 alt="iPhone 15 Pro Max" id="mainImage">
            <!-- 3D View Button -->
            <button class="view-3d-btn" id="view3dBtn">
              <i class="fas fa-cube"></i>
              Xem 360°
            </button>
          </div>
        </div>

        <!-- Thumbnail Gallery -->
        <div class="thumbnail-gallery">
          <div class="thumbnails" id="thumbnails">
            <div class="thumbnail active" data-image="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80">
              <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Front View">
            </div>
            <div class="thumbnail" data-image="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80">
              <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Back View">
            </div>
            <div class="thumbnail" data-image="https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80">
              <img src="https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Side View">
            </div>
            <div class="thumbnail" data-image="https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80">
              <img src="https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Camera">
            </div>
          </div>
        </div>

        <!-- Product Tags -->
        <div class="product-tags">
          <span class="tag new">Mới</span>
          <span class="tag sale">Giảm 15%</span>
          <span class="tag limited">Bán chạy</span>
        </div>
      </div>

      <!-- Product Info -->
      <div class="product-info">
        <div class="product-header">
          <h1 class="product-title">iPhone 15 Pro Max 256GB</h1>
          <div class="product-sku">
            SKU: <span>IP15PM256-2024</span>
          </div>
          <div class="product-stock">
            <i class="fas fa-check-circle"></i>
            <span class="in-stock">Còn hàng</span>
            <span class="stock-count">(15 sản phẩm)</span>
          </div>
        </div>

        <!-- Rating -->
        <div class="product-rating">
          <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
            <span class="rating-value">4.7</span>
          </div>
          <span class="review-count">(128 đánh giá)</span>
          <a href="#reviews" class="see-reviews">Xem đánh giá</a>
        </div>
        <div class="product-price">
          <div class="current-price">32.990.000₫</div>
          <div class="original-price">38.990.000₫</div>
          <div class="discount-percent">Tiết kiệm 15%</div>
        </div>

        <!-- Color Selection -->
        <div class="option-section">
          <h4>Màu sắc</h4>
          <div class="color-options">
            <label class="color-option active">
              <input type="radio" name="color" value="titan-black" checked>
              <span class="color-circle" style="background: #1a1a1a;"></span>
              <span class="color-name">Titan đen</span>
            </label>
            <label class="color-option">
              <input type="radio" name="color" value="titan-white">
              <span class="color-circle" style="background: #f5f5f7; border: 1px solid #ddd;"></span>
              <span class="color-name">Titan trắng</span>
            </label>
            <label class="color-option">
              <input type="radio" name="color" value="titan-blue">
              <span class="color-circle" style="background: #007AFF;"></span>
              <span class="color-name">Titan xanh</span>
            </label>
            <label class="color-option">
              <input type="radio" name="color" value="titan-natural">
              <span class="color-circle" style="background: #E3BC91;"></span>
              <span class="color-name">Titan tự nhiên</span>
            </label>
          </div>
        </div>

        <!-- Storage Selection -->
        <div class="option-section">
          <h4>Dung lượng</h4>
          <div class="storage-options">
            <label class="storage-option">
              <input type="radio" name="storage" value="256gb">
              <span class="storage-box">256GB</span>
            </label>
            <label class="storage-option active">
              <input type="radio" name="storage" value="512gb" checked>
              <span class="storage-box">512GB</span>
            </label>
            <label class="storage-option">
              <input type="radio" name="storage" value="1tb">
              <span class="storage-box">1TB</span>
            </label>
          </div>
        </div>

        <!-- Quantity -->
        <div class="option-section">
          <h4>Số lượng</h4>
          <div class="quantity-controls">
            <button class="quantity-btn minus">-</button>
            <input type="number" class="quantity-input" value="1" min="1" max="15">
            <button class="quantity-btn plus">+</button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn-add-to-cart">
            <i class="fas fa-shopping-cart"></i>
            Thêm vào giỏ hàng
          </button>
          <button class="btn-buy-now">
            <i class="fas fa-bolt"></i>
            Mua ngay
          </button>
          <button class="btn-wishlist">
            <i class="far fa-heart"></i>
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
        <button class="tab-btn" data-tab="comparison">So sánh sản phẩm</button>
      </nav>

      <div class="tab-content active" id="description">
        <div class="description-content">
          <h3>iPhone 15 Pro Max - Đột phá trong thiết kế và hiệu năng</h3>
          <p>iPhone 15 Pro Max đánh dấu bước nhảy vọt về công nghệ với thiết kế titan cao cấp, chip A17 Pro mạnh mẽ và hệ thống camera chuyên nghiệp.</p>

          <div class="features-grid">
            <div class="feature-item">
              <i class="fas fa-microchip"></i>
              <h4>Chip A17 Pro</h4>
              <p>CPU nhanh hơn 10%, GPU nhanh hơn 20% so với thế hệ trước</p>
            </div>
            <div class="feature-item">
              <i class="fas fa-camera"></i>
              <h4>Camera 48MP</h4>
              <p>Hệ thống camera tiên tiến với ống kính tele 5x</p>
            </div>
            <div class="feature-item">
              <i class="fas fa-battery-full"></i>
              <h4>Pin cả ngày</h4>
              <p>Thời lượng pin lên đến 29 giờ xem video</p>
            </div>
            <div class="feature-item">
              <i class="fas fa-bolt"></i>
              <h4>USB-C</h4>
              <p>Kết nối USB-C với tốc độ truyền dữ liệu lên đến 10Gbps</p>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-content" id="specifications">
        <div class="specs-table">
          <div class="spec-row">
            <div class="spec-name">Màn hình</div>
            <div class="spec-value">6.7 inch, Super Retina XDR, Always-On</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Chip</div>
            <div class="spec-value">A17 Pro, 6 nhân CPU, 6 nhân GPU</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Camera sau</div>
            <div class="spec-value">48MP chính, 12MP ultra wide, 12MP tele 5x</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Camera trước</div>
            <div class="spec-value">12MP TrueDepth</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Bộ nhớ</div>
            <div class="spec-value">256GB / 512GB / 1TB</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Kết nối</div>
            <div class="spec-value">5G, Wi-Fi 6E, Bluetooth 5.3, USB-C</div>
          </div>
          <div class="spec-row">
            <div class="spec-name">Pin</div>
            <div class="spec-value">4441 mAh, sạc nhanh 20W</div>
          </div>
        </div>
      </div>

      <div class="tab-content" id="reviews">
        <div class="reviews-header">
          <div class="rating-summary">
            <div class="overall-rating">
              <div class="rating-score">4.7/5</div>
              <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
              <div class="rating-count">128 đánh giá</div>
            </div>
            <div class="rating-bars">
              <div class="rating-bar">
                <span>5 sao</span>
                <div class="bar">
                  <div class="fill" style="width: 70%;"></div>
                </div>
                <span>70%</span>
              </div>
              <div class="rating-bar">
                <span>4 sao</span>
                <div class="bar">
                  <div class="fill" style="width: 20%;"></div>
                </div>
                <span>20%</span>
              </div>
              <div class="rating-bar">
                <span>3 sao</span>
                <div class="bar">
                  <div class="fill" style="width: 7%;"></div>
                </div>
                <span>7%</span>
              </div>
              <div class="rating-bar">
                <span>2 sao</span>
                <div class="bar">
                  <div class="fill" style="width: 2%;"></div>
                </div>
                <span>2%</span>
              </div>
              <div class="rating-bar">
                <span>1 sao</span>
                <div class="bar">
                  <div class="fill" style="width: 1%;"></div>
                </div>
                <span>1%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="reviews-list">
          <div class="review-item">
            <div class="review-header">
              <div class="reviewer">
                <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User">
                <div>
                  <div class="reviewer-name">Nguyễn Thị Minh</div>
                  <div class="review-date">15/12/2024</div>
                </div>
              </div>
              <div class="review-rating">
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
              <h4>Rất hài lòng với sản phẩm!</h4>
              <p>Mình mua iPhone 15 Pro Max được 2 tuần, máy chạy rất mượt, pin trâu, camera chụp ảnh đẹp xuất sắc. Thiết kế titan sang trọng, cầm rất chắc tay.</p>
            </div>
          </div>

          <div class="review-item">
            <div class="review-header">
              <div class="reviewer">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User">
                <div>
                  <div class="reviewer-name">Trần Văn Hùng</div>
                  <div class="review-date">10/12/2024</div>
                </div>
              </div>
              <div class="review-rating">
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
              </div>
            </div>
            <div class="review-content">
              <h4>Sản phẩm tốt, nhưng giá hơi cao</h4>
              <p>Máy rất đẹp và mượt, nhưng giá thành khá cao so với mặt bằng chung. Camera chụp đêm rất ấn tượng, pin dùng được cả ngày.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-content" id="comparison">
        <div class="comparison-section">
          <h3>So sánh với iPhone 14 Pro Max</h3>
          <div class="comparison-table">
            <div class="comparison-row header">
              <div class="comparison-feature">Tính năng</div>
              <div class="comparison-product">iPhone 15 Pro Max</div>
              <div class="comparison-product">iPhone 14 Pro Max</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-feature">Chip xử lý</div>
              <div class="comparison-product">A17 Pro</div>
              <div class="comparison-product">A16 Bionic</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-feature">Camera chính</div>
              <div class="comparison-product">48MP</div>
              <div class="comparison-product">48MP</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-feature">Zoom quang học</div>
              <div class="comparison-product">5x</div>
              <div class="comparison-product">3x</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-feature">Cổng kết nối</div>
              <div class="comparison-product">USB-C</div>
              <div class="comparison-product">Lightning</div>
            </div>
            <div class="comparison-row">
              <div class="comparison-feature">Chất liệu</div>
              <div class="comparison-product">Titanium</div>
              <div class="comparison-product">Thép không gỉ</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bundle Products -->
    <section class="bundle-products">
      <div class="section-header">
        <h2>Mua Kèm Ưu Đãi</h2>
        <p>Tiết kiệm thêm khi mua combo sản phẩm</p>
      </div>
      <div class="bundle-offer">
        <div class="bundle-items">
          <div class="bundle-item main-product">
            <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="iPhone">
            <span>iPhone 15 Pro Max</span>
          </div>
          <div class="bundle-plus">+</div>
          <div class="bundle-item">
            <img src="https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="AirPods">
            <span>AirPods Pro 2</span>
          </div>
          <div class="bundle-plus">+</div>
          <div class="bundle-item">
            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="Apple Watch">
            <span>Apple Watch Series 9</span>
          </div>
        </div>
        <div class="bundle-pricing">
          <div class="bundle-original">45.980.000₫</div>
          <div class="bundle-discount">39.990.000₫</div>
          <div class="bundle-save">Tiết kiệm 5.990.000₫</div>
          <button class="bundle-btn">Mua Combo</button>
        </div>
      </div>
    </section>

    <!-- Related Products -->
    <section class="related-products">
      <div class="section-header">
        <h2>Sản Phẩm Tương Tự</h2>
        <p>Những sản phẩm bạn có thể quan tâm</p>
      </div>
      <div class="products-grid" id="relatedProductsGrid">
        <!-- Related products will be populated by JavaScript -->
      </div>
    </section>

    <!-- Recently Viewed -->
    <section class="recently-viewed">
      <div class="section-header">
        <h2>Sản Phẩm Vừa Xem</h2>
      </div>
      <div class="products-grid" id="recentlyViewedGrid">
        <!-- Recently viewed products will be populated by JavaScript -->
      </div>
    </section>
  </div>
</div>

<!-- 3D View Modal -->
<div class="modal" id="modal3d">
  <div class="modal-content large">
    <button class="modal-close" id="close3dModal">
      <i class="fas fa-times"></i>
    </button>
    <h3>Xem 360° - iPhone 15 Pro Max</h3>
    <div class="viewer-3d">
      <div class="viewer-container" id="viewer3d">
        <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
             alt="3D View" id="viewerImage">
        <div class="viewer-controls">
          <button class="viewer-btn" id="rotateLeft">
            <i class="fas fa-undo"></i>
          </button>
          <button class="viewer-btn" id="rotateRight">
            <i class="fas fa-redo"></i>
          </button>
          <button class="viewer-btn" id="zoomIn">
            <i class="fas fa-search-plus"></i>
          </button>
          <button class="viewer-btn" id="zoomOut">
            <i class="fas fa-search-minus"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Product detail page loaded');

    // Sample data
    const relatedProducts = [
      {
        id: 1,
        name: "Samsung Galaxy S24 Ultra",
        image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 24990000,
        originalPrice: 27990000
      },
      {
        id: 2,
        name: "Google Pixel 8 Pro",
        image: "https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 21990000,
        originalPrice: 23990000
      },
      {
        id: 3,
        name: "Xiaomi 13 Ultra",
        image: "https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 18990000,
        originalPrice: 20990000
      },
      {
        id: 4,
        name: "OnePlus 11",
        image: "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 16990000,
        originalPrice: 18990000
      }
    ];

    const recentlyViewed = [
      {
        id: 1,
        name: "iPad Pro 12.9 M2",
        image: "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 27990000
      },
      {
        id: 2,
        name: "MacBook Air M2",
        image: "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 28990000
      }
    ];

    // Thumbnail Gallery
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');

    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', function() {
        // Remove active class from all thumbnails
        thumbnails.forEach(t => t.classList.remove('active'));

        // Add active class to clicked thumbnail
        this.classList.add('active');

        // Update main image
        const newImage = this.getAttribute('data-image');
        mainImage.src = newImage;
      });
    });

    // Color Selection
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
      option.addEventListener('click', function() {
        colorOptions.forEach(o => o.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Storage Selection
    const storageOptions = document.querySelectorAll('.storage-option');
    storageOptions.forEach(option => {
      option.addEventListener('click', function() {
        storageOptions.forEach(o => o.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Quantity Controls
    const quantityInput = document.querySelector('.quantity-input');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');

    minusBtn.addEventListener('click', function() {
      let value = parseInt(quantityInput.value);
      if (value > 1) {
        quantityInput.value = value - 1;
      }
    });

    plusBtn.addEventListener('click', function() {
      let value = parseInt(quantityInput.value);
      const max = parseInt(quantityInput.getAttribute('max'));
      if (value < max) {
        quantityInput.value = value + 1;
      }
    });

    // Tab Switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');

        // Remove active class from all buttons and contents
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));

        // Add active class to clicked button and corresponding content
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');
      });
    });

    // 3D Viewer
    const view3dBtn = document.getElementById('view3dBtn');
    const modal3d = document.getElementById('modal3d');
    const close3dModal = document.getElementById('close3dModal');
    const viewerImage = document.getElementById('viewerImage');
    const rotateLeft = document.getElementById('rotateLeft');
    const rotateRight = document.getElementById('rotateRight');
    const zoomIn = document.getElementById('zoomIn');
    const zoomOut = document.getElementById('zoomOut');

    let currentRotation = 0;
    let currentScale = 1;

    // 3D images for rotation (in real app, these would be actual 360° images)
    const rotationImages = [
      'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
    ];

    view3dBtn.addEventListener('click', function() {
      modal3d.classList.add('show');
      resetViewer();
    });

    close3dModal.addEventListener('click', function() {
      modal3d.classList.remove('show');
    });

    modal3d.addEventListener('click', function(e) {
      if (e.target === modal3d) {
        modal3d.classList.remove('show');
      }
    });

    rotateLeft.addEventListener('click', function() {
      currentRotation = (currentRotation - 1 + rotationImages.length) % rotationImages.length;
      updateViewerImage();
    });

    rotateRight.addEventListener('click', function() {
      currentRotation = (currentRotation + 1) % rotationImages.length;
      updateViewerImage();
    });

    zoomIn.addEventListener('click', function() {
      currentScale = Math.min(currentScale * 1.2, 3);
      updateViewerTransform();
    });

    zoomOut.addEventListener('click', function() {
      currentScale = Math.max(currentScale / 1.2, 0.5);
      updateViewerTransform();
    });

    function updateViewerImage() {
      viewerImage.src = rotationImages[currentRotation];
    }

    function updateViewerTransform() {
      viewerImage.style.transform = `scale(${currentScale})`;
    }

    function resetViewer() {
      currentRotation = 0;
      currentScale = 1;
      updateViewerImage();
      updateViewerTransform();
    }

    // Action Buttons
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    const buyNowBtn = document.querySelector('.btn-buy-now');
    const wishlistBtn = document.querySelector('.btn-wishlist');

    addToCartBtn.addEventListener('click', function() {
      const quantity = parseInt(quantityInput.value);
      const color = document.querySelector('input[name="color"]:checked').value;
      const storage = document.querySelector('input[name="storage"]:checked').value;

      alert(`Đã thêm vào giỏ hàng:\nSản phẩm: iPhone 15 Pro Max\nMàu: ${color}\nDung lượng: ${storage}\nSố lượng: ${quantity}`);
    });

    buyNowBtn.addEventListener('click', function() {
      alert('Chuyển hướng đến trang thanh toán...');
      // window.location.href = 'checkout.php';
    });

    wishlistBtn.addEventListener('click', function() {
      const icon = this.querySelector('i');
      if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        this.style.color = '#e74c3c';
        alert('Đã thêm vào danh sách yêu thích!');
      } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        this.style.color = '#666';
        alert('Đã xóa khỏi danh sách yêu thích!');
      }
    });

    // Load related products
    function loadRelatedProducts() {
      const grid = document.getElementById('relatedProductsGrid');
      grid.innerHTML = '';

      relatedProducts.forEach(product => {
        const productCard = createProductCard(product);
        grid.appendChild(productCard);
      });
    }

    // Load recently viewed
    function loadRecentlyViewed() {
      const grid = document.getElementById('recentlyViewedGrid');
      grid.innerHTML = '';

      recentlyViewed.forEach(product => {
        const productCard = createProductCard(product, false);
        grid.appendChild(productCard);
      });
    }

    // Create product card
    function createProductCard(product, showOriginalPrice = true) {
      const div = document.createElement('div');
      div.className = 'product-card';

      let originalPriceHtml = '';
      if (showOriginalPrice && product.originalPrice) {
        originalPriceHtml = `<div class="original-price">${formatPrice(product.originalPrice)}</div>`;
      }

      div.innerHTML = `
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h4>${product.name}</h4>
                <div class="product-price">
                    <div class="current-price">${formatPrice(product.price)}</div>
                    ${originalPriceHtml}
                </div>
                <button class="btn-view-detail" data-id="${product.id}">
                    Xem chi tiết
                </button>
            </div>
        `;
      return div;
    }

    // Format price
    function formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price);
    }

    // Bundle button
    const bundleBtn = document.querySelector('.bundle-btn');
    bundleBtn.addEventListener('click', function() {
      alert('Đã thêm combo sản phẩm vào giỏ hàng!');
    });

    // Initialize
    loadRelatedProducts();
    loadRecentlyViewed();
    console.log('Product detail page initialized');
  });
</script>

<?php include 'footer.php'?>

</body>
</html>
