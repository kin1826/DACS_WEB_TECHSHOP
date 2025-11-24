<?php
session_start();


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

<body>
<!-- Hero Slider -->
<section class="hero-slider">
  <div class="slides">
    <div class="slide">
      <img src="img/slideintro/a27d24_82d036f8249d4d008426380b6163004c~mv2.png" alt="Fashion Collection">
      <div class="slide-content">
        <h2>Bộ Sưu Tập Mùa Hè 2023</h2>
        <p>Khám phá những xu hướng thời trang mới nhất với thiết kế độc đáo và chất lượng cao cấp</p>
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
        <a href="#" class="btn">Xem Ngay</a>
      </div>
    </div>
    <div class="slide">
      <img src="img/slideintro/pngtree-rgb-lit-gaming-keyboard-and-3d-rendered-pc-case-for-ultimate-image_3705804.jpg" alt="Limited Edition">
      <div class="slide-content">
        <h2>Phiên Bản Giới Hạn</h2>
        <p>Sở hữu những sản phẩm độc quyền với số lượng có hạn, chỉ dành cho khách hàng đặc biệt</p>
        <a href="#" class="btn">Tìm Hiểu Thêm</a>
      </div>
    </div>
  </div>
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

<!-- Flash Sale Section Updated -->
<section class="flash-sale">
  <div class="fl container">
    <div class="sale-header">
      <h2 class="sale-title"><i class="fa-solid fa-bolt fa-bounce" style="color: #e6b400; margin-right: 20px"></i>FLASH SALE</h2>
      <div class="countdown" id="countdown">
        <div class="countdown-item">
          <span class="countdown-number" id="hours">12</span>
          <span class="countdown-label">Giờ</span>
        </div>
        <div class="countdown-item">
          <span class="countdown-number" id="minutes">45</span>
          <span class="countdown-label">Phút</span>
        </div>
        <div class="countdown-item">
          <span class="countdown-number" id="seconds">30</span>
          <span class="countdown-label">Giây</span>
        </div>
      </div>
      <p>Ưu đãi đặc biệt chỉ diễn ra trong thời gian giới hạn. Nhanh tay kẻo lỡ!</p>
    </div>

    <div class="sale-container">
      <div class="sale-nav prev" id="salePrev">
        <i class="fas fa-chevron-left"></i>
      </div>

      <div class="sale-products" id="saleProducts">
        <!-- Sản phẩm 1 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-45%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Giày Chạy Bộ Adidas Ultraboost</div>
            <div class="sale-price">
              <span class="discount-price">1.299.000đ</span>
              <span class="original-price">2.359.000đ</span>
              <span class="discount-percent">-45%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 75%"></div>
            </div>
            <div class="sold-text">Đã bán 75/100 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 2 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1556821840-3a63f95609a7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-60%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Tai Nghe Bluetooth Sony WH-1000XM4</div>
            <div class="sale-price">
              <span class="discount-price">4.999.000đ</span>
              <span class="original-price">7.499.000đ</span>
              <span class="discount-percent">-60%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 90%"></div>
            </div>
            <div class="sold-text">Đã bán 45/50 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 3 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-55%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Smartwatch Samsung Galaxy Watch5</div>
            <div class="sale-price">
              <span class="discount-price">3.599.000đ</span>
              <span class="original-price">7.999.000đ</span>
              <span class="discount-percent">-55%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 60%"></div>
            </div>
            <div class="sold-text">Đã bán 30/50 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 4 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-40%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Loa Bluetooth JBL Charge 5</div>
            <div class="sale-price">
              <span class="discount-price">2.399.000đ</span>
              <span class="original-price">3.999.000đ</span>
              <span class="discount-percent">-40%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 80%"></div>
            </div>
            <div class="sold-text">Đã bán 32/40 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 5 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-50%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Máy Ảnh Canon EOS R50</div>
            <div class="sale-price">
              <span class="discount-price">12.999.000đ</span>
              <span class="original-price">25.999.000đ</span>
              <span class="discount-percent">-50%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 45%"></div>
            </div>
            <div class="sold-text">Đã bán 9/20 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 6 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1605236453806-6ff36851218e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-35%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Bàn Phím Cơ Logitech G Pro</div>
            <div class="sale-price">
              <span class="discount-price">1.799.000đ</span>
              <span class="original-price">2.769.000đ</span>
              <span class="discount-percent">-35%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 70%"></div>
            </div>
            <div class="sold-text">Đã bán 21/30 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 7 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-55%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Máy Tính Bảng iPad Air 5</div>
            <div class="sale-price">
              <span class="discount-price">14.999.000đ</span>
              <span class="original-price">33.299.000đ</span>
              <span class="discount-percent">-55%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 85%"></div>
            </div>
            <div class="sold-text">Đã bán 17/20 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>

        <!-- Sản phẩm 8 -->
        <div class="sale-product">
          <div class="product-img">
            <img src="https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Sale Product">
            <span class="sale-badge">-30%</span>
          </div>
          <div class="product-info">
            <div class="product-name">Chuột Gaming Razer Viper</div>
            <div class="sale-price">
              <span class="discount-price">899.000đ</span>
              <span class="original-price">1.284.000đ</span>
              <span class="discount-percent">-30%</span>
            </div>
            <div class="progress-bar">
              <div class="progress" style="width: 65%"></div>
            </div>
            <div class="sold-text">Đã bán 26/40 sản phẩm</div>
            <button class="add-to-cart">
              <i class="fas fa-bolt"></i>
              Mua Ngay
            </button>
          </div>
        </div>
      </div>

      <div class="sale-nav next" id="saleNext">
        <i class="fas fa-chevron-right"></i>
      </div>
    </div>

    <div class="sale-dots" id="saleDots">
      <!-- Dots sẽ được tạo bằng JavaScript -->
    </div>
  </div>
