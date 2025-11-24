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
  <link rel="stylesheet" href="css/cart.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>

<body>

<!-- cart.php -->
<div class="cart-page">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <div class="container">
      <nav>
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="cart.php" class="active">Giỏ hàng</a>
      </nav>
    </div>
  </div>

  <div class="container">
    <div class="cart-layout">
      <!-- Cart Items -->
      <div class="cart-items-section">
        <div class="section-header">
          <h2>Giỏ Hàng Của Bạn</h2>
          <span class="items-count" id="itemsCount">3 sản phẩm</span>
        </div>

        <div class="cart-items" id="cartItems">
          <!-- Cart items will be populated by JavaScript -->
        </div>

        <!-- Continue Shopping -->
        <div class="continue-shopping">
          <a href="products.php" class="continue-btn">
            <i class="fas fa-arrow-left"></i>
            Tiếp tục mua sắm
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary-section">
        <div class="summary-card">
          <h3>Tóm tắt đơn hàng</h3>

          <div class="summary-details">
            <div class="summary-row">
              <span>Tạm tính</span>
              <span id="subtotal">0₫</span>
            </div>
            <div class="summary-row">
              <span>Phí vận chuyển</span>
              <span id="shippingFee">0₫</span>
            </div>
            <div class="summary-row">
              <span>Giảm giá</span>
              <span class="discount" id="discountAmount">-0₫</span>
            </div>
            <div class="summary-row total">
              <span>Tổng cộng</span>
              <span id="totalAmount">0₫</span>
            </div>
          </div>

          <!-- Coupon Code -->
          <div class="coupon-section">
            <div class="coupon-header">
              <h4>Mã giảm giá</h4>
              <button class="coupon-info" id="couponInfo">
                <i class="fas fa-info-circle"></i>
              </button>
            </div>
            <div class="coupon-input-group">
              <input type="text" id="couponCode" placeholder="Nhập mã giảm giá">
              <button id="applyCoupon">Áp dụng</button>
            </div>
            <div class="available-coupons">
              <p>Mã có sẵn:</p>
              <div class="coupon-tags">
                <span class="coupon-tag" data-code="WELCOME10" data-discount="10">WELCOME10 - Giảm 10%</span>
                <span class="coupon-tag" data-code="FREESHIP" data-discount="ship">FREESHIP - Miễn phí ship</span>
                <span class="coupon-tag" data-code="SAVE50K" data-discount="50000">SAVE50K - Giảm 50K</span>
              </div>
            </div>
          </div>

          <!-- Checkout Button -->
          <button class="checkout-btn" id="checkoutBtn">
            <i class="fas fa-lock"></i>
            Tiến hành thanh toán
          </button>

          <!-- Security Badges -->
          <div class="security-badges">
            <div class="badge">
              <i class="fas fa-shield-alt"></i>
              <span>Bảo mật thanh toán</span>
            </div>
            <div class="badge">
              <i class="fas fa-truck"></i>
              <span>Giao hàng miễn phí</span>
            </div>
            <div class="badge">
              <i class="fas fa-undo"></i>
              <span>Đổi trả 30 ngày</span>
            </div>
          </div>
        </div>

        <!-- Recommended Products -->
        <div class="recommended-products">
          <h4>Sản phẩm thường được mua kèm</h4>
          <div class="recommended-list">
            <div class="recommended-item">
              <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Tai nghe">
              <div class="item-info">
                <p>Tai nghe Bluetooth</p>
                <span class="price">799.000₫</span>
              </div>
              <button class="add-btn">+</button>
            </div>
            <div class="recommended-item">
              <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Ốp lưng">
              <div class="item-info">
                <p>Ốp lưng chính hãng</p>
                <span class="price">299.000₫</span>
              </div>
              <button class="add-btn">+</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Empty Cart State -->
<div class="empty-cart" id="emptyCart" style="display: none;">
  <div class="container">
    <div class="empty-cart-content">
      <div class="empty-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <h2>Giỏ hàng của bạn đang trống</h2>
      <p>Hãy khám phá các sản phẩm và thêm vào giỏ hàng nhé!</p>
      <a href="products.php" class="shopping-btn">
        <i class="fas fa-shopping-bag"></i>
        Mua sắm ngay
      </a>
    </div>
  </div>
</div>

