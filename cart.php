<?php

session_start();

require_once 'class/cart_product.php';
require_once 'class/CartItem.php';
require_once 'class/product_image.php';
require_once 'class/product.php';
require_once 'class/user_address.php';
require_once 'class/product_variant.php';
require_once 'class/variant_attribute.php';
require_once 'class/product_attribute.php';
require_once 'class/attribute_value.php';

$cartModel = new Cart();
$productModel = new Product();
$productImgModel = new ProductImage();
$productVariantModel = new ProductVariant();
$addressModel = new UserAddress();
$variantAttributeModel = new VariantAttribute();
$productAttributeModel = new ProductAttribute();
$attributeValueModel = new AttributeValue();

$user_id = $_SESSION['user_id'] ?? 0;

$cartItems = [];

if ($user_id) {
  // Lấy cart của user
  $cart = $cartModel->findCart($user_id);

  if ($cart) {
    $cart_id = $cart['id'];

    // Lấy tất cả item trong cart
    $items = $cartModel->getItems($cart_id);

    foreach ($items as $item) {
      // Lấy thông tin sản phẩm
      $product = $productModel->findById($item['product_id']);
      $productVariant = $productVariantModel->findById($item['variant_id']);
      $variantAttribute = $variantAttributeModel->getByVariantId($item['variant_id']);

      $attributeValues = [];

      foreach ($variantAttribute as $va) {
        $attributeValues[] = $va['attribute_value'];
      }
      if ($product) {
        $imgMain = $productImgModel->getMainImage($product['id']);
        $cartItems[] = [
          'id' => $item['id'],
          'name' => $product['name_pr'],
          'category' => $product['category'] ?? '',
          'image' => 'img/adminUP/products/' . $imgMain['image_url'] ?? '',
          'alt' => $imgMain['alt_text'],
          'sku' => $productVariant['sku'],
          'price' => $item['price'],
          'originalPrice' => $product['regular_price'],
          'quantity' => $item['quantity'],
          'totalPrice' => $item['price'] * $item['quantity'],
          'discount' => $product['regular_price'] - $item['price'],
          'attributeValues' => $attributeValues
        ];
      }
    }
  }
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
  <link rel="stylesheet" href="css/cart.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>
<?php include 'cornerButton.php'?>

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
          <span class="items-count" id="itemsCount"></span>
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
<!--          <div class="coupon-section">-->
<!--            <div class="coupon-header">-->
<!--              <h4>Mã giảm giá</h4>-->
<!--              <button class="coupon-info" id="couponInfo">-->
<!--                <i class="fas fa-info-circle"></i>-->
<!--              </button>-->
<!--            </div>-->
<!--            <div class="coupon-input-group">-->
<!--              <input type="text" id="couponCode" placeholder="Nhập mã giảm giá">-->
<!--              <button id="applyCoupon">Áp dụng</button>-->
<!--            </div>-->
<!--            <div class="available-coupons">-->
<!--              <p>Mã có sẵn:</p>-->
<!--              <div class="coupon-tags">-->
<!--                <span class="coupon-tag" data-code="WELCOME10" data-discount="10">WELCOME10 - Giảm 10%</span>-->
<!--                <span class="coupon-tag" data-code="FREESHIP" data-discount="ship">FREESHIP - Miễn phí ship</span>-->
<!--                <span class="coupon-tag" data-code="SAVE50K" data-discount="50000">SAVE50K - Giảm 50K</span>-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->

          <!-- Checkout Button -->
          <a class="checkout-btn" id="checkoutBtn" href="pay.php">
            <i class="fas fa-lock"></i>
            Tiến hành thanh toán
          </a>

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
    // Sample cart data from PHP (ensure PHP outputs valid JSON)
    let cartItems = <?php echo json_encode($cartItems, JSON_UNESCAPED_UNICODE); ?>;

    // Normalise data types to avoid string/number bugs
    cartItems = (cartItems || []).map(it => ({
      id: Number(it.id),
      name: it.name,
      image: it.image,
      alt: it.alt || '',
      price: Number(it.price) || 0,
      originalPrice: it.originalPrice !== undefined ? Number(it.originalPrice) : undefined,
      quantity: Math.max(1, Number(it.quantity) || 1),
      attri: it.attributeValues
    }));

    let appliedCoupon = null;
    const SHIPPING_FEE = 30000;
    const MAX_QUANTITY = 99; // Giới hạn số lượng tối đa

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
      // store id as data attribute (string)
      div.dataset.id = String(item.id);

      const isDiscounted = item.originalPrice && item.originalPrice > item.price;

      const attrText = item.attributeValues?.length
        ? item.attributeValues.join(', ')
        : '';

      console.log(attrText);

      div.innerHTML = `
        <div class="item-image">
          <img src="${item.image}" alt="${item.alt || ''}">
        </div>
        <div class="item-details">
          <h3 class="item-name">${item.name}</h3>
          <p class="item-name">(${item.attri})</p>
          <div class="item-price">
            <span class="current-price">${formatPrice(item.price)}</span>
            ${isDiscounted ? `<span class="original-price">${formatPrice(item.originalPrice)}</span>` : ''}
          </div>
          <div class="item-actions">
            <div class="quantity-controls">
              <button class="quantity-btn minus" data-id="${item.id}"
                      ${item.quantity <= 1 ? 'disabled' : ''}>-</button>

              <input type="number" class="quantity-input" value="${item.quantity}"
                     min="1" max="${MAX_QUANTITY}" data-id="${item.id}">

              <button class="quantity-btn plus" data-id="${item.id}"
                      ${item.quantity >= MAX_QUANTITY ? 'disabled' : ''}>+</button>
            </div>
            <button class="remove-btn" data-id="${item.id}">
              <i class="fas fa-trash"></i> Xóa
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
      if (checkoutBtn) checkoutBtn.disabled = cartItems.length === 0;
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
      return cartItems.reduce((total, item) => total + (Number(item.price) * Number(item.quantity)), 0);
    }

    // Get total items
    function getTotalItems() {
      return cartItems.reduce((total, item) => total + (Number(item.quantity) || 0), 0);
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
          return 0;
        default:
          return 0;
      }
    }

    // Toggle empty state
    function toggleEmptyState() {
      const emptyCart = document.getElementById('emptyCart');
      const cartLayout = document.querySelector('.cart-layout');

      if (cartItems.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (cartLayout) cartLayout.style.display = 'none';
      } else {
        if (emptyCart) emptyCart.style.display = 'none';
        if (cartLayout) cartLayout.style.display = 'grid';
      }
    }

    // Optimized: Update only specific item in cart
    function updateCartItem(itemId) {
      const item = cartItems.find(item => Number(item.id) === Number(itemId));
      if (!item) return;

      const itemElement = document.querySelector(`.cart-item[data-id="${itemId}"]`);
      if (!itemElement) return;

      // Update quantity input
      const quantityInput = itemElement.querySelector('.quantity-input');
      if (quantityInput) quantityInput.value = item.quantity;

      // Update minus button state
      const minusBtn = itemElement.querySelector('.minus');
      if (minusBtn) minusBtn.disabled = item.quantity <= 1;

      // Update plus button state
      const plusBtn = itemElement.querySelector('.plus');
      if (plusBtn) plusBtn.disabled = item.quantity >= MAX_QUANTITY;

      // Update item total
      const itemTotal = itemElement.querySelector('.item-total');
      if (itemTotal) itemTotal.textContent = formatPrice(item.price * item.quantity);

      // Update cart summary
      updateCartSummary();

      // Update items count
      const itemsCountEl = document.getElementById('itemsCount');
      if (itemsCountEl) itemsCountEl.textContent = `${getTotalItems()} sản phẩm`;

      // Optional: Add visual feedback
      if (itemTotal) {
        itemTotal.classList.add('updated');
        setTimeout(() => itemTotal.classList.remove('updated'), 300);
      }
    }

    // Delegated event listener for clicks (handles plus/minus/remove/coupon/add)
    document.addEventListener('click', function(e) {
      // Use closest to handle clicks on inner elements (icons, spans...)
      const plusBtn = e.target.closest('.plus');
      const minusBtn = e.target.closest('.minus');
      const removeBtn = e.target.closest('.remove-btn');
      const couponTag = e.target.closest('.coupon-tag');
      const addBtn = e.target.closest('.add-btn');

      // Handle quantity increase
      if (plusBtn) {
        const itemId = Number(plusBtn.dataset.id);
        const item = cartItems.find(it => Number(it.id) === itemId);

        if (item && item.quantity < MAX_QUANTITY) {
          item.quantity = Number(item.quantity) + 1;
          updateCartItem(itemId);
          syncQuantityToServer(itemId, item.quantity);

          // Animation feedback
          plusBtn.classList.add('active');
          setTimeout(() => plusBtn.classList.remove('active'), 200);
        }
        return;
      }

      // Handle quantity decrease
      if (minusBtn) {
        const itemId = Number(minusBtn.dataset.id);
        const item = cartItems.find(it => Number(it.id) === itemId);

        if (item && item.quantity > 1) {
          item.quantity = Number(item.quantity) - 1;
          updateCartItem(itemId);
          syncQuantityToServer(itemId, item.quantity);

          minusBtn.classList.add('active');
          setTimeout(() => minusBtn.classList.remove('active'), 200);
        }
        return;
      }

      // Handle remove item
      if (removeBtn) {
        const itemId = Number(removeBtn.dataset.id);

        if (confirm("Bạn có chắc muốn xóa sản phẩm này?")) {
          removeItem(itemId);
        }

        return;
      }

      // Handle coupon application (if you have coupon tags)
      if (couponTag) {
        const code = couponTag.dataset.code;
        const discount = couponTag.dataset.discount;
        applyCoupon(code, discount);
        return;
      }

      // Handle add to cart from suggestions
      if (addBtn) {
        alert('Sản phẩm đã được thêm vào giỏ hàng!');
        return;
      }
    });

    // Event listeners for quantity inputs (manual typing)
    document.addEventListener('change', function(e) {
      const input = e.target;
      if (!input.classList.contains('quantity-input')) return;

      const itemId = Number(input.dataset.id);
      let quantity = parseInt(input.value, 10);

      // Validate input
      if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
      } else if (quantity > MAX_QUANTITY) {
        quantity = MAX_QUANTITY;
      }

      const item = cartItems.find(it => Number(it.id) === itemId);
      if (item) {
        item.quantity = quantity;
        updateCartItem(itemId);
        syncQuantityToServer(itemId, item.quantity);
      }
    });

    // Also handle input event for real-time validation (only allow numbers)
    document.addEventListener('input', function(e) {
      const input = e.target;
      if (!input.classList.contains('quantity-input')) return;

      let value = input.value;

      // Only allow digits
      value = value.replace(/[^0-9]/g, '');

      // Prevent leading zero
      if (value.startsWith('0')) {
        value = value.replace(/^0+/, '');
      }

      // Limit to max quantity
      if (value !== '' && Number(value) > MAX_QUANTITY) {
        value = String(MAX_QUANTITY);
      }

      if (value === '') {
        // keep empty so user can type, but we won't accept empty on 'change' (we'll set 1)
        input.value = '';
      } else {
        input.value = value;
      }
    });

    // Remove item
    // function removeItem(itemId) {
    //   if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;
    //
    //   // Find item element for animation
    //   const itemElement = document.querySelector(`.cart-item[data-id="${itemId}"]`);
    //   if (itemElement) {
    //     // Add remove animation (optional)
    //     itemElement.classList.add('removing');
    //     setTimeout(() => {
    //       cartItems = cartItems.filter(item => Number(item.id) !== Number(itemId));
    //       initCart();
    //     }, 300);
    //   } else {
    //     cartItems = cartItems.filter(item => Number(item.id) !== Number(itemId));
    //     initCart();
    //   }
    // }

    // Apply coupon
    function applyCoupon(code, discountValue) {
      const couponInput = document.getElementById('couponCode');
      if (couponInput) couponInput.value = code;

      let coupon = { code: code };

      if (discountValue === 'ship') {
        coupon.type = 'ship';
        coupon.value = 0;
      } else if (typeof discountValue === 'string' && discountValue.includes('%')) {
        coupon.type = 'percentage';
        coupon.value = parseInt(discountValue, 10);
        coupon.maxDiscount = 500000;
      } else {
        coupon.type = 'fixed';
        coupon.value = parseInt(discountValue, 10);
      }

      appliedCoupon = coupon;
      updateCartSummary();

      // Show success message
      alert(`Áp dụng mã ${code} thành công!`);
    }

    // Checkout
    const checkoutBtnEl = document.getElementById('checkoutBtn');
    if (checkoutBtnEl) {
      checkoutBtnEl.addEventListener('click', function() {
        if (cartItems.length === 0) {
          alert('Giỏ hàng của bạn đang trống!');
          return;
        }

        // In a real application, this would redirect to checkout page
        alert('Thanh toán ngay...');
        // window.location.href = 'checkout.php';
      });
    }

    // Initialize cart
    initCart();

    function syncQuantityToServer(productId, quantity) {
      fetch("update_quantity.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "update",
          product_id: productId,
          quantity: quantity
        })
      })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            console.error("Update error:", data.message);
          } else {
            console.log(`Updated ${productId} → ${quantity}`);
          }
        })
        .catch(err => console.error("Server error:", err));
    }

    function removeItem(productId) {
      fetch("update_quantity.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "delete",
          product_id: productId
        })
      })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            console.error("Delete error:", data.message);
          } else {
            console.log(`Deleted ${productId}`);
            // 1️⃣ Xóa khỏi state
            cartItems = cartItems.filter(item => Number(item.id) !== Number(productId));

            // 2️⃣ Xóa DOM (có animation)
            const itemElement = document.querySelector(
              `.cart-item[data-id="${productId}"]`
            );

            if (itemElement) {
              itemElement.classList.add('removing'); // optional animation
              setTimeout(() => {
                itemElement.remove();
              }, 300);
            }

            // 3️⃣ Update summary + count + empty state
            updateCartSummary();

            const itemsCountEl = document.getElementById('itemsCount');
            if (itemsCountEl) {
              itemsCountEl.textContent = `${getTotalItems()} sản phẩm`;
            }

            toggleEmptyState();

            // remove DOM, reload cart, ...
          }
        })
        .catch(err => console.error("Server error:", err));
    }
  });

  // function syncQuantityToServer(productId, quantity) {
  //   fetch("update_quantity.php", {
  //     method: "POST",
  //     headers: {
  //       "Content-Type": "application/x-www-form-urlencoded",
  //     },
  //     body: `product_id=${productId}&quantity=${quantity}`
  //   })
  //     .then(res => res.json())
  //     .then(data => {
  //       if (!data.success) {
  //         console.error("Update error:", data.message);
  //       } else {
  //         console.log(`ok${productId}, ${quantity}`)
  //       }
  //     })
  //     .catch(err => console.error("Server error:", err));
  // }



</script>


<?php include 'footer.php'?>

</body>
</html>
