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
  <link rel="stylesheet" href="css/products.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>

<body>

<!-- products.php -->
<div class="products-page">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <div class="container">
      <nav>
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="products.php" class="active">Sản phẩm</a>
      </nav>
    </div>
  </div>

  <div class="container">
    <div class="products-layout">
      <!-- Sidebar Filter -->
      <aside class="filter-sidebar">
        <div class="filter-header">
          <h3><i class="fas fa-filter"></i> Bộ Lọc</h3>
          <button class="clear-filters" id="clearFilters">
            <i class="fas fa-times"></i> Xóa hết
          </button>
        </div>

        <div class="filter-content">
          <!-- Danh mục -->
          <div class="filter-group">
            <h4>Danh mục</h4>
            <div class="filter-options">
              <label class="filter-option">
                <input type="checkbox" name="category" value="laptop">
                <span class="checkmark"></span>
                Laptop & Máy tính
              </label>
              <label class="filter-option">
                <input type="checkbox" name="category" value="smartphone">
                <span class="checkmark"></span>
                Điện thoại
              </label>
              <label class="filter-option">
                <input type="checkbox" name="category" value="tablet">
                <span class="checkmark"></span>
                Máy tính bảng
              </label>
              <label class="filter-option">
                <input type="checkbox" name="category" value="audio">
                <span class="checkmark"></span>
                Tai nghe & Loa
              </label>
              <label class="filter-option">
                <input type="checkbox" name="category" value="accessory">
                <span class="checkmark"></span>
                Phụ kiện
              </label>
            </div>
          </div>

          <!-- Giá -->
          <div class="filter-group">
            <h4>Mức giá</h4>
            <div class="filter-options">
              <label class="filter-option">
                <input type="radio" name="price" value="0-5">
                <span class="checkmark"></span>
                Dưới 5 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="5-10">
                <span class="checkmark"></span>
                5 - 10 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="10-20">
                <span class="checkmark"></span>
                10 - 20 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="20-50">
                <span class="checkmark"></span>
                20 - 50 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="50+">
                <span class="checkmark"></span>
                Trên 50 triệu
              </label>
            </div>
          </div>

          <!-- Thương hiệu -->
          <div class="filter-group">
            <h4>Thương hiệu</h4>
            <div class="filter-options">
              <label class="filter-option">
                <input type="checkbox" name="brand" value="apple">
                <span class="checkmark"></span>
                Apple
              </label>
              <label class="filter-option">
                <input type="checkbox" name="brand" value="samsung">
                <span class="checkmark"></span>
                Samsung
              </label>
              <label class="filter-option">
                <input type="checkbox" name="brand" value="sony">
                <span class="checkmark"></span>
                Sony
              </label>
              <label class="filter-option">
                <input type="checkbox" name="brand" value="asus">
                <span class="checkmark"></span>
                ASUS
              </label>
              <label class="filter-option">
                <input type="checkbox" name="brand" value="dell">
                <span class="checkmark"></span>
                Dell
              </label>
              <label class="filter-option">
                <input type="checkbox" name="brand" value="lenovo">
                <span class="checkmark"></span>
                Lenovo
              </label>
            </div>
          </div>

          <!-- Đánh giá -->
          <div class="filter-group">
            <h4>Đánh giá</h4>
            <div class="filter-options">
              <label class="filter-option rating-option">
                <input type="radio" name="rating" value="5">
                <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </span>
                <span class="rating-text">5 sao</span>
              </label>
              <label class="filter-option rating-option">
                <input type="radio" name="rating" value="4">
                <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                <span class="rating-text">4 sao trở lên</span>
              </label>
              <label class="filter-option rating-option">
                <input type="radio" name="rating" value="3">
                <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                <span class="rating-text">3 sao trở lên</span>
              </label>
            </div>
          </div>

          <!-- Tình trạng -->
          <div class="filter-group">
            <h4>Tình trạng</h4>
            <div class="filter-options">
              <label class="filter-option">
                <input type="checkbox" name="status" value="in-stock">
                <span class="checkmark"></span>
                Còn hàng
              </label>
              <label class="filter-option">
                <input type="checkbox" name="status" value="pre-order">
                <span class="checkmark"></span>
                Đặt trước
              </label>
              <label class="filter-option">
                <input type="checkbox" name="status" value="sale">
                <span class="checkmark"></span>
                Đang giảm giá
              </label>
            </div>
          </div>
        </div>


        <button class="apply-filters-btn" id="applyFilters">
          Áp dụng bộ lọc
        </button>
      </aside>

      <!-- Main Content -->
      <main class="products-main">
        <!-- Toolbar -->
        <div class="products-toolbar">
          <div class="toolbar-left">
            <div class="view-mode">
              <button class="view-btn active" data-view="grid">
                <i class="fas fa-th"></i>
              </button>
              <button class="view-btn" data-view="list">
                <i class="fas fa-list"></i>
              </button>
            </div>
            <div class="results-count">
              Hiển thị <span id="resultsCount">24</span> sản phẩm
            </div>
          </div>

          <div class="toolbar-right">
            <div class="sort-by">
              <label>Sắp xếp:</label>
              <select id="sortBy">
                <option value="default">Mặc định</option>
                <option value="price-asc">Giá: Thấp đến Cao</option>
                <option value="price-desc">Giá: Cao đến Thấp</option>
                <option value="newest">Mới nhất</option>
                <option value="popular">Bán chạy</option>
                <option value="rating">Đánh giá cao</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
          <!-- Sản phẩm sẽ được thêm bằng JavaScript -->
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button class="page-btn prev" disabled>
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn">4</button>
          <button class="page-btn next">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>

        <!-- Related Products -->
        <section class="related-products">
          <h3>Sản phẩm liên quan</h3>
          <div class="related-grid" id="relatedProducts">
            <!-- Sản phẩm liên quan sẽ được thêm bằng JavaScript -->
          </div>
        </section>
      </main>
    </div>
  </div>