</section>

<!-- Newest Products -->
<section class="products-container">
  <div class="container">
    <div class="section-title">
      <h2>Sản Phẩm Mới Nhất</h2>
    </div>
    <div class="products-scroll">
      <div class="products-track" id="newProductsTrack">
        <!-- Sản phẩm sẽ được thêm bằng JavaScript -->
      </div>
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
        <img src="https://images.unsplash.com/photo-1595341888016-a392ef81b7de?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Best Seller">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Bán Chạy Nhất</span>
        <h3>Áo Thun Premium Cotton</h3>
        <p>Chất liệu cotton cao cấp, thoáng mát và thấm hút mồ hôi tốt. Thiết kế đơn giản nhưng không kém phần thời trang, phù hợp với mọi hoàn cảnh.</p>
        <p>Đã bán: 1,250+ sản phẩm</p>
        <a href="#" class="btn">Mua Ngay</a>
      </div>
    </div>

    <!-- Newest -->
    <div class="featured-item reverse">
      <div class="featured-img">
        <img src="https://images.unsplash.com/photo-1505022610480-5e0b09ae7c40?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1970&q=80" alt="Newest">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Mới Nhất</span>
        <h3>Giày Thể Thao Ultra Boost</h3>
        <p>Công nghệ đệm khí tiên tiến, mang lại cảm giác êm ái và hỗ trợ tối đa cho đôi chân của bạn. Thiết kế trẻ trung, năng động.</p>
        <p>Phiên bản giới hạn 2023</p>
        <a href="#" class="btn">Mua Ngay</a>
      </div>
    </div>

    <!-- Rare -->
    <div class="featured-item">
      <div class="featured-img">
        <img src="https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1987&q=80" alt="Rare">
      </div>
      <div class="featured-content">
        <span class="featured-badge">Độc Quyền</span>
        <h3>Túi Xách Da Thật Handmade</h3>
        <p>Được làm thủ công từ da bò thật, mỗi sản phẩm là một tác phẩm nghệ thuật độc nhất. Chỉ sản xuất 50 chiếc trên toàn thế giới.</p>
        <p>Số lượng cực kỳ hạn chế</p>
        <a href="#" class="btn">Đặt Trước</a>
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
            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Customer Avatar">
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
          "Tôi rất hài lòng với chất lượng sản phẩm. Áo thun mặc rất thoải mái, chất liệu cotton mềm mại. Dịch vụ giao hàng nhanh chóng, nhân viên tư vấn nhiệt tình. Chắc chắn sẽ ủng hộ shop dài dài!"
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="review-avatar">
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Customer Avatar">
          </div>
          <div class="review-info">
            <h4>Trần Văn Hùng</h4>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
            </div>
          </div>
        </div>
        <div class="review-content">
          "Giày thể thao mua tại shop đẹp và chất lượng hơn cả mong đợi. Đế giày êm ái, ôm chân, đi cả ngày không thấy mỏi. Size chuẩn với description, giao hàng đúng hẹn. Rất đáng để mua!"
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="review-avatar">
            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Customer Avatar">
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
          "Túi xách da thật đẹp xuất sắc! Chất da mềm, đường may tỉ mỉ, khóa kéo trơn tru. Màu sắc đúng như hình, thiết kế thời trang và rất tiện dụng. Đóng gói cẩn thận, sản phẩm xứng đáng với giá tiền."
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
          <img src="https://images.unsplash.com/photo-1518709268805-4e9042af2176?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Tech News">
        </div>
        <div class="news-content">
          <span class="news-category">Tin Mới</span>
          <h3 class="news-title">iPhone 15 Series Chính Thức Ra Mắt Với Tính Năng Đột Phá</h3>
          <p class="news-excerpt">Apple vừa chính thức trình làng dòng iPhone 15 với camera 48MP, chip A17 Pro và cổng USB-C đầu tiên...</p>
          <div class="news-meta">
            <span><i class="far fa-clock"></i> 2 giờ trước</span>
            <span><i class="far fa-eye"></i> 1.2K lượt xem</span>
          </div>
        </div>
      </div>
      <div class="news-card">
        <div class="news-image">
          <img src="https://images.unsplash.com/photo-1542751110-97427bbecf20?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Guide">
        </div>
        <div class="news-content">
          <span class="news-category">Hướng Dẫn</span>
          <h3 class="news-title">Hướng Dẫn Build PC Gaming Tối Ưu Với Ngân Sách 20 Triệu</h3>
          <p class="news-excerpt">Tự lắp ráp PC gaming không khó với hướng dẫn chi tiết này. Tối ưu hiệu năng chơi game với mức ngân sách hợp lý...</p>
          <div class="news-meta">
            <span><i class="far fa-clock"></i> 1 ngày trước</span>
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
