<?php
// Khởi động session
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
$addressList = [];

if ($user_id) {
  $cart = $cartModel->findCart($user_id);
  $addressList = $addressModel->getAddressesByUser($user_id);

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
          'product_id' => $item['product_id'],
          'variant_id' => $item['variant_id'],
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

// Xử lý khi có yêu cầu POST từ form
//if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//    // Xử lý đặt hàng
//    if (isset($_POST['place_order'])) {
//        // Lấy dữ liệu từ form
//        $address = $_POST['address'] ?? '';
//        $payment_method = $_POST['payment_method'] ?? '';
//        $coupon_code = $_POST['coupon_code'] ?? '';
//
//        // Ở đây bạn sẽ xử lý lưu vào CSDL
//        // Hiện tại chỉ hiển thị thông báo
//        echo '<script>
//            alert("Đặt hàng thành công!\\n\\nThông tin đơn hàng:\\n- Địa chỉ: ' . htmlspecialchars($address) . '\\n- Phương thức: ' . htmlspecialchars($payment_method) . '\\n- Mã giảm giá: ' . ($coupon_code ? htmlspecialchars($coupon_code) : 'Không có') . '\\n\\nĐơn hàng đang được xử lý.");
//            // Trong thực tế, bạn sẽ chuyển hướng đến trang xác nhận
//        </script>';
//    }
//
//    // Xử lý xác nhận mã giảm giá (trong thực tế sẽ kiểm tra với CSDL)
//    if (isset($_POST['validate_coupon'])) {
//        $coupon_code = $_POST['coupon_code'] ?? '';
//
//        // Mã giảm giá mẫu (trong thực tế lấy từ CSDL)
//        $valid_coupons = [
//            'SALE10' => ['discount' => 10, 'type' => 'percent', 'message' => 'Giảm 10% cho đơn hàng'],
//            'FREESHIP' => ['discount' => 30000, 'type' => 'fixed', 'message' => 'Miễn phí vận chuyển'],
//            'SAVE50K' => ['discount' => 50000, 'type' => 'fixed', 'message' => 'Giảm 50K cho đơn hàng từ 300K']
//        ];
//
//        if (isset($valid_coupons[$coupon_code])) {
//            $coupon = $valid_coupons[$coupon_code];
//            echo json_encode([
//                'valid' => true,
//                'discount' => $coupon['discount'],
//                'type' => $coupon['type'],
//                'message' => $coupon['message']
//            ]);
//        } else {
//            echo json_encode(['valid' => false, 'message' => 'Mã giảm giá không hợp lệ']);
//        }
//        exit;
//    }
//}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/pay.css">
</head>
<?php include 'header.php'?>
<body>
    <div class="container" style="margin-top: 80px">
<!--        <div class="checkout-steps">-->
<!--            <div class="step active">-->
<!--                <div class="step-number">1</div>-->
<!--                <div class="step-title">Giỏ hàng</div>-->
<!--            </div>-->
<!--            <div class="step active">-->
<!--                <div class="step-number">2</div>-->
<!--                <div class="step-title">Thanh toán</div>-->
<!--            </div>-->
<!--            <div class="step">-->
<!--                <div class="step-number">3</div>-->
<!--                <div class="step-title">Hoàn tất</div>-->
<!--            </div>-->
<!--        </div>-->

        <form method="POST" action="" id="checkout-form">
            <div class="checkout-container">
                <div class="checkout-left">
                    <div class="section">
                        <a href="cart.php" class="back-to-cart btn" style="margin: 0; width: fit-content">
                          <i class="fa-solid fa-arrow-left"></i>
                          Quay lại giỏ hàng
                        </a>
                    </div>

                    <!-- Phần địa chỉ giao hàng -->
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Địa chỉ giao hàng
                        </h2>

                        <div class="address-section">
                            <!-- Địa chỉ sẽ được thêm bằng JavaScript -->
                            <div id="address-list"></div>

                            <button type="button" class="add-address-btn" id="show-address-form">
                                <i class="fas fa-plus"></i> Thêm địa chỉ mới
                            </button>

                            <button type="button" class="location-btn" id="use-location">
                                <i class="fas fa-location-arrow"></i> Sử dụng vị trí hiện tại
                            </button>

                            <div class="location-loading" id="location-loading">
                                <i class="fas fa-spinner fa-spin"></i> Đang lấy vị trí...
                            </div>
                            <div class="location-error" id="location-error"></div>
                            <div class="location-success" id="location-success"></div>

                            <div class="address-form" id="address-form" style="display: none;">
                                <div class="form-group">
                                  <label for="recipient_name">Tên người nhận</label>
                                  <input type="text" id="recipient_name">
                                </div>

                                <div class="form-group">
                                    <label for="address_name">Tên địa chỉ (ví dụ: Nhà, Công ty)</label>
                                    <input type="text" id="address_name">
                                </div>

                                <div class="form-group">
                                    <label for="address_phone">Số điện thoại</label>
                                    <input type="tel" id="address_phone">
                                </div>

                                <div class="form-group">
                                    <label for="address_input">Địa chỉ chi tiết</label>
                                    <input type="text" id="address_input">
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-primary" id="save-address">Lưu địa chỉ</button>
                                    <button type="button" class="btn btn-secondary" id="cancel-address-form">Hủy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần sản phẩm trong giỏ hàng -->
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-shopping-cart"></i>
                            Sản phẩm trong giỏ hàng
                        </h2>

                        <div class="cart-items" id="cart-items">
                            <!-- Sản phẩm sẽ được thêm bằng JavaScript -->
                        </div>
                    </div>

                    <!-- Phần mã giảm giá -->
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-tag"></i>
                            Mã giảm giá
                        </h2>

                        <div class="coupon-section">
                            <div class="coupon-input">
                                <input type="text" id="coupon-input" name="coupon_code" placeholder="Nhập mã giảm giá">
                                <button type="button" id="apply-coupon" class="apply-coupon-btn">
                                    <i class="fas fa-check"></i> Áp dụng
                                </button>
                            </div>

                            <div id="coupon-result"></div>

                            <div class="coupon-list" id="coupon-list">
                                <!-- Danh sách mã giảm giá sẽ được thêm bằng JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="section">
                      <h2 class="section-title">
                        <i class="fa-regular fa-note"></i>
                        Ghi chú đơn hàng
                      </h2>

                      <div class="coupon-section">
                        <div class="coupon-input">
                          <input type="text" id="note-input" name="bote" placeholder="Nhập ghi chú của bạn">
                        </div>

                        <div id="coupon-result"></div>

                        <div class="coupon-list" id="coupon-list">
                          <!-- Danh sách mã giảm giá sẽ được thêm bằng JavaScript -->
                        </div>
                      </div>
                    </div>

                    <!-- Phần phương thức thanh toán -->
                    <div class="section">
                        <h2 class="section-title">
                            <i class="fas fa-credit-card"></i>
                            Phương thức thanh toán
                        </h2>

                        <div class="payment-section">
                            <div class="payment-option selected" data-method="cash">
                                <div class="payment-radio">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                </div>
                                <div class="payment-details">
                                    <h4>Thanh toán khi nhận hàng (COD)</h4>
                                    <p>Thanh toán bằng tiền mặt khi nhận được hàng</p>
                                </div>
                            </div>

                            <div class="payment-option" data-method="momo">
                                <div class="payment-radio">
                                    <input type="radio" name="payment_method" value="momo">
                                </div>
                                <div class="payment-details">
                                    <h4>Thanh toán bằng QR Code</h4>
                                    <p>Quét mã QR để thanh toán trước</p>
                                </div>
                            </div>

                            <div class="momo" id="momo" style="display: none;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ThanhToanDonHangShopVN_<?php echo time(); ?>" alt="QR Code">
                                <p>Quét mã QR này để thanh toán</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-right">
                    <div class="order-summary section">
                        <h2 class="section-title">
                            <i class="fas fa-receipt"></i>
                            Tóm tắt đơn hàng
                        </h2>

                        <div class="summary-row">
                            <span>Tổng sản phẩm</span>
                            <span id="subtotal">0</span>
                        </div>

                        <div class="summary-row">
                            <span>Giảm giá</span>
                            <span id="discount">0</span>
                        </div>

                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <span id="shipping-fee">30,000</span>
                        </div>

                        <div class="summary-row total">
                            <span>TỔNG CỘNG</span>
                            <span id="total">0</span>
                        </div>

                        <input type="hidden" name="address" id="selected-address">
                        <input type="hidden" name="coupon_code" id="applied-coupon-code">

                        <button type="submit" name="place_order" class="place-order-btn btn">
                            <i class="fas fa-shopping-bag"></i> ĐẶT HÀNG
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // ========== DỮ LIỆU MẪU TRONG JAVASCRIPT ==========

        let sampleProducts = <?php echo json_encode($cartItems, JSON_UNESCAPED_UNICODE); ?>;

        // Dữ liệu địa chỉ mẫu
        let sampleAddresses = <?php echo json_encode($addressList, JSON_UNESCAPED_UNICODE); ?>;

        // Dữ liệu mã giảm giá mẫu
        const sampleCoupons = [

        ];

        // Phí vận chuyển mặc định
        const defaultShippingFee = 30000;

        // ========== BIẾN TOÀN CỤC ==========
        let cart = [...sampleProducts];
        let addresses = [...sampleAddresses];
        let coupons = [...sampleCoupons];
        let selectedAddress = addresses.find(addr => addr.isDefault) || addresses[0];
        let appliedCoupon = null;
        let paymentMethod = 'cod';

        // ========== HÀM HIỂN THỊ ==========

        // Hiển thị sản phẩm trong giỏ hàng
        function renderCartItems() {
            const cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';

            cart.forEach(item => {
              const attrText = item.attributeValues?.length
                ? item.attributeValues.join(', ')
                : '';

              console.log(attrText);

                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${formatCurrency(item.price)}</div>
                        <div class="cart-item-quantity">(${attrText})</div>
                        <div class="cart-item-quantity">
                            <span class="quantity-label">Số lượng:</span>
                            <span>${item.quantity}</span>
                        </div>
                    </div>
                `;
                cartItemsContainer.appendChild(itemElement);
            });

            updateOrderSummary();
        }

        // Hiển thị danh sách địa chỉ
        function renderAddressList() {
          const addressListContainer = document.getElementById('address-list');
          addressListContainer.innerHTML = '';

          addresses.forEach(address => {
            const addressElement = document.createElement('div');

            // Sử dụng is_default từ database: "1" hoặc "0"
            addressElement.className = `address-option ${address.is_default === "1" ? 'selected' : ''}`;

            addressElement.innerHTML = `
            <div class="address-radio">
                <input type="radio" name="address_id" value="${address.id}"
                       ${address.is_default === "1" ? 'checked' : ''}
                       onchange="selectAddress(${address.id})">
            </div>

            <div class="address-details">
                <h3>${address.recipient_name}</h3>
                <p>
                    <strong>${address.title || address.name}</strong>
                    ${address.address}
                </p>
                <p>Điện thoại: ${address.phone}</p>
            </div>
        `;

            addressListContainer.appendChild(addressElement);
          });

          // Cập nhật ô địa chỉ đã chọn bên dưới (nếu có)
          if (selectedAddress) {
            document.getElementById('selected-address').value =
              `${selectedAddress.name}: ${selectedAddress.address} - ${selectedAddress.phone}`;
          }
        }


        // Hiển thị danh sách mã giảm giá
        function renderCouponList() {
            const couponListContainer = document.getElementById('coupon-list');
            couponListContainer.innerHTML = '';

            coupons.forEach(coupon => {
                const couponElement = document.createElement('div');
                couponElement.className = 'coupon-item';
                couponElement.innerHTML = `
                    <div>
                        <span class="coupon-code">${coupon.code}</span>
                        <span>${coupon.description}</span>
                    </div>
                `;
                couponListContainer.appendChild(couponElement);
            });
        }

        // Cập nhật tóm tắt đơn hàng
        function updateOrderSummary() {
            // Tính tổng tiền sản phẩm
            const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);

            // Tính giảm giá
            let discount = 0;
            let shippingFee = defaultShippingFee;

            if (appliedCoupon) {
                if (appliedCoupon.type === 'percent') {
                    discount = subtotal * (appliedCoupon.discount / 100);
                } else {
                    discount = appliedCoupon.discount;

                    // Nếu là mã miễn phí vận chuyển
                    if (appliedCoupon.code === 'FREESHIP') {
                        shippingFee = 0;
                    }
                }

                // Kiểm tra điều kiện đơn hàng tối thiểu
                if (appliedCoupon.minOrder > 0 && subtotal < appliedCoupon.minOrder) {
                    discount = 0;
                    if (appliedCoupon.code === 'FREESHIP') {
                        shippingFee = defaultShippingFee;
                    }
                    alert(`Mã ${appliedCoupon.code} yêu cầu đơn hàng tối thiểu ${formatCurrency(appliedCoupon.minOrder)} đ`);
                    appliedCoupon = null;
                    document.getElementById('coupon-input').value = '';
                    updateCouponResult();
                }
            }

            // Tính tổng tiền
            const total = subtotal - discount + shippingFee;

            // Cập nhật giao diện
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('discount').textContent = formatCurrency(discount);
            document.getElementById('shipping-fee').textContent = formatCurrency(shippingFee);
            document.getElementById('total').textContent = formatCurrency(total);
        }

        // Cập nhật kết quả áp dụng mã giảm giá
        function updateCouponResult() {
            const couponResultContainer = document.getElementById('coupon-result');

            if (appliedCoupon) {
                couponResultContainer.innerHTML = `
                    <div class="applied-coupon">
                        <div>
                            <span class="coupon-code">${appliedCoupon.code}</span>
                            <span>${appliedCoupon.description}</span>
                        </div>
                        <button type="button" class="remove-coupon" onclick="removeCoupon()">Xóa</button>
                    </div>
                `;
                document.getElementById('applied-coupon-code').value = appliedCoupon.code;
            } else {
                couponResultContainer.innerHTML = '';
                document.getElementById('applied-coupon-code').value = '';
            }

            updateOrderSummary();
        }

        // ========== HÀM XỬ LÝ SỰ KIỆN ==========

        // Chọn địa chỉ
        function selectAddress(addressId) {
          addresses.forEach(address => {
            address.is_default = (Number(address.id) === Number(addressId) ? "1" : "0");
          });

          selectedAddress = addresses.find(addr => Number(addr.id) === Number(addressId));

          renderAddressList();
        }


        // Áp dụng mã giảm giá
        function applyCoupon() {
            const couponCode = document.getElementById('coupon-input').value.trim().toUpperCase();

            if (!couponCode) {
                alert('Vui lòng nhập mã giảm giá');
                return;
            }

            // Tìm mã giảm giá
            const coupon = coupons.find(c => c.code === couponCode);

            if (coupon) {
                appliedCoupon = coupon;
                updateCouponResult();
            } else {
                alert('Mã giảm giá không hợp lệ');
            }
        }

        // Xóa mã giảm giá
        function removeCoupon() {
            appliedCoupon = null;
            document.getElementById('coupon-input').value = '';
            updateCouponResult();
        }

        // Thêm địa chỉ mới
        async function addNewAddress() {
          const recipient_name = document.getElementById('recipient_name').value.trim();
          const name = document.getElementById('address_name').value.trim();
          const phone = document.getElementById('address_phone').value.trim();
          const address = document.getElementById('address_input').value.trim();

          if (!recipient_name || !name || !phone || !address) {
            alert('Vui lòng điền đầy đủ thông tin');
            return;
          }

          // Kiểm tra số điện thoại
          if (!/^\d{10,11}$/.test(phone)) {
            alert('Số điện thoại không hợp lệ');
            return;
          }

          const formData = new FormData();
          formData.append("action", "add");
          formData.append("recipient_name", recipient_name);
          formData.append("name", name);
          formData.append("phone", phone);
          formData.append("address", address);

          const res = await fetch("address_action.php", {
            method: "POST",
            body: formData
          });

          const data = await res.json();

          if (data.success) {
            addresses.push(data.address);   // nhận address vừa tạo từ PHP
            selectedAddress = data.address;
            renderAddressList();
          } else {
            alert(data.message);
          }

          // Ẩn form
          document.getElementById('address-form').style.display = 'none';

          // Reset form
          document.getElementById('address_name').value = '';
          document.getElementById('address_phone').value = '';
          document.getElementById('address_input').value = '';

          // Chọn địa chỉ mới

          alert('Đã thêm địa chỉ mới thành công!');
        }

        // Sử dụng vị trí hiện tại
        function useCurrentLocation() {
            const loadingElement = document.getElementById('location-loading');
            const errorElement = document.getElementById('location-error');
            const successElement = document.getElementById('location-success');

            loadingElement.style.display = 'block';
            errorElement.textContent = '';
            successElement.textContent = '';

            if (!navigator.geolocation) {
                loadingElement.style.display = 'none';
                errorElement.textContent = 'Trình duyệt của bạn không hỗ trợ định vị';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    try {
                        // Sử dụng OpenStreetMap Nominatim API để lấy địa chỉ
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                        const data = await response.json();

                        if (data && data.display_name) {
                            const address = data.display_name;

                            // Tự động điền vào form địa chỉ
                            document.getElementById('address_input').value = address;
                            document.getElementById('address_name').value = 'Vị trí hiện tại';

                            loadingElement.style.display = 'none';
                            successElement.textContent = 'Đã lấy địa chỉ từ vị trí của bạn';

                            // Hiển thị form địa chỉ
                            document.getElementById('address-form').style.display = 'block';
                        } else {
                            throw new Error('Không thể lấy địa chỉ');
                        }
                    } catch (error) {
                        loadingElement.style.display = 'none';
                        errorElement.textContent = 'Không thể lấy địa chỉ từ vị trí của bạn';
                    }
                },
                (error) => {
                    loadingElement.style.display = 'none';

                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorElement.textContent = 'Bạn đã từ chối quyền truy cập vị trí';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorElement.textContent = 'Thông tin vị trí không khả dụng';
                            break;
                        case error.TIMEOUT:
                            errorElement.textContent = 'Yêu cầu vị trí đã hết thời gian';
                            break;
                        default:
                            errorElement.textContent = 'Đã xảy ra lỗi khi lấy vị trí';
                    }
                }
            );
        }

        // ========== HÀM TIỆN ÍCH ==========

        // Định dạng tiền tệ
        function formatCurrency(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // ========== XỬ LÝ SỰ KIỆN KHI TRANG TẢI XONG ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Hiển thị dữ liệu mẫu
            renderCartItems();
            renderAddressList();
            renderCouponList();
            updateCouponResult();

            // Xử lý hiển thị/ẩn form địa chỉ
            document.getElementById('show-address-form').addEventListener('click', function() {
                document.getElementById('address-form').style.display = 'block';
            });

            document.getElementById('cancel-address-form').addEventListener('click', function() {
                document.getElementById('address-form').style.display = 'none';
            });

            // Xử lý lưu địa chỉ
            document.getElementById('save-address').addEventListener('click', addNewAddress);

            // Xử lý sử dụng vị trí
            document.getElementById('use-location').addEventListener('click', useCurrentLocation);

            // Xử lý áp dụng mã giảm giá
            document.getElementById('apply-coupon').addEventListener('click', applyCoupon);

            // Xử lý phương thức thanh toán
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', function() {
                    // Cập nhật giao diện
                    document.querySelectorAll('.payment-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');

                    // Cập nhật radio button
                    this.querySelector('input[type="radio"]').checked = true;

                    // Cập nhật biến toàn cục
                    paymentMethod = this.dataset.method;

                    // Hiển thị/ẩn QR code
                    const qrCodeElement = document.getElementById('momo');
                    if (paymentMethod === 'momo') {
                        qrCodeElement.style.display = 'block';
                    } else {
                        qrCodeElement.style.display = 'none';
                    }
                });
            });

            // Xử lý form đặt hàng
            // document.getElementById('checkout-form').addEventListener('submit', function(e) {
            //     if (!selectedAddress) {
            //         e.preventDefault();
            //         alert('Vui lòng chọn địa chỉ giao hàng');
            //         return;
            //     }
            //
            //     // Kiểm tra xem đã chọn phương thức thanh toán chưa
            //     const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
            //     if (!paymentSelected) {
            //         e.preventDefault();
            //         alert('Vui lòng chọn phương thức thanh toán');
            //         return;
            //     }
            //
            //     // Hiển thị thông báo xác nhận
            //     const confirmSubmit = confirm('Bạn có chắc chắn muốn đặt hàng?');
            //     if (!confirmSubmit) {
            //         e.preventDefault();
            //     }
            // });

            // Cho phép nhấn Enter để áp dụng mã giảm giá
            document.getElementById('coupon-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyCoupon();
                }
            });

          document.getElementById('checkout-form').addEventListener('submit', async function(e) {
            e.preventDefault(); // Ngăn submit form thông thường

            if (!selectedAddress) {
              alert('Vui lòng chọn địa chỉ giao hàng');
              return;
            }

            // Kiểm tra xem đã chọn phương thức thanh toán chưa
            const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentSelected) {
              alert('Vui lòng chọn phương thức thanh toán');
              return;
            }

            // Hiển thị thông báo xác nhận
            const confirmSubmit = confirm('Bạn có chắc chắn muốn đặt hàng?');
            if (!confirmSubmit) {
              return;
            }

            // Hiển thị loading
            const placeOrderBtn = document.querySelector('.place-order-btn');
            const originalText = placeOrderBtn.innerHTML;
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            placeOrderBtn.disabled = true;

            try {
              // console.log()
              // Chuẩn bị dữ liệu đơn hàng
              const orderData = {
                address_id: selectedAddress.id,
                payment_method: paymentSelected.value,
                coupon_code: appliedCoupon ? appliedCoupon.code : '',
                note: document.getElementById('note-input').value.trim(),

                items: cart.map(item => ({
                  product_id: item.product_id,            // ✅ đúng product_id
                  variant_id: item.variant_id,    // ✅ null nếu không có
                  product_name: item.name,
                  product_sku: item.sku ?? '',
                  quantity: Number(item.quantity),
                  unit_price: Number(item.price)
                })),

                totals: {
                  subtotal: cart.reduce(
                    (t, i) => t + i.price * i.quantity, 0
                  ),

                  discount: appliedCoupon
                    ? (appliedCoupon.type === 'percent'
                      ? cart.reduce((t, i) => t + i.price * i.quantity, 0)
                      * appliedCoupon.discount / 100
                      : appliedCoupon.discount)
                    : 0,

                  shipping_fee: Number(defaultShippingFee),

                  total_amount: Number(
                    document.getElementById('total')
                      .textContent
                      .replace(/[^\d]/g, '')
                  )
                }
              };

              // Gửi request đến server
              const response = await fetch('add_order.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
              });

              const result = await response.json();

              if (result.success) {
                // Đặt hàng thành công
                alert(`Đặt hàng thành công!\n\nMã đơn hàng: ${result.order_number}`);

                // Chuyển hướng đến trang chi tiết đơn hàng hoặc trang chủ
                window.location.href = `account.php?tab=orders`;
              } else {
                // Lỗi khi đặt hàng
                alert('Đặt hàng thất bại: ' + (result.message || 'Có lỗi xảy ra'));
                placeOrderBtn.innerHTML = originalText;
                placeOrderBtn.disabled = false;
              }
            } catch (error) {
              console.error('Error:', error);
              alert('Có lỗi xảy ra khi kết nối đến server');
              placeOrderBtn.innerHTML = originalText;
              placeOrderBtn.disabled = false;
            }
          });
        });

        // Thêm vào phần xử lý submit form
    </script>

<?php include 'footer.php'?>
</body>
</html>