</div>

<!-- Quick View Modal -->
<div class="modal quick-view-modal" id="quickViewModal">
  <div class="modal-content">
    <button class="modal-close" id="closeQuickView">
      <i class="fas fa-times"></i>
    </button>
    <div class="quick-view-content" id="quickViewContent">
      <!-- Nội dung quick view sẽ được thêm bằng JavaScript -->
    </div>
  </div>
</div>

<!-- Floating Filter Button (Mobile) -->
<button class="floating-filter-btn" id="floatingFilterBtn">
  <i class="fas fa-filter"></i>
  <span>Bộ lọc</span>
</button>

<?php include 'footer.php'?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu sản phẩm mẫu
    const products = [
      {
        id: 1,
        name: "iPhone 15 Pro Max 256GB",
        category: "smartphone",
        brand: "apple",
        price: 32990000,
        originalPrice: 35990000,
        image: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.8,
        reviews: 124,
        description: "iPhone 15 Pro Max với chip A17 Pro, camera 48MP và thiết kế titan cao cấp.",
        tags: ["new"],
        inStock: true,
        isSale: true
      },
      {
        id: 2,
        name: "MacBook Air M2 2023",
        category: "laptop",
        brand: "apple",
        price: 28990000,
        originalPrice: 30990000,
        image: "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.9,
        reviews: 89,
        description: "MacBook Air siêu mỏng nhẹ với chip M2, màn hình Liquid Retina 13.6 inch.",
        tags: ["new", "sale"],
        inStock: true,
        isSale: true
      },
      {
        id: 3,
        name: "Samsung Galaxy S24 Ultra",
        category: "smartphone",
        brand: "samsung",
        price: 24990000,
        originalPrice: 27990000,
        image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.7,
        reviews: 156,
        description: "Galaxy S24 Ultra với bút S-Pen, camera 200MP và chip Snapdragon 8 Gen 3.",
        tags: ["new"],
        inStock: true,
        isSale: false
      },
      {
        id: 4,
        name: "Sony WH-1000XM5",
        category: "audio",
        brand: "sony",
        price: 7990000,
        originalPrice: 8990000,
        image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.8,
        reviews: 203,
        description: "Tai nghe chống ồn tốt nhất thế giới với công nghệ AI Noise Canceling.",
        tags: ["sale"],
        inStock: true,
        isSale: true
      },
      {
        id: 5,
        name: "iPad Pro 12.9 M2",
        category: "tablet",
        brand: "apple",
        price: 27990000,
        originalPrice: 29990000,
        image: "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.9,
        reviews: 67,
        description: "iPad Pro mạnh mẽ với chip M2, màn hình Liquid Retina XDR 12.9 inch.",
        tags: ["limited"],
        inStock: false,
        isSale: false
      },
      {
        id: 6,
        name: "ASUS ROG Strix G16",
        category: "laptop",
        brand: "asus",
        price: 35990000,
        originalPrice: 38990000,
        image: "https://images.unsplash.com/photo-1603302576837-37561b2e2302?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.6,
        reviews: 45,
        description: "Laptop gaming cao cấp với RTX 4060, Intel Core i9 và màn hình 240Hz.",
        tags: ["new", "sale"],
        inStock: true,
        isSale: true
      },
      {
        id: 7,
        name: "Dell XPS 13 Plus",
        category: "laptop",
        brand: "dell",
        price: 31990000,
        originalPrice: 33990000,
        image: "https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.7,
        reviews: 78,
        description: "XPS 13 Plus thiết kế không viền, Intel Core i7 và bàn phím cảm ứng.",
        tags: ["new"],
        inStock: true,
        isSale: false
      },
      {
        id: 8,
        name: "Samsung Galaxy Tab S9",
        category: "tablet",
        brand: "samsung",
        price: 18990000,
        originalPrice: 20990000,
        image: "https://images.unsplash.com/photo-1561154464-82e9adf32764?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        rating: 4.5,
        reviews: 92,
        description: "Tablet Android mạnh mẽ với S-Pen, màn hình 120Hz và chip Snapdragon 8 Gen 2.",
        tags: ["sale"],
        inStock: true,
        isSale: true
      }
    ];

    // Hiển thị sản phẩm
    function displayProducts(productsToShow) {
      const productsGrid = document.getElementById('productsGrid');
      productsGrid.innerHTML = '';

      productsToShow.forEach(product => {
        const productCard = createProductCard(product);
        productsGrid.appendChild(productCard);
      });

      // Cập nhật số lượng kết quả
      document.getElementById('resultsCount').textContent = productsToShow.length;
    }

    // Tạo card sản phẩm
    function createProductCard(product) {
      const card = document.createElement('div');
      card.className = 'product-card';
      card.innerHTML = `
            <div class="product-tags">
                ${product.tags.map(tag => `
                    <span class="product-tag tag-${tag}">${tag === 'new' ? 'Mới' : tag === 'sale' ? 'Sale' : 'Giới hạn'}</span>
                `).join('')}
            </div>
            <div class="product-actions">
                <button class="action-btn wishlist-btn" data-id="${product.id}">
                    <i class="far fa-heart"></i>
                </button>
                <button class="action-btn compare-btn" data-id="${product.id}">
                    <i class="fas fa-chart-bar"></i>
                </button>
            </div>
            <div class="product-img">
                <img src="${product.image}" alt="${product.name}">
            </div>
            <div class="product-info">
                <div class="product-category">${getCategoryName(product.category)}</div>
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">
                    <span class="current-price">${formatPrice(product.price)}</span>
                    ${product.originalPrice > product.price ?
        `<span class="original-price">${formatPrice(product.originalPrice)}</span>` : ''
      }
                </div>
                <div class="product-rating">
                    <div class="stars">
                        ${generateStars(product.rating)}
                    </div>
                    <span class="rating-count">(${product.reviews})</span>
                </div>
                <button class="add-to-cart" data-id="${product.id}">
                    <i class="fas fa-shopping-cart"></i>
                    Thêm vào giỏ
                </button>
                <a href="product_detail.php" class="quick-view-btn"">
                    <i class="fas fa-eye"></i>
                    Xem thêm
                </a>
            </div>
        `;
      return card;
    }

    // Hàm hỗ trợ
    function getCategoryName(category) {
      const categories = {
        'laptop': 'Laptop & Máy tính',
        'smartphone': 'Điện thoại',
        'tablet': 'Máy tính bảng',
        'audio': 'Tai nghe & Loa',
        'accessory': 'Phụ kiện'
      };
      return categories[category] || category;
    }

    function formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price);
    }

    function generateStars(rating) {
      let stars = '';
      const fullStars = Math.floor(rating);
      const halfStar = rating % 1 >= 0.5;

      for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
      }

      if (halfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
      }

      const emptyStars = 5 - Math.ceil(rating);
      for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
      }

      return stars;
    }

    // View mode toggle
    const viewBtns = document.querySelectorAll('.view-btn');
    const productsGrid = document.getElementById('productsGrid');

    viewBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        viewBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const viewMode = this.dataset.view;
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
          card.classList.toggle('list-view', viewMode === 'list');
        });

        productsGrid.classList.toggle('list-view', viewMode === 'list');
      });
    });

    // Quick view modal
    const quickViewModal = document.getElementById('quickViewModal');
    const quickViewContent = document.getElementById('quickViewContent');
    const closeQuickView = document.getElementById('closeQuickView');

    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('quick-view-btn')) {
        const productId = parseInt(e.target.dataset.id);
        const product = products.find(p => p.id === productId);
        showQuickView(product);
      }
    });

    function showQuickView(product) {
      quickViewContent.innerHTML = `
            <div class="quick-view-grid">
                <div class="quick-view-image">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="quick-view-info">
                    <div class="product-tags">
                        ${product.tags.map(tag => `
                            <span class="product-tag tag-${tag}">${tag === 'new' ? 'Mới' : tag === 'sale' ? 'Sale' : 'Giới hạn'}</span>
                        `).join('')}
                    </div>
                    <h2>${product.name}</h2>
                    <div class="product-rating large">
                        <div class="stars">
                            ${generateStars(product.rating)}
                        </div>
                        <span class="rating-count">${product.reviews} đánh giá</span>
                    </div>
                    <div class="product-price large">
                        <span class="current-price">${formatPrice(product.price)}</span>
                        ${product.originalPrice > product.price ?
        `<span class="original-price">${formatPrice(product.originalPrice)}</span>` : ''
      }
                    </div>
                    <p class="product-description">${product.description}</p>
                    <div class="quick-view-actions">
                        <button class="add-to-cart large" data-id="${product.id}">
                            <i class="fas fa-shopping-cart"></i>
                            Thêm vào giỏ hàng
                        </button>
                        <button class="wishlist-btn large" data-id="${product.id}">
                            <i class="far fa-heart"></i>
                            Yêu thích
                        </button>
                    </div>
                    <div class="product-specs">
                        <h4>Thông số kỹ thuật</h4>
                        <ul>
                            <li><strong>Thương hiệu:</strong> ${product.brand.toUpperCase()}</li>
                            <li><strong>Danh mục:</strong> ${getCategoryName(product.category)}</li>
                            <li><strong>Tình trạng:</strong> ${product.inStock ? 'Còn hàng' : 'Hết hàng'}</li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
      quickViewModal.classList.add('show');
    }

    closeQuickView.addEventListener('click', function() {
      quickViewModal.classList.remove('show');
    });

    quickViewModal.addEventListener('click', function(e) {
      if (e.target === quickViewModal) {
        quickViewModal.classList.remove('show');
      }
    });

    // Floating filter button
    const floatingFilterBtn = document.getElementById('floatingFilterBtn');
    const filterSidebar = document.querySelector('.filter-sidebar');
    //
    // floatingFilterBtn.addEventListener('click', function() {
    //   filterSidebar.style.display = filterSidebar.style.display === 'block' ? 'none' : 'block';
    // });

    floatingFilterBtn.addEventListener('click', function() {
      filterSidebar.classList.toggle('show');
    });

// Đóng filter khi click bên ngoài trên mobile
    document.addEventListener('click', function(e) {
      if (window.innerWidth <= 768) {
        if (!filterSidebar.contains(e.target) && !floatingFilterBtn.contains(e.target)) {
          filterSidebar.classList.remove('show');
        }
      }
    });

    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
      const inputs = document.querySelectorAll('.filter-sidebar input');
      inputs.forEach(input => {
        input.checked = false;
      });
    });

    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
      // Logic lọc sản phẩm sẽ được thêm ở đây
      displayProducts(products); // Tạm thời hiển thị tất cả
    });

    // Sort products
    document.getElementById('sortBy').addEventListener('change', function(e) {
      const sortValue = e.target.value;
      let sortedProducts = [...products];

      switch(sortValue) {
        case 'price-asc':
          sortedProducts.sort((a, b) => a.price - b.price);
          break;
        case 'price-desc':
          sortedProducts.sort((a, b) => b.price - a.price);
          break;
        case 'newest':
          // Giả sử sản phẩm mới hơn có ID lớn hơn
          sortedProducts.sort((a, b) => b.id - a.id);
          break;
        case 'popular':
          sortedProducts.sort((a, b) => b.reviews - a.reviews);
          break;
        case 'rating':
          sortedProducts.sort((a, b) => b.rating - a.rating);
          break;
      }

      displayProducts(sortedProducts);
    });

    // Hiển thị sản phẩm ban đầu
    displayProducts(products);

    // Hiển thị sản phẩm liên quan
    const relatedProducts = document.getElementById('relatedProducts');
    const related = products.slice(0, 4); // Lấy 4 sản phẩm đầu làm liên quan

    related.forEach(product => {
      const relatedCard = createProductCard(product);
      relatedCard.classList.add('related-card');
      relatedProducts.appendChild(relatedCard);
    });
  });
</script>

</body>
</html>
