<?php
session_start();

require_once 'class/product.php';
require_once 'class/product_image.php';
require_once 'class/category.php';
require_once 'class/brand.php';

$productModel = new Product();
$productImgModel = new ProductImage();
$categoryModel = new Category();
$brandModel = new Brand();

$categoryList = $categoryModel->getHierarchical();
$brandList = $brandModel->getAll(true);

$productsArr = $productModel->getAll(true);

$productsData = [];
foreach ($productsArr as $product) {
  // Lấy hình ảnh chính
  $mainImage = $productImgModel->getMainImage($product['id']);
  $imageUrl = $mainImage ? 'img/adminUP/products/' . $mainImage['image_url'] :
    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';

  // Lấy danh mục
  $category_if = $categoryModel->findById($product['category_id']);
  $categoryName = !empty($category_if['name']) ? $category_if['name'] : 'Không có danh mục';

  $brand_if = null;
  $brandName = 'Không xác định';
  if (!empty($product['brand_id'])) {
    $brand_if = $brandModel->findById($product['brand_id']);
    $brandName = !empty($brand_if['name']) ? $brand_if['name'] : $brandName;
  }

  // Giá: giữ cả numeric và formatted
  $currentPriceNumeric = !empty($product['sale_price']) ? (float)$product['sale_price'] : (float)$product['regular_price'];
  $originalPriceNumeric = (!empty($product['sale_price']) && $product['sale_price'] < $product['regular_price']) ?
    (float)$product['regular_price'] : null;

  $currentPriceFormatted = number_format($currentPriceNumeric, 0, ',', '.') . 'đ';
  $originalPriceFormatted = $originalPriceNumeric ? number_format($originalPriceNumeric, 0, ',', '.') . 'đ' : null;

  // Stock status (vẫn lưu để hiển thị tag nhưng không làm filter theo yêu cầu)
  $stock_status = $product['stock_status'] ?? '';

  $productsData[] = [
    'id' => (int)$product['id'],
    'name' => $product['name_pr'],
    'category_id' => (int)$product['category_id'],
    'category' => $categoryName,
    'brand_id' => isset($product['brand_id']) ? (int)$product['brand_id'] : null,
    'brand' => $brandName,
    'current_price' => $currentPriceFormatted,
    'current_price_numeric' => $currentPriceNumeric,
    'original_price' => $originalPriceFormatted,
    'original_price_numeric' => $originalPriceNumeric,
    'image' => $imageUrl,
    'slug' => $product['slug'] ?? '',
    'stock_status' => $stock_status,
    'created_at' => $product['created_at'] ?? null,
    'num_buy' => isset($product['num_buy']) ? (int)$product['num_buy'] : 0,
    'rating' => $product['rate'] ? (int)$product['rate'] : 0
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
  <link rel="stylesheet" href="css/products.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

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
              <?php foreach ($categoryList as $parent): ?>
                <div class="category-item">
                  <label class="filter-option parent-option">
                    <input type="checkbox" name="category[]" value="<?= $parent['id'] ?>">
                    <span class="checkmark"></span>
                    <?= htmlspecialchars($parent['name']) ?>
                    <?php if (!empty($parent['children'])): ?>
                      <i class="fas fa-chevron-down toggle-children"></i>
                    <?php endif; ?>
                  </label>

                  <?php if (!empty($parent['children'])): ?>
                    <div class="children" style="display:none; margin-left:18px;">
                      <?php foreach ($parent['children'] as $child): ?>
                        <label class="filter-option">
                          <input type="checkbox" name="category[]" value="<?= $child['id'] ?>">
                          <span class="checkmark"></span>
                          <?= htmlspecialchars($child['name']) ?>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

           Giá
          <div class="filter-group">
            <h4>Mức giá</h4>
            <div class="filter-options">
              <label class="filter-option">
                <input type="radio" name="price" value="all" checked>
                <span class="checkmark"></span>
                Tất cả
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="0-5000000">
                <span class="checkmark"></span>
                Dưới 5 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="5000000-10000000">
                <span class="checkmark"></span>
                5 - 10 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="10000000-20000000">
                <span class="checkmark"></span>
                10 - 20 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="20000000-50000000">
                <span class="checkmark"></span>
                20 - 50 triệu
              </label>
              <label class="filter-option">
                <input type="radio" name="price" value="50000000+">
                <span class="checkmark"></span>
                Trên 50 triệu
              </label>
            </div>
          </div>

          <!-- Thương hiệu -->
          <div class="filter-group">
            <h4>Thương hiệu</h4>
            <div class="filter-options">
<!--              <label class="filter-option">-->
<!--                <input type="checkbox" name="brand[]" value="all" checked>-->
<!--                <span class="checkmark"></span>-->
<!--                Tất cả-->
<!--              </label>-->
              <?php foreach ($brandList as $brand): ?>
                <label class="filter-option">
                  <input type="checkbox" name="brand[]" value="<?= $brand['id'] ?>">
                  <span class="checkmark"></span>
                  <?= htmlspecialchars($brand['name']) ?>
                </label>
              <?php endforeach; ?>
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
<!--            <div class="results-count">-->
<!--              Hiển thị <span id="">--><?php //echo count($productsData)?><!--</span> sản phẩm-->
<!--            </div>-->
          </div>

<!--          <div class="toolbar-center">-->
<!--            <div class="search-box">-->
<!--              <i class="fas fa-search search-icon"></i>-->
<!--              <input-->
<!--                type="text"-->
<!--                class="search-product"-->
<!--                id="searchProduct"-->
<!--                placeholder="Tìm kiếm sản phẩm..."-->
<!--              >-->
<!--              <button class="clear-search" id="clearSearch">-->
<!--                <i class="fas fa-times"></i>-->
<!--              </button>-->
<!--            </div>-->
<!--          </div>-->

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
          <button class="see-more-btn" id="seeMoreBtn">
            <i class="fa-solid fa-arrow-down"></i>
            Xem thêm
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
<?php include 'header.php'?>
<?php include 'cornerButton.php'?>
<?php include 'footer.php'?>

<script>
  /*
    Client-side filtering & sorting:
    - allProducts: mảng sản phẩm (từ server, JSON-encoded)
    - renderProducts(): render vào #productsGrid
    - applyFilters(): filter + sort + paginate
  */

  // Lấy dữ liệu sản phẩm từ PHP (an toàn vì dữ liệu đã load 1 lần)
  const allProducts = <?php echo json_encode($productsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

  let state = {
    filtered: [...allProducts],
    sortBy: 'default',
    page: 1,
    perPage: 12,
    visibleCount: 12
  };

  document.getElementById('seeMoreBtn').addEventListener('click', () => {
    state.visibleCount += state.perPage;
    applyFilters(); // ⚠️ gọi lại applyFilters, KHÔNG gọi renderProducts
  });

  function updateSeeMoreBtn() {
    const btn = document.getElementById('seeMoreBtn');

    if (state.visibleCount >= state.filtered.length) {
      btn.style.display = 'none';
    } else {
      btn.style.display = 'block';
    }
  }


  // Helpers
  function formatCurrency(num) {
    // num is numeric
    return new Intl.NumberFormat('vi-VN').format(num) + 'đ';
  }

  function renderProductCard(p) {
    // create product card HTML (escape where needed)
    const origPriceHTML = p.original_price ? `<span class="original-price">${p.original_price}</span>` : '';
    return `
  <div class="product-card" data-id="${p.id}" data-price="${p.current_price_numeric}">
    <div class="product-tags">
      <p class="product-tag tag-${p.stock_status}">${p.stock_status}</p>
    </div>
    <div class="product-actions">
      <button class="action-btn wishlist-btn" data-id="${p.id}"><i class="far fa-heart"></i></button>
      <button class="action-btn compare-btn" data-id="${p.id}"><i class="fas fa-chart-bar"></i></button>
    </div>
    <div class="product-img">
      <img src="${p.image}" alt="${(p.name || '').replace(/"/g, '&quot;')}">
    </div>
    <div class="product-info">
      <div class="product-category">${(p.category || '')} - ${p.brand}</div>
      <h3 class="product-name">${(p.name || '')}</h3>
      <div class="product-price">
        <span class="current-price">${p.current_price}</span>
        ${origPriceHTML}
      </div>
      <div class="product-rating">
        <div class="stars" id="productRating">${renderStars(p.rating)}</div>
        <span class="rating-count">(${p.num_buy})</span>
      </div>

      <div class="product-btn-inPro">
        <a href="product_detail.php?id=${p.id}" class="add-to-cart">
          <i class="fas fa-eye"></i> Xem thêm
        </a>
        <button class="compare_btn add-to-cart"
                  data-id="${p.id}"
                  data-category="${p.category_id}">
            <i class="fa-solid fa-code-compare"></i> So sánh
          </button>
      </div>

    </div>
  </div>
  `;
  }

  function renderProducts(products) {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    if (!products.length) {
      grid.innerHTML = '<p>Không có sản phẩm phù hợp.</p>';
      return;
    }
    grid.innerHTML = products.map(renderProductCard).join('');
  }

  // Pagination render
  function renderPagination(total, page, perPage) {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    let html = '';

    html += `<button class="page-btn prev" ${page<=1 ? 'disabled' : ''} data-page="${page-1}"><i class="fas fa-chevron-left"></i></button>`;
    // show up to 5 pages with current in middle
    const range = 2;
    const start = Math.max(1, page - range);
    const end = Math.min(totalPages, page + range);
    for (let i = start; i <= end; i++) {
      html += `<button class="page-btn ${i===page ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
    html += `<button class="page-btn next" ${page>=totalPages ? 'disabled' : ''} data-page="${page+1}"><i class="fas fa-chevron-right"></i></button>`;

    pagination.innerHTML = html;

    // bind events
    pagination.querySelectorAll('button.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const next = parseInt(btn.getAttribute('data-page'));
        if (!isNaN(next)) {
          state.page = next;
          applyFilters();
          window.scrollTo({top: 200, behavior: 'smooth'});
        }
      });
    });
  }

  function renderStars(rating) {
    rating = Math.max(0, Math.min(5, rating));

    let html = '';

    for (let i = 1; i <= 5; i++) {
      if (rating >= i) {
        html += '<i class="fa-solid fa-star"></i>';
      } else if (rating >= i - 0.5) {
        html += '<i class="fa-solid fa-star-half-stroke"></i>';
      } else {
        html += '<i class="fa-regular fa-star"></i>';
      }
    }

    return html;
  }


  // Read filters from UI
  function getSelectedCategories() {
    return Array.from(document.querySelectorAll('input[name="category[]"]:checked')).map(i => parseInt(i.value));
  }
  function getSelectedBrands() {
    // treat 'all' specially
    const checked = Array.from(document.querySelectorAll('input[name="brand[]"]:checked')).map(i => i.value);
    if (checked.includes('all')) return []; // empty -> means all brands
    return checked.map(v => parseInt(v));
  }
  function getSelectedPriceRange() {
    const v = document.querySelector('input[name="price"]:checked').value;
    if (v === 'all') return null;
    if (v.endsWith('+')) {
      const min = parseInt(v.replace('+',''));
      return {min, max: Infinity};
    }
    const parts = v.split('-');
    return {min: parseInt(parts[0]), max: parseInt(parts[1])};
  }

  // Apply filters + sort + paginate
  function applyFilters() {
    const cats = getSelectedCategories().filter(n => !isNaN(n));
    const brands = getSelectedBrands().filter(n => !isNaN(n));
    const priceRange = getSelectedPriceRange();
    const sortBy = document.getElementById('sortBy').value;
    state.sortBy = sortBy;

    let filtered = allProducts.filter(p => {
      // category: if none selected -> pass all
      if (cats.length) {
        if (!cats.includes(p.category_id)) return false;
      }

      // brand
      if (brands.length) {
        if (!brands.includes(p.brand_id)) return false;
      }

      // price
      if (priceRange) {
        const price = parseFloat(p.current_price_numeric);
        if (isNaN(price)) return false;
        if (price < priceRange.min) return false;
        if (priceRange.max !== Infinity && price > priceRange.max) return false;
      }
      return true;
    });

    // Sort
    if (sortBy === 'price-asc') {
      filtered.sort((a,b)=> a.current_price_numeric - b.current_price_numeric);
    } else if (sortBy === 'price-desc') {
      filtered.sort((a,b)=> b.current_price_numeric - a.current_price_numeric);
    } else if (sortBy === 'newest') {
      filtered.sort((a,b)=> {
        const da = a.created_at ? new Date(a.created_at).getTime() : 0;
        const db = b.created_at ? new Date(b.created_at).getTime() : 0;
        return db - da;
      });
    } else if (sortBy === 'popular') {
      filtered.sort((a,b)=> b.num_buy - a.num_buy);
    }

    state.filtered = filtered;

    // Pagination
    // const total = filtered.length;
    // const perPage = state.perPage;
    // const page = Math.min(state.page, Math.max(1, Math.ceil(total / perPage)));
    // state.page = page;
    // const start = (page - 1) * perPage;
    // const pageItems = filtered.slice(start, start + perPage);
    //
    // renderProducts(pageItems);
    // document.querySelector('.results-count span').textContent = total;
    // // renderPagination(total, page, perPage);

    const total = filtered.length;

// luôn đảm bảo visibleCount không vượt total
    state.visibleCount = Math.min(state.visibleCount, total);

// lấy từ đầu tới visibleCount
    const visibleItems = filtered.slice(0, state.visibleCount);

    renderProducts(visibleItems);
    updateSeeMoreBtn();
  }

  // Clear filters
  document.getElementById('clearFilters').addEventListener('click', () => {
    // uncheck all category and brand except 'all' for brand and price
    document.querySelectorAll('input[name="category[]"]').forEach(i => i.checked = false);
    document.querySelectorAll('input[name="brand[]"]').forEach(i => i.checked = i.value === 'all');
    document.querySelector('input[name="price"][value="all"]').checked = true;
    document.getElementById('sortBy').value = 'default';
    state.page = 1;
    state.visibleCount = state.perPage;

    applyFilters();
  });

  // Apply button
  document.getElementById('applyFilters').addEventListener('click', () => {
    state.page = 1;
    state.visibleCount = state.perPage;

    applyFilters();
  });

  // Sort change immediate
  document.getElementById('sortBy').addEventListener('change', () => {
    state.page = 1;
    state.visibleCount = state.perPage;

    applyFilters();
  });

  // Toggle children for category
  document.querySelectorAll(".toggle-children").forEach(btn => {
    btn.addEventListener("click", function () {
      const children = this.parentElement.nextElementSibling;
      if (children.style.display === "none") {
        children.style.display = "block";
        this.classList.add("open");
      } else {
        children.style.display = "none";
        this.classList.remove("open");
      }
    });
  });

  // View mode buttons (grid/list)
  document.querySelectorAll('.view-btn').forEach(el=>{
    el.addEventListener('click', ()=>{
      document.querySelectorAll('.view-btn').forEach(b=>b.classList.remove('active'));
      el.classList.add('active');
      const view = el.getAttribute('data-view');
      const grid = document.getElementById('productsGrid');
      if (view === 'list') grid.classList.add('list-view'); else grid.classList.remove('list-view');
    });
  });

  // Init
  document.addEventListener('DOMContentLoaded', ()=> {
    // default: render first page of allProducts
    state.filtered = [...allProducts];
    applyFilters();
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const categoryId = params.get('category');

    if (!categoryId) return;

    // Tìm checkbox category tương ứng
    const checkbox = document.querySelector(
      `input[name="category[]"][value="${categoryId}"]`
    );

    if (checkbox) {
      checkbox.checked = true;

      // Nếu là category con → mở category cha
      const childrenBox = checkbox.closest('.children');
      if (childrenBox) {
        childrenBox.style.display = 'block';
      }

      // Nếu có filter AJAX → trigger change
      checkbox.dispatchEvent(new Event('change'));
    }
  });

  document.querySelectorAll('input[name="category[]"]').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const brandId = params.get('brand');

    if (!brandId) return;

    // Tìm checkbox category tương ứng
    const checkbox = document.querySelector(
      `input[name="brand[]"][value="${brandId}"]`
    );

    if (checkbox) {
      checkbox.checked = true;

      // Nếu có filter AJAX → trigger change
      checkbox.dispatchEvent(new Event('change'));
    }
  });

  document.querySelectorAll('input[name="brand[]"]').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });
</script>

<script>
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.compare_btn');
    if (!btn) return;

    const productId = parseInt(btn.dataset.id);
    const categoryId = parseInt(btn.dataset.category);

    console.log(productId, categoryId);

    fetch('/apiPrivate/compare_save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ productId, categoryId })
    })
      .then(res => res.json())
      .then(res => {
        if (!res.success) {
          alert(res.message);

          openPopup('compare');
          loadCompareSession();
          return;
        }

        // tuỳ bạn: mở popup, quay lại, hay toast

      });
  });

</script>

</body>
</html>