<!-- Coupon Info Modal -->
<div class="modal" id="couponModal">
  <div class="modal-content">
    <button class="modal-close" id="closeCouponModal">
      <i class="fas fa-times"></i>
    </button>
    <h3>Mã giảm giá có sẵn</h3>
    <div class="coupon-list">
      <div class="coupon-item">
        <div class="coupon-code">WELCOME10</div>
        <div class="coupon-details">
          <strong>Giảm 10% cho đơn hàng đầu tiên</strong>
          <p>Áp dụng cho tất cả sản phẩm, tối đa 500.000₫</p>
        </div>
      </div>
      <div class="coupon-item">
        <div class="coupon-code">FREESHIP</div>
        <div class="coupon-details">
          <strong>Miễn phí vận chuyển</strong>
          <p>Miễn phí ship toàn quốc cho đơn từ 500.000₫</p>
        </div>
      </div>
      <div class="coupon-item">
        <div class="coupon-code">SAVE50K</div>
        <div class="coupon-details">
          <strong>Giảm ngay 50.000₫</strong>
          <p>Áp dụng cho đơn hàng từ 1.000.000₫</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Sample cart data
    let cartItems = [
      {
        id: 1,
        name: "iPhone 15 Pro Max 256GB",
        category: "smartphone",
        image: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 32990000,
        originalPrice: 35990000,
        quantity: 1
      },
      {
        id: 2,
        name: "MacBook Air M2 2023",
        category: "laptop",
        image: "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 28990000,
        originalPrice: 30990000,
        quantity: 1
      },
      {
        id: 3,
        name: "Tai nghe Sony WH-1000XM5",
        category: "audio",
        image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
        price: 7990000,
        originalPrice: 8990000,
        quantity: 2
      }
    ];

    let appliedCoupon = null;
    const SHIPPING_FEE = 30000;

    // Initialize cart
    function initCart() {
      renderCartItems();
      updateCartSummary();
      toggleEmptyState();
    }

    // Render cart items
    function renderCartItems() {
      const cartItemsContainer = document.getElementById('cartItems');
      cartItemsContainer.innerHTML = '';

      cartItems.forEach(item => {
        const itemElement = createCartItemElement(item);
        cartItemsContainer.appendChild(itemElement);
      });

      document.getElementById('itemsCount').textContent = `${getTotalItems()} sản phẩm`;
    }

    // Create cart item element
    function createCartItemElement(item) {
      const div = document.createElement('div');
      div.className = 'cart-item';
      div.innerHTML = `
            <div class="item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="item-details">
                <div class="item-category">${getCategoryName(item.category)}</div>
                <h3 class="item-name">${item.name}</h3>
                <div class="item-price">
                    <span class="current-price">${formatPrice(item.price)}</span>
                    ${item.originalPrice > item.price ?
        `<span class="original-price">${formatPrice(item.originalPrice)}</span>` : ''
      }
                </div>
                <div class="item-actions">
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" data-id="${item.id}">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-id="${item.id}">
                        <button class="quantity-btn plus" data-id="${item.id}">+</button>
                    </div>
                    <button class="remove-btn" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                        Xóa
                    </button>
                </div>
            </div>
            <div class="item-total">
                ${formatPrice(item.price * item.quantity)}
            </div>
        `;
      return div;
    }

    // Update cart summary
    function updateCartSummary() {
      const subtotal = getSubtotal();
      const discount = getDiscountAmount(subtotal);
      const shipping = appliedCoupon?.type === 'ship' ? 0 : SHIPPING_FEE;
      const total = subtotal - discount + shipping;

      document.getElementById('subtotal').textContent = formatPrice(subtotal);
      document.getElementById('shippingFee').textContent = formatPrice(shipping);
      document.getElementById('discountAmount').textContent = `-${formatPrice(discount)}`;
      document.getElementById('totalAmount').textContent = formatPrice(total);

      // Update checkout button
      const checkoutBtn = document.getElementById('checkoutBtn');
      checkoutBtn.disabled = cartItems.length === 0;
    }

    // Get category name
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

    // Format price
    function formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price);
    }

    // Get subtotal
    function getSubtotal() {
      return cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Get total items
    function getTotalItems() {
      return cartItems.reduce((total, item) => total + item.quantity, 0);
    }

    // Get discount amount
    function getDiscountAmount(subtotal) {
      if (!appliedCoupon) return 0;

      switch (appliedCoupon.type) {
        case 'percentage':
          const maxDiscount = appliedCoupon.maxDiscount || Infinity;
          return Math.min(subtotal * appliedCoupon.value / 100, maxDiscount);
        case 'fixed':
          return appliedCoupon.value;
        case 'ship':
          return 0; // Shipping is handled separately
        default:
          return 0;
      }
    }

    // Toggle empty state
    function toggleEmptyState() {
      const emptyCart = document.getElementById('emptyCart');
      const cartLayout = document.querySelector('.cart-layout');

      if (cartItems.length === 0) {
        emptyCart.style.display = 'block';
        cartLayout.style.display = 'none';
      } else {
        emptyCart.style.display = 'none';
        cartLayout.style.display = 'grid';
      }
    }

    // Event listeners for quantity controls
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('quantity-btn')) {
        const itemId = parseInt(e.target.dataset.id);
        const item = cartItems.find(item => item.id === itemId);

        if (e.target.classList.contains('plus')) {
          item.quantity++;
        } else if (e.target.classList.contains('minus') && item.quantity > 1) {
          item.quantity--;
        }

        updateItem(itemId);
      }

      if (e.target.classList.contains('remove-btn')) {
        const itemId = parseInt(e.target.dataset.id);
        removeItem(itemId);
      }

      if (e.target.classList.contains('coupon-tag')) {
        const code = e.target.dataset.code;
        const discount = e.target.dataset.discount;
        applyCoupon(code, discount);
      }

      if (e.target.classList.contains('add-btn')) {
        alert('Sản phẩm đã được thêm vào giỏ hàng!');
      }
    });

    // Event listeners for quantity input
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('quantity-input')) {
        const itemId = parseInt(e.target.dataset.id);
        const quantity = parseInt(e.target.value);

        if (quantity > 0) {
          updateItemQuantity(itemId, quantity);
        } else {
          e.target.value = 1;
        }
      }
    });

    // Update item
    function updateItem(itemId) {
      renderCartItems();
      updateCartSummary();
    }

    // Update item quantity
    function updateItemQuantity(itemId, quantity) {
      const item = cartItems.find(item => item.id === itemId);
      if (item) {
        item.quantity = quantity;
        updateItem(itemId);
      }
    }

    // Remove item
    function removeItem(itemId) {
      if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        cartItems = cartItems.filter(item => item.id !== itemId);
        initCart();
      }
    }

    // Apply coupon
    function applyCoupon(code, discountValue) {
      document.getElementById('couponCode').value = code;

      let coupon = { code: code };

      if (discountValue === 'ship') {
        coupon.type = 'ship';
        coupon.value = 0;
      } else if (discountValue.includes('%')) {
        coupon.type = 'percentage';
        coupon.value = parseInt(discountValue);
        coupon.maxDiscount = 500000; // Max 500K for percentage coupons
      } else {
        coupon.type = 'fixed';
        coupon.value = parseInt(discountValue);
      }

      appliedCoupon = coupon;
      updateCartSummary();

      // Show success message
      alert(`Áp dụng mã ${code} thành công!`);
    }

    // Apply coupon from input
    document.getElementById('applyCoupon').addEventListener('click', function() {
      const code = document.getElementById('couponCode').value.trim().toUpperCase();
      const availableCoupons = {
        'WELCOME10': '10%',
        'FREESHIP': 'ship',
        'SAVE50K': '50000'
      };

      if (availableCoupons[code]) {
        applyCoupon(code, availableCoupons[code]);
      } else {
        alert('Mã giảm giá không hợp lệ hoặc đã hết hạn!');
      }
    });

    // Coupon info modal
    const couponModal = document.getElementById('couponModal');
    const couponInfo = document.getElementById('couponInfo');
    const closeCouponModal = document.getElementById('closeCouponModal');

    couponInfo.addEventListener('click', function() {
      couponModal.classList.add('show');
    });

    closeCouponModal.addEventListener('click', function() {
      couponModal.classList.remove('show');
    });

    couponModal.addEventListener('click', function(e) {
      if (e.target === couponModal) {
        couponModal.classList.remove('show');
      }
    });

    // Checkout
    document.getElementById('checkoutBtn').addEventListener('click', function() {
      if (cartItems.length === 0) {
        alert('Giỏ hàng của bạn đang trống!');
        return;
      }

      // In a real application, this would redirect to checkout page
      alert('Chuyển hướng đến trang thanh toán...');
      // window.location.href = 'checkout.php';
    });

    // Initialize cart
    initCart();
  });
</script>

<?php include 'footer.php'?>

</body>
</html>
