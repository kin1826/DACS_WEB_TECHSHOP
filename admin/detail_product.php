<?php
// admin/detail_product.php
require_once 'class/product.php';
require_once 'class/product_image.php';
require_once 'class/product_attribute.php';
require_once 'class/product_specification.php';
require_once 'class/product_variant.php';
require_once 'class/variant_attribute.php';
require_once 'class/product_review.php';
require_once 'class/attribute_value.php';

$productModel = new Product();
$imageModel = new ProductImage();
$attributeModel = new ProductAttribute();
$specificationModel = new ProductSpecification();
$variantModel = new ProductVariant();
$variantAttributeModel = new VariantAttribute();
$reviewModel = new ProductReview();
$attributeValueModel = new AttributeValue(); // Thêm dòng này

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tab = $_GET['tab'] ?? 'images';


if (!$productId) {
  echo '<script>alert("Sản phẩm không tồn tại!"); window.location.href="admin.php?page=products";</script>';
  exit();
}

$product = $productModel->findById($productId);
if (!$product) {
  echo '<script>alert("Sản phẩm không tồn tại!"); window.location.href="admin.php?page=products";</script>';
  exit();
}

// Chỉ load dữ liệu cơ bản của sản phẩm
$productImages = [];
$productSpecifications = [];
$productVariants = [];
$productReviews = [];
$attributes = [];
$attributeValues = [];

// Xử lý actions cho từng tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = isset($_POST['action']) ? $_POST['action'] : '';

  switch ($tab) {
    case 'images':
      if ($action === 'upload_multiple_images' && isset($_POST['images'])) {
        $imagesData = $_POST['images'];
        $uploadedCount = 0;
        $errorMessages = [];

        foreach ($imagesData as $index => $imageData) {
          // Kiểm tra xem có file được upload không
          if (isset($_FILES['images']['name'][$index]['file']) &&
            $_FILES['images']['name'][$index]['file']) {

            $file = [
              'name' => $_FILES['images']['name'][$index]['file'],
              'type' => $_FILES['images']['type'][$index]['file'],
              'tmp_name' => $_FILES['images']['tmp_name'][$index]['file'],
              'error' => $_FILES['images']['error'][$index]['file'],
              'size' => $_FILES['images']['size'][$index]['file']
            ];

            $uploadResult = $imageModel->uploadImage($file);
            if ($uploadResult['success']) {
              $altText = isset($imageData['alt_text']) ? $imageData['alt_text'] : '';
              $sortOrder = isset($imageData['sort_order']) ? (int)$imageData['sort_order'] : 0;
              $isMain = isset($imageData['is_main']) ? 1 : 0;

              $query = "INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_main)
                              VALUES ('$productId', '{$uploadResult['file_name']}', '$altText', '$sortOrder', '$isMain')";

              if ($imageModel->db_query($query)) {
                $imageId = $imageModel->db_insert_id();
                if ($isMain) {
                  $imageModel->setMainImage($imageId, $productId);
                }
                $uploadedCount++;
              } else {
                $errorMessages[] = "Lỗi khi lưu ảnh " . ($index + 1);
              }
            } else {
              $errorMessages[] = "Lỗi upload ảnh " . ($index + 1) . ": " . $uploadResult['error'];
            }
          }
        }

        if ($uploadedCount > 0) {
          $message = "Đã upload thành công $uploadedCount ảnh!";
          if (!empty($errorMessages)) {
            $message .= "\\n\\nLỗi:\\n" . implode("\\n", $errorMessages);
          }
          echo '<script>alert("' . $message . '");</script>';
        } else if (!empty($errorMessages)) {
          echo '<script>alert("Upload thất bại!\\n\\n' . implode("\\n", $errorMessages) . '");</script>';
        }
      }

      // Xử lý đặt ảnh chính
      elseif ($action === 'set_main_image' && isset($_POST['image_id'])) {
        $imageId = (int)$_POST['image_id'];
        if ($imageModel->setMainImage($imageId, $productId)) {
          echo '<script>alert("Đã đặt làm ảnh chính!");</script>';
        }
      }
      // Xử lý xóa ảnh
      elseif ($action === 'delete_image' && isset($_POST['image_id'])) {
        $imageId = (int)$_POST['image_id'];
        $image = $imageModel->findById($imageId);
        if ($image && $imageModel->delete($imageId)) {
          $imageModel->delete($image['image_url']);
          echo '<script>alert("Xóa ảnh thành công!");</script>';
        }
      }

      // Xử lý sửa ảnh
      if ($action === 'edit_image' && isset($_POST['image_id'])) {
        $imageId = (int)$_POST['image_id'];
        $altText = trim($_POST['alt_text']);
        $sortOrder = (int)$_POST['sort_order'];

        $data = [
          'alt_text' => $altText,
          'sort_order' => $sortOrder
        ];

        if ($imageModel->update($imageId, $data)) {
          echo '<script>alert("Cập nhật ảnh thành công!");</script>';
        }
      }
      break;

    case 'specifications':
      // Xử lý thêm/thêm sửa thông số
      if ($action === 'save_specification') {
        $specName = trim($_POST['spec_name']);
        $specValue = trim($_POST['spec_value']);
        $sortOrder = (int)(isset($_POST['sort_order']) ? $_POST['sort_order'] : 0);

        if ($specName) {
          $specificationModel->saveSpecification($productId, $specName, $specValue, $sortOrder);
          echo '<script>alert("Lưu thông số thành công!");</script>';
        }
      }
      // Xử lý xóa thông số
      elseif ($action === 'delete_specification' && isset($_POST['spec_id'])) {
        $specId = (int)$_POST['spec_id'];
        $specificationModel->delete($specId);
        echo '<script>alert("Xóa thông số thành công!");</script>';
      }

      // Xử lý sửa thông số
      if ($action === 'edit_specification' && isset($_POST['spec_id'])) {
        $specId = (int)$_POST['spec_id'];
        $specName = trim($_POST['spec_name']);
        $specValue = trim($_POST['spec_value']);
        $sortOrder = (int)$_POST['sort_order'];

        $data = [
          'spec_name' => $specName,
          'spec_value' => $specValue,
          'sort_order' => $sortOrder
        ];

        if ($specificationModel->update($specId, $data)) {
          echo '<script>alert("Cập nhật thông số thành công!");</script>';
        }
      }

      // Xử lý thêm nhiều thông số cùng lúc
      if ($action === 'save_multiple_specifications' && isset($_POST['specifications'])) {
        $specifications = $_POST['specifications'];
        $savedCount = 0;
        $errorMessages = [];

        foreach ($specifications as $index => $spec) {
          $specName = trim($spec['name'] ?? '');
          $specValue = trim($spec['value'] ?? '');
          $sortOrder = isset($spec['sort_order']) ? (int)$spec['sort_order'] : 0;

          // Chỉ lưu nếu có cả tên và giá trị
          if (!empty($specName) && !empty($specValue)) {
            // Kiểm tra xem thông số đã tồn tại chưa
            $checkQuery = "SELECT id FROM product_specifications
                              WHERE product_id = '$productId'
                              AND spec_name = '" . $specificationModel->db_escape($specName) . "'";
            $exists = $specificationModel->db_query($checkQuery);

            if ($exists && $specificationModel->db_num_rows($exists) > 0) {
              $errorMessages[] = "Thông số '$specName' đã tồn tại (dòng " . ($index + 1) . ")";
            } else {
              // Lưu thông số mới
              $saveResult = $specificationModel->saveSpecification($productId, $specName, $specValue, $sortOrder);
              if ($saveResult) {
                $savedCount++;
              } else {
                $errorMessages[] = "Lỗi khi lưu thông số '$specName'";
              }
            }
          } else if (!empty($specName) || !empty($specValue)) {
            // Nếu chỉ có một trong hai trường
            $errorMessages[] = "Dòng " . ($index + 1) . ": Cần nhập cả tên và giá trị";
          }
        }

        if ($savedCount > 0) {
          $message = "Đã lưu thành công $savedCount thông số!";
          if (!empty($errorMessages)) {
            $message .= "\\n\\nLỗi:\\n" . implode("\\n", $errorMessages);
          }
          echo '<script>alert("' . $message . '");</script>';
        } else if (!empty($errorMessages)) {
          echo '<script>alert("Không có thông số nào được lưu!\\n\\n' . implode("\\n", $errorMessages) . '");</script>';
        } else {
          echo '<script>alert("Vui lòng nhập thông tin hợp lệ!");</script>';
        }
      }
      break;

    case 'variants':
      // Xử lý thêm variant với attributes từ hệ thống
      if ($action === 'add_variant') {
        $sku = trim($_POST['sku']);
        $price = (float)$_POST['price'];
        $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stockQuantity = (int)$_POST['stock_quantity'];
        $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        // Tạo SKU tự động nếu để trống
        if (empty($sku)) {
          $product = $productModel->findById($productId);
          $sku = $variantModel->generateSKU($product['sku']);
        }

        $variantData = [
          'product_id' => $productId,
          'sku' => $sku,
          'price' => $price,
          'sale_price' => $salePrice,
          'stock_quantity' => $stockQuantity,
          'weight' => $weight,
          'is_default' => $isDefault
        ];

        // Xử lý attributes từ select box
        $attributes = [];
        foreach ($_POST as $key => $value) {
          if (strpos($key, 'attribute_') === 0 && !empty($value)) {
            $attributeId = (int)str_replace('attribute_', '', $key);
            $valueId = (int)$value;
            $attributes[$attributeId] = $valueId;
          }
        }

        // **DEBUG: In ra để kiểm tra**
        error_log("Product ID: " . $productId);
        error_log("Attributes: " . print_r($attributes, true));

        // **SỬA: Sử dụng hàm check đơn giản trước**
        $exists = false;
        if (!empty($attributes)) {
          // Kiểm tra đơn giản: lấy tất cả variants và so sánh thủ công
          $allVariants = $variantModel->getWithAttributes($productId);
          error_log("Total variants: " . count($allVariants));

          foreach ($allVariants as $variant) {
            $variantAttrs = [];
            if (!empty($variant['attributes'])) {
              foreach ($variant['attributes'] as $attr) {
                $variantAttrs[$attr['attribute_id']] = $attr['value_id'];
              }

              if ($variantAttrs == $attributes) {
                $exists = true;
                error_log("Found duplicate variant: " . $variant['sku']);
                break;
              }
            }
          }
        }

        if (!$exists) {
          // Tạo variant với attributes
          $variantId = $variantModel->createWithAttributes($variantData, $attributes);

          if ($variantId) {
            if ($isDefault) {
              $variantModel->setDefaultVariant($variantId, $productId);
            }
            header("Location: admin.php?page=detail_product&id=" . $productId . "&tab=variants&msg=variant_added");
            exit();
          } else {
            echo '<script>alert("Lỗi khi thêm biến thể!");</script>';
          }
        } else {
          echo '<script>alert("Biến thể với các thuộc tính này đã tồn tại!");</script>';
        }
      }
      // Xử lý cập nhật variant
      elseif ($action === 'update_variant' && isset($_POST['variant_id'])) {
        $variantId = (int)$_POST['variant_id'];
        $price = (float)$_POST['price'];
        $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stockQuantity = (int)$_POST['stock_quantity'];
        $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
        $imageId = !empty($variantData['image_id']) ? (int)$variantData['image_id'] : null;
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        $updateData = [
          'price' => $price,
          'sale_price' => $salePrice,
          'stock_quantity' => $stockQuantity,
          'weight' => $weight,
          'image_id' => $imageId,
          'is_default' => $isDefault
        ];

        // Cập nhật variant
        if ($variantModel->update($variantId, $updateData)) {
          // Cập nhật attributes nếu có
          $attributes = [];
          foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0 && !empty($value)) {
              $attributeId = (int)str_replace('attribute_', '', $key);
              $valueId = (int)$value;
              $attributes[$attributeId] = $valueId;
            }
          }

          // Cập nhật attributes cho variant
          if (!empty($attributes)) {
            $variantAttributeModel->updateVariantAttributes($variantId, $attributes);
          }

          if ($isDefault) {
            $variantModel->setDefaultVariant($variantId, $productId);
          }

          echo '<script>alert("Cập nhật biến thể thành công!");</script>';
          header("Location: admin.php?page=detail_product&id=" . $productId . "&tab=variants&msg=variant_added");
          exit();
        } else {
          echo '<script>alert("Lỗi khi cập nhật biến thể!");</script>';
          header("Location: admin.php?page=detail_product&id=" . $productId . "&tab=variants&msg=variant_added");
          exit();
        }
      }
      // Xử lý xóa variant
      elseif ($action === 'delete_variant' && isset($_POST['variant_id'])) {
        $variantId = (int)$_POST['variant_id'];
        if ($variantModel->delete($variantId)) {
          echo '<script>alert("Xóa biến thể thành công!");</script>';
          header("Location: admin.php?page=detail_product&id=" . $productId . "&tab=variants&msg=variant_added");
          exit();
        } else {
          echo '<script>alert("Lỗi khi xóa biến thể!");</script>';
          header("Location: admin.php?page=detail_product&id=" . $productId . "&tab=variants&msg=variant_added");
          exit();
        }
      }

      if ($action === 'add_multiple_variants' && isset($_POST['variants'])) {
        $variantsData = $_POST['variants'];
        $addedCount = 0;
        $errorMessages = [];
        $successMessages = [];

        foreach ($variantsData as $index => $variantData) {
          $sku = trim($variantData['sku'] ?? '');
          $price = isset($variantData['price']) ? (float)$variantData['price'] : 0;
          $salePrice = !empty($variantData['sale_price']) ? (float)$variantData['sale_price'] : null;
          $stockQuantity = isset($variantData['stock_quantity']) ? (int)$variantData['stock_quantity'] : 0;
          $weight = !empty($variantData['weight']) ? (float)$variantData['weight'] : null;
          $isDefault = isset($variantData['is_default']) ? 1 : 0;
          $imageId = !empty($variantData['image_id']) ? (int)$variantData['image_id'] : null;

          // Kiểm tra dữ liệu cơ bản
          if ($price <= 0) {
            $errorMessages[] = "Dòng " . ($index + 1) . ": Giá phải lớn hơn 0";
            continue;
          }

          if ($stockQuantity < 0) {
            $errorMessages[] = "Dòng " . ($index + 1) . ": Số lượng tồn không hợp lệ";
            continue;
          }

          // Tạo SKU tự động nếu để trống
          if (empty($sku)) {
            $product = $productModel->findById($productId);
            $sku = $variantModel->generateSKU($product['sku']);
          }

          // Lấy attributes từ dữ liệu
          $attributes = [];
          if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
            foreach ($variantData['attributes'] as $attrId => $valueId) {
              if (!empty($valueId)) {
                $attributes[(int)$attrId] = (int)$valueId;
              }
            }
          }

          // Kiểm tra trùng lặp (chỉ kiểm tra nếu có attributes)
          $exists = false;
          if (!empty($attributes)) {
            $allVariants = $variantModel->getWithAttributes($productId);
            foreach ($allVariants as $existingVariant) {
              $existingAttrs = [];
              if (!empty($existingVariant['attributes'])) {
                foreach ($existingVariant['attributes'] as $attr) {
                  $existingAttrs[$attr['attribute_id']] = $attr['value_id'];
                }
                if ($existingAttrs == $attributes) {
                  $exists = true;
                  $errorMessages[] = "Dòng " . ($index + 1) . ": Biến thể với các thuộc tính này đã tồn tại (SKU: " . $existingVariant['sku'] . ")";
                  break;
                }
              }
            }
          }

          if (!$exists) {
            $variantData = [
              'product_id' => $productId,
              'sku' => $sku,
              'price' => $price,
              'sale_price' => $salePrice,
              'stock_quantity' => $stockQuantity,
              'weight' => $weight,
              'image_id' => $imageId,
              'is_default' => $isDefault
            ];

            $variantId = $variantModel->createWithAttributes($variantData, $attributes);
            if ($variantId) {
              if ($isDefault) {
                $variantModel->setDefaultVariant($variantId, $productId);
              }
              $addedCount++;
              $successMessages[] = "Dòng " . ($index + 1) . ": Thêm thành công (SKU: $sku)";
            } else {
              $errorMessages[] = "Dòng " . ($index + 1) . ": Lỗi khi thêm biến thể";
            }
          }
        }

        // Hiển thị kết quả
        if ($addedCount > 0) {
          $message = "Đã thêm thành công $addedCount biến thể!";
          if (!empty($successMessages)) {
            $message .= "\\n\\nChi tiết:\\n" . implode("\\n", $successMessages);
          }
          if (!empty($errorMessages)) {
            $message .= "\\n\\nLỗi:\\n" . implode("\\n", $errorMessages);
          }
          echo '<script>alert("' . $message . '");</script>';
        } else if (!empty($errorMessages)) {
          echo '<script>alert("Không có biến thể nào được thêm!\\n\\n' . implode("\\n", $errorMessages) . '");</script>';
        } else {
          echo '<script>alert("Vui lòng nhập thông tin hợp lệ!");</script>';
        }
      }
      break;

    case 'reviews':


      // Xử lý phê duyệt review
      if ($action === 'approve_review' && isset($_POST['review_id'])) {
        $reviewId = (int)$_POST['review_id'];
        $reviewModel->approveReview($reviewId);
        echo '<script>alert("Đã phê duyệt đánh giá!");</script>';
      }
      // Xử lý xóa review
      elseif ($action === 'delete_review' && isset($_POST['review_id'])) {
        $reviewId = (int)$_POST['review_id'];
        $reviewModel->delete($reviewId);
        echo '<script>alert("Xóa đánh giá thành công!");</script>';
      }
      break;
  }
}

// Lấy dữ liệu cho các tab
$productImages = $imageModel->getByProductId($productId);
$productSpecifications = $specificationModel->getByProductId($productId);
$productVariants = $variantModel->getWithAttributes($productId); // Sửa thành getWithAttributes
$productReviews = $reviewModel->getByProductId($productId, false);
$attributes = $attributeModel->getAll();

// Lấy giá trị cho từng attribute
$attributeValues = [];
$attributeValueModel = new AttributeValue();
foreach ($attributes as $attribute) {
  $attributeValues[$attribute['id']] = $attributeValueModel->getByAttribute($attribute['id']);
}

// Tính rating trung bình
$ratingStats = $reviewModel->getAverageRating($productId);


?>

<div class="container">
  <div class="header">
    <div class="header-content">
      <div class="product-header">
        <h1><i class="fas fa-box"></i> Chi tiết sản phẩm</h1>
        <div class="product-info">
          <h2><?php echo htmlspecialchars($product['name_pr']); ?></h2>
          <p class="product-meta">
            SKU: <strong><?php echo htmlspecialchars($product['sku']); ?></strong> |
            Danh mục: <strong><?php echo htmlspecialchars(isset($product['category_name']) ? $product['category_name'] : 'N/A'); ?></strong> |
            Thương hiệu: <strong><?php echo htmlspecialchars(isset($product['brand_name']) ? $product['brand_name'] : 'N/A'); ?></strong>
          </p>
        </div>
      </div>
      <a href="admin.php?page=products" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
      </a>
    </div>
  </div>

  <!-- Tab navigation -->
  <div class="tabs">
    <a href="?page=detail_product&id=<?php echo $productId; ?>&tab=images"
       class="tab <?php echo $tab === 'images' ? 'active' : ''; ?>">
      <i class="fas fa-images"></i> Hình ảnh
    </a>
    <a href="?page=detail_product&id=<?php echo $productId; ?>&tab=specifications"
       class="tab <?php echo $tab === 'specifications' ? 'active' : ''; ?>">
      <i class="fas fa-list-alt"></i> Thông số
    </a>
    <a href="?page=detail_product&id=<?php echo $productId; ?>&tab=variants"
       class="tab <?php echo $tab === 'variants' ? 'active' : ''; ?>">
      <i class="fas fa-layer-group"></i> Biến thể
    </a>
    <a href="?page=detail_product&id=<?php echo $productId; ?>&tab=reviews"
       class="tab <?php echo $tab === 'reviews' ? 'active' : ''; ?>">
      <i class="fas fa-star"></i> Đánh giá
      <?php if ($productReviews): ?>
        <span class="badge"><?php echo count($productReviews); ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Tab content -->
  <div class="tab-content">
    <?php switch($tab):
      case 'images': ?>
      <!-- Tab Hình ảnh -->
      <div class="card">
        <div class="card-header">
          <h3>Quản lý hình ảnh sản phẩm</h3>
        </div>
        <div class="card-body">
          <!-- ========== PHẦN UPLOAD NHIỀU ẢNH CÙNG LÚC ========== -->
          <div class="upload-section">
            <div class="upload-section-header">
              <h4>Upload nhiều ảnh cùng lúc</h4>
<!--              <button type="button" class="btn btn-success btn-add-more" onclick="addImageUploadForm()">-->
<!--                <i class="fas fa-plus"></i> Thêm form upload-->
<!--              </button>-->
            </div>

            <!-- Template cho một form upload (ẩn) -->
            <template id="image-upload-template">
              <div class="image-upload-row">
                <form method="POST" enctype="multipart/form-data" class="upload-form multiple-upload">
                  <input type="hidden" name="tab" value="images">
                  <input type="hidden" name="action" value="upload_multiple_images">

                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label>Chọn ảnh *</label>
                      <input type="file" name="images[][file]" accept="image/*" required class="form-control-file">
                      <img src="" class="image-preview" alt="Preview" style="display: none;">
                    </div>
                    <div class="form-group col-md-4">
                      <label>Alt Text</label>
                      <input type="text" name="images[][alt_text]" placeholder="Mô tả ảnh" class="form-control">
                      <div class="form-group mt-2">
                        <label>Thứ tự</label>
                        <input type="number" name="images[][sort_order]" value="0" min="0" class="form-control">
                      </div>
                    </div>
                    <div class="form-group col-md-4">
                      <div class="checkbox-group">
                        <label class="checkbox-label">
                          <input type="checkbox" name="images[][is_main]" id="is-main">
                          <span class="checkmark"></span>
                          Đặt làm ảnh chính
                        </label>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm btn-remove-upload" onclick="removeUploadForm(this)">
                        <i class="fas fa-trash"></i> Xóa form này
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </template>

            <!-- Container chứa các form upload -->
            <div id="multiple-upload-container">
              <!-- Form upload mặc định -->
              <div class="image-upload-row">
                <form method="POST" enctype="multipart/form-data" class="upload-form multiple-upload">
                  <input type="hidden" name="tab" value="images">
                  <input type="hidden" name="action" value="upload_multiple_images">

                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label>Chọn ảnh *</label>
                      <input type="file" name="images[0][file]" accept="image/*" required
                             class="form-control-file" onchange="previewImage(this, 'image-preview-0')" id="image-input-0">
                      <img src="" class="image-preview" id="image-preview-0" alt="Preview" style="display: none;">
                    </div>
                    <div class="form-group col-md-4">
                      <label>Alt Text</label>
                      <input type="text" name="images[0][alt_text]" placeholder="Mô tả ảnh" class="form-control">
                      <div class="form-group mt-2">
                        <label>Thứ tự</label>
                        <input type="number" name="images[0][sort_order]" value="0" min="0" class="form-control">
                      </div>
                    </div>
                    <div class="form-group col-md-4">
                      <div class="checkbox-group">
                        <label class="checkbox-label">
                          <input type="checkbox" name="images[0][is_main]" id="is-main-0">
                          <span class="checkmark"></span>
                          Đặt làm ảnh chính
                        </label>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm btn-remove-upload" onclick="removeUploadForm(this)">
                        <i class="fas fa-trash"></i> Xóa form này
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Nút submit tất cả -->
            <div class="mt-3">
              <button type="button" class="btn btn-primary" onclick="submitAllImages()">
                <i class="fas fa-upload"></i> Upload tất cả ảnh
              </button>
              <span class="ml-2 text-muted" id="upload-status"></span>
              <button type="button" class="btn btn-success btn-add-more" onclick="addImageUploadForm()">
                <i class="fas fa-plus"></i> Thêm form upload
              </button>
            </div>
          </div>

          <hr>

          <!-- ========== PHẦN UPLOAD 1 ẢNH (GIỮ LẠI ĐỂ DÙNG) ========== -->
          <div class="upload-section mt-4">
            <h4>Thêm ảnh mới (từng cái)</h4>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
              <input type="hidden" name="action" value="upload_image">
              <div class="form-row">
                <div class="form-group">
                  <label>Chọn ảnh</label>
                  <input type="file" name="new_image" accept="image/*" required>
                </div>
                <div class="form-group">
                  <label>Alt Text</label>
                  <input type="text" name="alt_text" placeholder="Mô tả ảnh">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Thứ tự</label>
                  <input type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group checkbox-group">
                  <label class="checkbox-label">
                    <input type="checkbox" name="is_main">
                    <span class="checkmark"></span>
                    Đặt làm ảnh chính
                  </label>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload ảnh
              </button>
            </form>
          </div>

          <!-- Danh sách ảnh -->
          <div class="images-grid mt-4">
            <h4>Ảnh hiện có (<?php echo count($productImages); ?>)</h4>
            <?php if ($productImages): ?>
              <div class="images-list">
                <?php foreach ($productImages as $image): ?>
                  <div class="image-item <?php echo $image['is_main'] ? 'main-image' : ''; ?>" id="image-<?php echo $image['id']; ?>">
                    <img src="img/adminUP/products/<?php echo $image['image_url']; ?>"
                         alt="<?php echo htmlspecialchars($image['alt_text']); ?>">

                    <!-- Form sửa ảnh (ẩn/hiện bằng JavaScript) -->
                    <div class="edit-form" id="edit-form-<?php echo $image['id']; ?>" style="display: none;">
                      <form method="POST" class="edit-image-form">
                        <input type="hidden" name="action" value="edit_image">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <div class="form-group">
                          <label>Alt Text</label>
                          <input type="text" name="alt_text" value="<?php echo htmlspecialchars($image['alt_text']); ?>" class="form-control-sm">
                        </div>
                        <div class="form-group">
                          <label>Thứ tự</label>
                          <input type="number" name="sort_order" value="<?php echo $image['sort_order']; ?>" min="0" class="form-control-sm">
                        </div>
                        <div class="form-actions-sm">
                          <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Lưu
                          </button>
                          <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEdit(<?php echo $image['id']; ?>)">
                            <i class="fas fa-times"></i> Hủy
                          </button>
                        </div>
                      </form>
                    </div>

                    <div class="image-actions">
                      <?php if (!$image['is_main']): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="set_main_image">
                          <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                          <button type="submit" class="btn-action btn-set-main" title="Đặt làm ảnh chính">
                            <i class="fas fa-star"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="main-badge">Ảnh chính</span>
                      <?php endif; ?>

                      <button type="button" class="btn-action btn-edit" title="Sửa" onclick="toggleEdit(<?php echo $image['id']; ?>)">
                        <i class="fas fa-edit"></i>
                      </button>

                      <form method="POST" style="display: inline;" onsubmit="return confirm('Xóa ảnh này?')">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                        <button type="submit" class="btn-action btn-delete" title="Xóa ảnh">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                    <div class="image-info">
                      <small>Thứ tự: <?php echo $image['sort_order']; ?></small>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="no-data">Chưa có ảnh nào</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- JavaScript xử lý submit tất cả form -->
      <script>
        function submitAllImages() {
          const container = document.getElementById("multiple-upload-container");
          const forms = container.querySelectorAll(".multiple-upload");

          if (forms.length === 0) {
            alert("Không có ảnh nào để upload!");
            return;
          }

          // Tạo FormData tổng hợp
          const formData = new FormData();
          formData.append("tab", "images");
          formData.append("action", "upload_multiple_images");

          let hasValidFile = false;

          // Thu thập dữ liệu từ tất cả các form
          forms.forEach((form, index) => {
            const inputs = form.querySelectorAll("input, select, textarea");

            inputs.forEach(input => {
              if (input.type === "file") {
                if (input.files.length > 0) {
                  hasValidFile = true;
                  formData.append(`images[${index}][file]`, input.files[0]);
                }
              } else if (input.type === "checkbox") {
                formData.append(`images[${index}][${input.name.split('[')[2]?.split(']')[0] || 'is_main'}]`, input.checked ? '1' : '0');
              } else if (input.name && input.value) {
                const fieldName = input.name.match(/\[(.*?)\]/);
                if (fieldName) {
                  formData.append(`images[${index}][${fieldName[1]}]`, input.value);
                }
              }
            });
          });

          if (!hasValidFile) {
            alert("Vui lòng chọn ít nhất một ảnh để upload!");
            return;
          }

          // Hiển thị trạng thái loading
          const status = document.getElementById("upload-status");
          status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...';
          status.className = "ml-2 text-info";

          // Gửi request bằng AJAX
          fetch(window.location.href, {
            method: "POST",
            body: formData
          })
            .then(response => response.text())
            .then(data => {
              // Tìm thông báo trong response
              if (data.includes("alert")) {
                // Trích xuất thông báo từ alert
                const match = data.match(/alert\("([^"]+)"\)/);
                if (match) {
                  status.innerHTML = `<i class="fas fa-check"></i> ${match[1]}`;
                  status.className = "ml-2 text-success";

                  // Reload trang sau 1.5 giây để xem kết quả
                  setTimeout(() => {
                    window.location.reload();
                  }, 1500);
                }
              } else {
                // Nếu không có alert, reload luôn
                status.innerHTML = '<i class="fas fa-check"></i> Upload thành công!';
                status.className = "ml-2 text-success";
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              }
            })
            .catch(error => {
              status.innerHTML = '<i class="fas fa-times"></i> Upload thất bại!';
              status.className = "ml-2 text-danger";
              console.error("Error:", error);
            });
        }

        // Thêm xử lý enter cho các input
        document.addEventListener("DOMContentLoaded", function() {
          const container = document.getElementById("multiple-upload-container");
          if (container) {
            container.addEventListener("keypress", function(e) {
              if (e.key === "Enter" && e.target.tagName === "INPUT") {
                e.preventDefault();
              }
            });
          }
        });
      </script>
      <?php break; ?>

    <?php case 'specifications': ?>
      <!-- Tab Thông số -->
      <div class="card">
        <div class="card-header">
          <h3>Quản lý thông số kỹ thuật</h3>
        </div>
        <div class="card-body">
          <!-- ========== PHẦN THÊM NHIỀU THÔNG SỐ CÙNG LÚC ========== -->
          <div class="add-spec-section">
            <div class="section-header">
              <h4>Thêm nhiều thông số cùng lúc</h4>
<!--              <button type="button" class="btn btn-success btn-sm" onclick="addSpecificationRow()">-->
<!--                <i class="fas fa-plus"></i> Thêm dòng-->
<!--              </button>-->
            </div>

            <!-- Template cho một dòng thông số (ẩn) -->
            <template id="spec-row-template">
              <div class="spec-row row mb-2">
                <div class="form-group col-md-4">
                  <input type="text" name="specifications[][name]"
                         class="form-control" placeholder="Tên thông số *" required>
                </div>
                <div class="form-group col-md-4">
                  <input type="text" name="specifications[][value]"
                         class="form-control" placeholder="Giá trị *" required>
                </div>
                <div class="form-group col-md-2">
                  <input type="number" name="specifications[][sort_order]"
                         class="form-control" placeholder="Thứ tự" value="0" min="0">
                </div>
                <div class="form-group col-md-2">
                  <button type="button" class="btn btn-danger btn-sm btn-remove-upload" onclick="removeSpecRow(this)"
                  style="width: fit-content">
                    <i class="fas fa-trash"></i> Xóa
                  </button>
                </div>
              </div>
            </template>

            <!-- Container chứa các dòng thông số -->
            <form method="POST" id="bulk-spec-form">
              <input type="hidden" name="tab" value="specifications">
              <input type="hidden" name="action" value="save_multiple_specifications">

              <div id="specifications-container">
                <!-- Dòng mặc định -->
                <div class="spec-row row mb-2">
                  <div class="form-group col-md-4">
                    <input type="text" name="specifications[0][name]"
                           class="form-control" placeholder="Tên thông số *" required>
                  </div>
                  <div class="form-group col-md-4">
                    <input type="text" name="specifications[0][value]"
                           class="form-control" placeholder="Giá trị *" required>
                  </div>
                  <div class="form-group col-md-2">
                    <input type="number" name="specifications[0][sort_order]"
                           class="form-control" placeholder="Thứ tự" value="0" min="0">
                  </div>
                  <div class="form-group col-md-2">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-upload" onclick="removeSpecRow(this)" style="width: fit-content">
                      <i class="fas fa-trash"></i> Xóa
                    </button>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <button type="button" class="btn btn-primary" onclick="submitAllSpecifications()">
                  <i class="fas fa-save"></i> Lưu tất cả thông số
                </button>
                <span class="ml-2 text-muted" id="spec-status"></span>

                <button type="button" class="btn btn-success btn-sm" onclick="addSpecificationRow()">
                  <i class="fas fa-plus"></i> Thêm dòng
                </button>
              </div>
            </form>
          </div>

          <hr>

          <!-- ========== PHẦN THÊM TỪNG THÔNG SỐ (GIỮ LẠI) ========== -->
          <div class="add-spec-section mt-4">
            <h4>Thêm thông số mới (từng cái)</h4>
            <form method="POST" class="spec-form">
              <input type="hidden" name="action" value="save_specification">
              <div class="form-row">
                <div class="form-group">
                  <label>Tên thông số *</label>
                  <input type="text" name="spec_name" required>
                </div>
                <div class="form-group">
                  <label>Giá trị *</label>
                  <input type="text" name="spec_value" required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Thứ tự</label>
                  <input type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm thông số
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Danh sách thông số -->
          <div class="specs-list mt-4">
            <h4>Thông số hiện có (<?php echo count($productSpecifications); ?>)</h4>
            <?php if ($productSpecifications): ?>
              <table class="table">
                <thead>
                <tr>
                  <th>Tên thông số</th>
                  <th>Giá trị</th>
                  <th width="80">Thứ tự</th>
                  <th width="120">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($productSpecifications as $spec): ?>
                  <tr id="spec-<?php echo $spec['id']; ?>">
                    <!-- Hiển thị thông tin -->
                    <td class="view-mode">
                      <?php echo htmlspecialchars($spec['spec_name']); ?>
                    </td>
                    <td class="view-mode">
                      <?php echo htmlspecialchars($spec['spec_value']); ?>
                    </td>
                    <td class="view-mode">
                      <?php echo $spec['sort_order']; ?>
                    </td>
                    <td class="view-mode">
                      <div class="action-buttons">
                        <button type="button" class="btn-action btn-edit" title="Sửa"
                                onclick="toggleEditSpec(<?php echo $spec['id']; ?>)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" onsubmit="return confirm('Xóa thông số này?')" style="display: inline;">
                          <input type="hidden" name="action" value="delete_specification">
                          <input type="hidden" name="spec_id" value="<?php echo $spec['id']; ?>">
                          <button type="submit" class="btn-action btn-delete" title="Xóa">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>

                    <!-- Form sửa (ẩn) -->
                    <td colspan="4" class="edit-mode" style="display: none;">
                      <form method="POST" class="edit-spec-form">
                        <input type="hidden" name="action" value="edit_specification">
                        <input type="hidden" name="spec_id" value="<?php echo $spec['id']; ?>">
                        <div class="form-row">
                          <div class="form-group">
                            <input type="text" name="spec_name" value="<?php echo htmlspecialchars($spec['spec_name']); ?>"
                                   class="form-control-sm" required>
                          </div>
                          <div class="form-group">
                            <input type="text" name="spec_value" value="<?php echo htmlspecialchars($spec['spec_value']); ?>"
                                   class="form-control-sm" required>
                          </div>
                          <div class="form-group">
                            <input type="number" name="sort_order" value="<?php echo $spec['sort_order']; ?>"
                                   min="0" class="form-control-sm">
                          </div>
                          <div class="form-group">
                            <div class="action-buttons">
                              <button type="submit" class="btn-action btn-success">
                                <i class="fas fa-check"></i>
                              </button>
                              <button type="button" class="btn-action btn-secondary"
                                      onclick="toggleEditSpec(<?php echo $spec['id']; ?>)">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p class="no-data">Chưa có thông số nào</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- JavaScript cho tab Thông số -->
      <script>
        // Hàm thêm dòng thông số mới
        function addSpecificationRow() {
          const container = document.getElementById("specifications-container");
          const template = document.getElementById("spec-row-template");
          const clone = template.content.cloneNode(true);

          // Tăng số thứ tự
          const index = container.querySelectorAll(".spec-row").length;
          const lastSortOrder = getLastSortOrder();

          // Cập nhật tên input
          // clone.querySelectorAll("[name]").forEach(input => {
          //   const name = input.name;
          //   input.name = name.replace("[]", "[" + index + "]");
          // });

          clone.querySelectorAll("[name]").forEach(input => {
            const name = input.name;
            input.name = name.replace("[]", "[" + index + "]");

            // Set giá trị thứ tự = giá trị cuối + 1
            if (name.includes("sort_order")) {
              input.value = lastSortOrder + 1;
            }
          });

          container.appendChild(clone);

          // Focus vào ô đầu tiên của dòng mới
          const newRow = container.lastElementChild;
          const firstInput = newRow.querySelector("input");
          if (firstInput) {
            firstInput.focus();
          }
        }

        // Hàm xóa dòng thông số
        function removeSpecRow(button) {
          const row = button.closest(".spec-row");
          if (row) {
            row.remove();

            // Đánh lại index cho các dòng còn lại
            const container = document.getElementById("specifications-container");
            const rows = container.querySelectorAll(".spec-row");

            rows.forEach((row, index) => {
              const inputs = row.querySelectorAll("[name]");
              inputs.forEach(input => {
                const oldName = input.name;
                // Tìm và thay thế index cũ bằng index mới
                const newName = oldName.replace(/\[\d+\]/, "[" + index + "]");
                input.name = newName;
              });
            });
          }
        }

        // Hàm submit tất cả thông số
        function submitAllSpecifications() {
          const form = document.getElementById("bulk-spec-form");
          const formData = new FormData(form);

          // Kiểm tra xem có dòng nào hợp lệ không
          const rows = document.querySelectorAll(".spec-row");
          let hasValidData = false;

          rows.forEach(row => {
            const nameInput = row.querySelector("input[name*='[name]']");
            const valueInput = row.querySelector("input[name*='[value]']");

            if (nameInput && nameInput.value.trim() !== '' &&
              valueInput && valueInput.value.trim() !== '') {
              hasValidData = true;
            }
          });

          if (!hasValidData) {
            alert("Vui lòng nhập ít nhất một thông số hợp lệ!");
            return;
          }

          // Hiển thị trạng thái loading
          const status = document.getElementById("spec-status");
          status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
          status.className = "ml-2 text-info";

          // Gửi request
          fetch(window.location.href, {
            method: "POST",
            body: formData
          })
            .then(response => response.text())
            .then(data => {
              // Tìm thông báo trong response
              if (data.includes("alert")) {
                const match = data.match(/alert\("([^"]+)"\)/);
                if (match) {
                  status.innerHTML = `<i class="fas fa-check"></i> ${match[1]}`;
                  status.className = "ml-2 text-success";

                  // Xóa tất cả dòng sau khi lưu thành công
                  const container = document.getElementById("specifications-container");
                  container.innerHTML = '';
                  // Thêm lại 1 dòng trống
                  addSpecificationRow();

                  // Reload trang sau 1.5 giây
                  setTimeout(() => {
                    window.location.reload();
                  }, 1500);
                }
              } else {
                status.innerHTML = '<i class="fas fa-check"></i> Lưu thành công!';
                status.className = "ml-2 text-success";

                // Xóa form
                const container = document.getElementById("specifications-container");
                container.innerHTML = '';
                addSpecificationRow();

                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              }
            })
            .catch(error => {
              status.innerHTML = '<i class="fas fa-times"></i> Lưu thất bại!';
              status.className = "ml-2 text-danger";
              console.error("Error:", error);
            });
        }

        // Hàm toggle form sửa thông số (giữ nguyên)
        function toggleEditSpec(specId) {
          const row = document.getElementById("spec-" + specId);
          if (!row) return;

          const viewCells = row.querySelectorAll(".view-mode");
          const editCell = row.querySelector(".edit-mode");

          if (editCell.style.display === "none") {
            // Chuyển sang mode edit
            viewCells.forEach(cell => cell.style.display = "none");
            editCell.style.display = "table-cell";
            editCell.colSpan = 4;

            // Focus vào ô đầu tiên
            const firstInput = editCell.querySelector("input");
            if (firstInput) {
              firstInput.focus();
            }
          } else {
            // Chuyển về mode view
            viewCells.forEach(cell => cell.style.display = "");
            editCell.style.display = "none";
          }
        }

        // Thêm CSS động
        document.addEventListener("DOMContentLoaded", function() {
          const style = document.createElement("style");
          style.textContent = `
        .spec-row {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 8px;
            background: #fff;
            transition: all 0.2s ease;
        }
        .spec-row:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-action {
            padding: 3px 8px;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-action.btn-edit {
            color: #17a2b8;
        }
        .btn-action.btn-edit:hover {
            background: #17a2b8;
            color: white;
        }
        .btn-action.btn-delete {
            color: #dc3545;
        }
        .btn-action.btn-delete:hover {
            background: #dc3545;
            color: white;
        }
        .btn-action.btn-success {
            color: #28a745;
        }
        .btn-action.btn-success:hover {
            background: #28a745;
            color: white;
        }
        .btn-action.btn-secondary {
            color: #6c757d;
        }
        .btn-action.btn-secondary:hover {
            background: #6c757d;
            color: white;
        }
    `;
          document.head.appendChild(style);

          // Khởi tạo 2 dòng mặc định
          setTimeout(() => {
            addSpecificationRow();
          }, 100);
        });
      </script>
      <?php break; ?>

    <?php case 'variants': ?>
      <!-- Tab Biến thể -->
      <div class="card">
        <div class="card-header">
          <h3>Quản lý biến thể sản phẩm</h3>
        </div>
        <div class="card-body">
          <!-- ========== PHẦN THÊM NHIỀU BIẾN THỂ CÙNG LÚC ========== -->
          <div class="add-variant-section">
            <div class="section-header">
              <h4>Thêm nhiều biến thể cùng lúc</h4>
<!--              <button type="button" class="btn btn-success btn-sm" onclick="addVariantRow()">-->
<!--                <i class="fas fa-plus"></i> Thêm dòng biến thể-->
<!--              </button>-->
            </div>

            <!-- Template cho một dòng biến thể (ẩn) -->
            <template id="variant-row-template">
              <div class="variant-row card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">Biến thể <span class="variant-index"> __INDEX__</span></h4>
                  <button type="button" class="btn btn-danger btn-sm" onclick="removeVariantRow(this)">
                    <i class="fas fa-times"></i> Xóa
                  </button>
                </div>
                <div class="card-body">
                  <!-- Phần thuộc tính -->
                  <div class="attributes-section mb-3">
                    <h4>Thuộc tính:</h4>
                    <div class="row">
                      <?php foreach ($attributes as $attribute): ?>
                        <?php if (!empty($attributeValues[$attribute['id']])): ?>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label><?php echo htmlspecialchars($attribute['name']); ?></label>
                              <select name="variants[__INDEX__][attributes][<?php echo $attribute['id']; ?>]"
                                      class="form-control form-control-sm">
                                <option value="">-- Chọn --</option>
                                <?php foreach ($attributeValues[$attribute['id']] as $value): ?>
                                  <option value="<?php echo $value['id']; ?>"
                                    <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                      data-color="<?php echo $value['color_code']; ?>"
                                    <?php endif; ?>>
                                    <?php echo htmlspecialchars($value['value']); ?>
                                    <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                      <span class="color-preview" style="background: <?php echo $value['color_code']; ?>"></span>
                                    <?php endif; ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- Phần thông tin cơ bản -->
                  <div class="basic-info-section">
                    <h4>Thông tin cơ bản:</h4>
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-group">
                          <label>SKU</label>
                          <input type="text" name="variants[__INDEX__][sku]"
                                 class="form-control form-control-sm" placeholder="Tự động tạo">
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <label>Giá *</label>
                          <input type="number" name="variants[__INDEX__][price]"
                                 class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <label>Giá KM</label>
                          <input type="number" name="variants[__INDEX__][sale_price]"
                                 class="form-control form-control-sm" step="0.01" min="0">
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <label>Tồn kho *</label>
                          <input type="number" name="variants[__INDEX__][stock_quantity]"
                                 class="form-control form-control-sm" value="0" min="0" required>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="form-group">
                          <label>Trọng lượng (kg)</label>
                          <input type="number" name="variants[__INDEX__][weight]"
                                 class="form-control form-control-sm" step="0.01" min="0">
                        </div>
                      </div>

                      <div class="col-md-2">
                        <div class="form-group">
                          <label>Ảnh biến thể</label>

                          <!-- Nút toggle để mở/đóng danh sách ảnh -->
                          <button type="button" class="btn btn-light btn-block btn-sm mb-2"
                                  onclick="toggleImageList(this)">
                            <i class="fas fa-images mr-1"></i>
                            Chọn ảnh
                          </button>

                          <!-- Hiển thị ảnh đã chọn và nút hủy -->
                          <div class="selected-image-container mb-2" style="display: none;">
                            <div class="selected-image-preview text-center p-2 border rounded bg-light">
                              <img src="" alt="Đã chọn"
                                   style="max-width: 60px; max-height: 60px; border: 2px solid #28a745; border-radius: 4px;">
                              <div class="small text-success mt-1">Đã chọn</div>
                            </div>

                            <!-- Nút hủy chọn -->
                            <div class="text-center mt-2">
                              <button type="button" class="btn btn-sm btn-outline-danger"
                                      onclick="clearImageSelection(this)" title="Hủy chọn">
                                <i class="fas fa-times mr-1"></i> Hủy chọn
                              </button>
                            </div>
                          </div>

                          <!-- Thông báo chưa chọn ảnh -->
                          <div class="no-image-selected text-center text-muted small mb-2">
                            <i class="fas fa-image"></i> Chưa chọn ảnh
                          </div>

                          <input type="hidden" name="variants[__INDEX__][image_id]"
                                 class="variant-image-id" value="">

                          <!-- Danh sách ảnh (ẩn mặc định) -->
                          <div class="image-selection-grid" style="display: none;">
                            <div class="row">
                              <?php if (!empty($productImages)): ?>
                                <?php foreach ($productImages as $image): ?>
                                  <?php
                                  $imagePath = 'img/adminUP/products/' . $image['image_url'];
                                  $altText = $image['alt_text'] ?: 'Ảnh ' . $image['id'];
                                  ?>
                                  <div class="col-4 mb-2">
                                    <div class="image-option-card text-center p-2 border rounded"
                                         onclick="selectImage(this, <?php echo $image['id']; ?>, '<?php echo $imagePath; ?>', '<?php echo htmlspecialchars($altText, ENT_QUOTES); ?>')"
                                         style="cursor: pointer; transition: all 0.2s;">
                                      <img src="<?php echo $imagePath; ?>"
                                           alt="<?php echo htmlspecialchars($altText); ?>"
                                           class="img-fluid mb-1"
                                           style="width: 60px; height: 60px; object-fit: cover; border-radius: 3px;"
                                           onerror="this.src='img/default-product.jpg'">
                                      <div class="small text-truncate"><?php echo htmlspecialchars($altText); ?></div>
                                      <small class="text-muted">ID: <?php echo $image['id']; ?></small>
                                      <div class="selected-check" style="display: none;">
                                        <i class="fas fa-check-circle text-success"></i>
                                      </div>
                                    </div>
                                  </div>
                                <?php endforeach; ?>
                              <?php else: ?>
                                <div class="col-12">
                                  <div class="alert alert-light text-center py-3">
                                    <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                    <p class="mb-0">Không có ảnh nào</p>
                                  </div>
                                </div>
                              <?php endif; ?>
                            </div>

                            <!-- Nút đóng và hủy -->
                            <div class="text-center mt-2">
                              <button type="button" class="btn btn-sm btn-outline-secondary mr-2"
                                      onclick="closeImageList(this)">
                                <i class="fas fa-times mr-1"></i> Đóng
                              </button>
                              <button type="button" class="btn btn-sm btn-outline-danger"
                                      onclick="clearImageSelectionFromList(this)">
                                <i class="fas fa-undo mr-1"></i> Hủy chọn
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-2">
                        <div class="form-group">
                          <div class="checkbox-group mt-4">
                            <label class="checkbox-label">
                              <input type="checkbox" name="variants[__INDEX__][is_default]">
                              <span class="checkmark"></span>
                              Mặc định
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Preview thông tin -->
                  <div class="variant-preview mt-2 p-2 bg-light border rounded">
                    <small class="text-muted">
                      <span class="preview-attributes"></span> |
                      Giá: <span class="preview-price">0</span>₫ |
                      Tồn: <span class="preview-stock">0</span>
                    </small>
                  </div>
                </div>
              </div>
            </template>

            <!-- Container chứa các dòng biến thể -->
            <form method="POST" id="bulk-variant-form">
              <input type="hidden" name="tab" value="variants">
              <input type="hidden" name="action" value="add_multiple_variants">

              <div id="variants-container">
                <!-- Dòng mặc định sẽ được thêm bằng JavaScript -->
              </div>

              <div class="mt-3">
                <button type="button" class="btn btn-primary" onclick="submitAllVariants()"
                style="margin-top: 10px">
                  <i class="fas fa-save"></i> Lưu tất cả biến thể
                </button>
                <span class="ml-2 text-muted" id="variant-status"></span>

                <button type="button" class="btn btn-success btn-sm" onclick="addVariantRow()">
                  <i class="fas fa-plus"></i> Thêm dòng biến thể
                </button>
              </div>
            </form>
          </div>

          <hr>

          <!-- ========== PHẦN THÊM TỪNG BIẾN THỂ (GIỮ LẠI) ========== -->
          <div class="add-variant-section mt-4">
            <h4>Thêm biến thể mới (từng cái)</h4>
            <form method="POST" class="variant-form" id="variantForm">
              <input type="hidden" name="action" value="add_variant">

              <!-- Phần thuộc tính từ hệ thống -->
              <div class="custom-attributes-section">
                <div class="section-header">
                  <h5>Chọn thuộc tính</h5>
                </div>

                <div id="attributeSelection">
                  <?php foreach ($attributes as $attribute): ?>
                    <?php if (!empty($attributeValues[$attribute['id']])): ?>
                      <div class="form-row">
                        <div class="form-group">
                          <label><?php echo htmlspecialchars($attribute['name']); ?></label>
                          <select name="attribute_<?php echo $attribute['id']; ?>" class="form-control" required>
                            <option value="">-- Chọn <?php echo htmlspecialchars($attribute['name']); ?> --</option>
                            <?php foreach ($attributeValues[$attribute['id']] as $value): ?>
                              <option value="<?php echo $value['id']; ?>"
                                <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                  data-color="<?php echo $value['color_code']; ?>"
                                <?php endif; ?>>
                                <?php echo htmlspecialchars($value['value']); ?>
                                <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                  <span class="color-preview" style="background: <?php echo $value['color_code']; ?>"></span>
                                <?php endif; ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Phần thông tin cơ bản -->
              <div class="basic-info-section">
                <h5>Thông tin cơ bản</h5>
                <div class="form-row">
                  <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" placeholder="Tự động tạo nếu để trống">
                  </div>
                  <div class="form-group">
                    <label>Giá *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label>Giá khuyến mãi</label>
                    <input type="number" name="sale_price" step="0.01" min="0">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Số lượng tồn *</label>
                    <input type="number" name="stock_quantity" value="0" min="0" required>
                  </div>
                  <div class="form-group">
                    <label>Trọng lượng (kg)</label>
                    <input type="number" name="weight" step="0.01" min="0">
                  </div>
                  <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                      <input type="checkbox" name="is_default">
                      <span class="checkmark"></span>
                      Biến thể mặc định
                    </label>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm biến thể
              </button>
            </form>
          </div>

          <!-- Danh sách biến thể -->
          <div class="variants-list mt-4">
            <h4>Biến thể hiện có (<?php echo count($productVariants); ?>)</h4>
            <?php if ($productVariants): ?>
              <table class="table">
                <thead>
                <tr>
                  <th>SKU</th>
                  <th>Thuộc tính</th>
                  <th>Giá</th>
                  <th>Giá KM</th>
                  <th>Tồn kho</th>
                  <th>Trọng lượng</th>
                  <th>Mặc định</th>
                  <th width="120">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($productVariants as $variant): ?>
                  <tr id="variant-<?php echo $variant['id']; ?>">
                    <!-- Hiển thị thông tin -->
                    <td class="view-mode">
                      <code><?php echo htmlspecialchars($variant['sku']); ?></code>
                    </td>
                    <td class="view-mode">
                      <?php if (!empty($variant['attributes'])): ?>
                        <div class="variant-attributes">
                          <?php foreach ($variant['attributes'] as $attr): ?>
                            <div class="attribute-display">
                              <strong><?php echo htmlspecialchars($attr['attribute_name']); ?>:</strong>
                              <span class="attribute-value">
                          <?php echo htmlspecialchars($attr['attribute_value']); ?>
                                <?php if ($attr['color_code']): ?>
                                  <span class="color-badge" style="background: <?php echo $attr['color_code']; ?>"></span>
                                <?php endif; ?>
                        </span>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <span class="text-muted">Không có thuộc tính</span>
                      <?php endif; ?>
                    </td>
                    <td class="view-mode">
                      <?php echo number_format($variant['price']); ?>₫
                    </td>
                    <td class="view-mode">
                      <?php if ($variant['sale_price']): ?>
                        <?php echo number_format($variant['sale_price']); ?>₫
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td class="view-mode">
                      <?php echo $variant['stock_quantity']; ?>
                    </td>
                    <td class="view-mode">
                      <?php if ($variant['weight']): ?>
                        <?php echo $variant['weight']; ?>kg
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td class="view-mode">
                      <?php if ($variant['is_default']): ?>
                        <span class="badge primary">Mặc định</span>
                      <?php else: ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="update_variant">
                          <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                          <input type="hidden" name="is_default" value="1">
                          <button type="submit" class="btn-action btn-set-default" title="Đặt làm mặc định">
                            <i class="fas fa-check"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                    </td>
                    <td class="view-mode">
                      <div class="action-buttons">
                        <button type="button" class="btn-action btn-edit" title="Sửa"
                                onclick="toggleEditVariant(<?php echo $variant['id']; ?>)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" onsubmit="return confirm('Xóa biến thể này?')" style="display: inline;">
                          <input type="hidden" name="action" value="delete_variant">
                          <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                          <button type="submit" class="btn-action btn-delete" title="Xóa">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>

                    <!-- Form sửa (ẩn) -->
                    <td colspan="8" class="edit-mode" style="display: none;">
                      <form method="POST" class="edit-variant-form">
                        <input type="hidden" name="action" value="update_variant">
                        <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">

                        <!-- Phần thuộc tính khi sửa -->
                        <div class="attributes-section">
                          <h5>Thuộc tính biến thể</h5>
                          <div class="form-row">
                            <?php foreach ($attributes as $attribute): ?>
                              <?php if (!empty($attributeValues[$attribute['id']])): ?>
                                <div class="form-group">
                                  <label><?php echo htmlspecialchars($attribute['name']); ?></label>
                                  <select name="attribute_<?php echo $attribute['id']; ?>" class="form-control-sm">
                                    <option value="">-- Chọn <?php echo htmlspecialchars($attribute['name']); ?> --</option>
                                    <?php
                                    $currentValue = null;
                                    if (!empty($variant['attributes'])) {
                                      foreach ($variant['attributes'] as $attr) {
                                        if ($attr['attribute_id'] == $attribute['id']) {
                                          $currentValue = $attr['value_id'];
                                          break;
                                        }
                                      }
                                    }
                                    ?>
                                    <?php foreach ($attributeValues[$attribute['id']] as $value): ?>
                                      <option value="<?php echo $value['id']; ?>"
                                        <?php echo $currentValue == $value['id'] ? 'selected' : ''; ?>
                                        <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                          data-color="<?php echo $value['color_code']; ?>"
                                        <?php endif; ?>>
                                        <?php echo htmlspecialchars($value['value']); ?>
                                        <?php if ($attribute['type'] == 'color' && $value['color_code']): ?>
                                          <span class="color-preview" style="background: <?php echo $value['color_code']; ?>"></span>
                                        <?php endif; ?>
                                      </option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>
                              <?php endif; ?>
                            <?php endforeach; ?>
                          </div>
                        </div>

                        <!-- Phần thông tin cơ bản khi sửa -->
                        <div class="basic-info-section">
                          <h5>Thông tin cơ bản</h5>
                          <div class="form-row">
                            <div class="form-group">
                              <label>Giá *</label>
                              <input type="number" name="price" value="<?php echo $variant['price']; ?>" step="0.01" min="0" required class="form-control-sm">
                            </div>
                            <div class="form-group">
                              <label>Giá khuyến mãi</label>
                              <input type="number" name="sale_price" value="<?php echo $variant['sale_price']; ?>" step="0.01" min="0" class="form-control-sm">
                            </div>
                          </div>

                          <!-- Thêm phần chọn ảnh biến thể -->
                          <div class="form-row">
                            <div class="form-group">
                              <label>Ảnh biến thể</label>

                              <!-- Lấy ảnh hiện tại của variant -->
                              <?php
                              $currentImageId = $variant['image_id'] ?? null;
                              $currentImage = null;
                              $currentImageUrl = '';
                              $currentImageAlt = '';

                              if ($currentImageId && !empty($productImages)) {
                                foreach ($productImages as $image) {
                                  if ($image['id'] == $currentImageId) {
                                    $currentImage = $image;
                                    $currentImageUrl = 'img/adminUP/products/' . $image['image_url'];
                                    $currentImageAlt = $image['alt_text'] ?: 'Ảnh ' . $image['id'];
                                    break;
                                  }
                                }
                              }
                              ?>

                              <!-- Nút toggle để mở/đóng danh sách ảnh -->
                              <button type="button" class="btn btn-light btn-block btn-sm mb-2"
                                      onclick="toggleEditImageList(this, <?php echo $variant['id']; ?>)">
                                <i class="fas fa-images mr-1"></i>
                                <?php echo $currentImage ? 'Đổi ảnh' : 'Chọn ảnh'; ?>
                              </button>

                              <!-- Hiển thị ảnh đã chọn và nút hủy -->
                              <div class="selected-image-container mb-2" id="selectedImageContainer_<?php echo $variant['id']; ?>"
                                   style="<?php echo $currentImage ? '' : 'display: none;'; ?>">
                                <div class="selected-image-preview text-center p-2 border rounded bg-light">
                                  <?php if ($currentImage): ?>
                                    <img src="<?php echo $currentImageUrl; ?>"
                                         alt="<?php echo htmlspecialchars($currentImageAlt); ?>"
                                         style="max-width: 60px; max-height: 60px; border: 2px solid #28a745; border-radius: 4px;">
                                    <div class="small text-success mt-1">Đã chọn</div>
                                  <?php endif; ?>
                                </div>

                                <!-- Nút hủy chọn -->
                                <div class="text-center mt-2">
                                  <button type="button" class="btn btn-sm btn-outline-danger"
                                          onclick="clearEditImageSelection(<?php echo $variant['id']; ?>)" title="Hủy chọn">
                                    <i class="fas fa-times mr-1"></i> Hủy chọn
                                  </button>
                                </div>
                              </div>

                              <!-- Thông báo chưa chọn ảnh -->
                              <div class="no-image-selected text-center text-muted small mb-2"
                                   id="noImageSelected_<?php echo $variant['id']; ?>"
                                   style="<?php echo $currentImage ? 'display: none;' : ''; ?>">
                                <i class="fas fa-image"></i> Chưa chọn ảnh
                              </div>

                              <!-- Input hidden chứa image_id -->
                              <input type="hidden" name="image_id"
                                     id="variantImageId_<?php echo $variant['id']; ?>"
                                     value="<?php echo $currentImageId; ?>">

                              <!-- Danh sách ảnh (ẩn mặc định) -->
                              <div class="image-selection-grid" id="imageSelectionGrid_<?php echo $variant['id']; ?>" style="display: none;">
                                <div class="row">
                                  <?php if (!empty($productImages)): ?>
                                    <?php foreach ($productImages as $image): ?>
                                      <?php
                                      $imagePath = 'img/adminUP/products/' . $image['image_url'];
                                      $altText = $image['alt_text'] ?: 'Ảnh ' . $image['id'];
                                      $isSelected = ($image['id'] == $currentImageId);
                                      ?>
                                      <div class="col-4 mb-2">
                                        <div class="image-option-card text-center p-2 border rounded <?php echo $isSelected ? 'selected' : ''; ?>"
                                             onclick="selectEditImage(this, <?php echo $image['id']; ?>, '<?php echo $imagePath; ?>', '<?php echo htmlspecialchars($altText, ENT_QUOTES); ?>', <?php echo $variant['id']; ?>)"
                                             style="cursor: pointer; transition: all 0.2s; <?php echo $isSelected ? 'border-color: #28a745; background-color: rgba(40,167,69,0.1);' : ''; ?>">
                                          <img src="<?php echo $imagePath; ?>"
                                               alt="<?php echo htmlspecialchars($altText); ?>"
                                               class="img-fluid mb-1"
                                               style="width: 60px; height: 60px; object-fit: cover; border-radius: 3px;"
                                               onerror="this.src='img/default-product.jpg'">
                                          <div class="small text-truncate"><?php echo htmlspecialchars($altText); ?></div>
                                          <small class="text-muted">ID: <?php echo $image['id']; ?></small>
                                          <div class="selected-check" style="display: <?php echo $isSelected ? 'block' : 'none'; ?>;">
                                            <i class="fas fa-check-circle text-success"></i>
                                          </div>
                                        </div>
                                      </div>
                                    <?php endforeach; ?>
                                  <?php else: ?>
                                    <div class="col-12">
                                      <div class="alert alert-light text-center py-3">
                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Không có ảnh nào</p>
                                      </div>
                                    </div>
                                  <?php endif; ?>
                                </div>

                                <!-- Nút đóng và hủy -->
                                <div class="text-center mt-2">
                                  <button type="button" class="btn btn-sm btn-outline-secondary mr-2"
                                          onclick="closeEditImageList(<?php echo $variant['id']; ?>)">
                                    <i class="fas fa-times mr-1"></i> Đóng
                                  </button>
                                  <button type="button" class="btn btn-sm btn-outline-danger"
                                          onclick="clearEditImageSelection(<?php echo $variant['id']; ?>)">
                                    <i class="fas fa-undo mr-1"></i> Hủy chọn
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="form-row">
                            <div class="form-group">
                              <label>Số lượng tồn</label>
                              <input type="number" name="stock_quantity" value="<?php echo $variant['stock_quantity']; ?>" min="0" class="form-control-sm">
                            </div>
                            <div class="form-group">
                              <label>Trọng lượng (kg)</label>
                              <input type="number" name="weight" value="<?php echo $variant['weight']; ?>" step="0.01" min="0" class="form-control-sm">
                            </div>
                            <div class="form-group checkbox-group">
                              <label class="checkbox-label">
                                <input type="checkbox" name="is_default" <?php echo $variant['is_default'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Biến thể mặc định
                              </label>
                            </div>
                          </div>
                        </div>

                        <div class="form-actions">
                          <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Lưu thay đổi
                          </button>
                          <button type="button" class="btn btn-secondary btn-sm"
                                  onclick="toggleEditVariant(<?php echo $variant['id']; ?>)">
                            <i class="fas fa-times"></i> Hủy
                          </button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p class="no-data">Chưa có biến thể nào</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- JavaScript cho tab Biến thể -->
      <script>
        // Biến lưu trữ thông tin để tạo SKU tự động
        let variantCounter = <?php echo count($productVariants); ?>;

        // Hàm tạo SKU tự động dựa trên product SKU
        function generateAutoSKU(index) {
          const productName = "<?php echo isset($product['sku']) ? $product['sku'] : 'PROD'; ?>";
          const variantNumber = variantCounter + index + 1;
          return `${productName}-${variantNumber}`;
        }

        // Hàm lấy giá trị từ dòng cuối để làm mẫu
        function getLastVariantValues() {
          const rows = document.querySelectorAll(".variant-row");
          if (rows.length === 0) {
            return {
              price: 0,
              sale_price: '',
              stock_quantity: 0,
              weight: ''
            };
          }

          const lastRow = rows[rows.length - 1];
          return {
            price: lastRow.querySelector("input[name*='price']")?.value || 0,
            sale_price: lastRow.querySelector("input[name*='sale_price']")?.value || '',
            stock_quantity: lastRow.querySelector("input[name*='stock_quantity']")?.value || 0,
            weight: lastRow.querySelector("input[name*='weight']")?.value || ''
          };
        }

        // Hàm thêm dòng biến thể mới
        function addVariantRow() {
          const container = document.getElementById("variants-container");
          const template = document.getElementById("variant-row-template");
          const clone = template.content.cloneNode(true);

          // Lấy index mới
          const index = container.querySelectorAll(".variant-row").length;

          // Lấy giá trị từ dòng cuối để copy
          const lastValues = getLastVariantValues();

          // Cập nhật tên input với index mới
          clone.querySelectorAll("[name]").forEach(input => {
            const originalName = input.name;
            input.name = originalName.replace("__INDEX__", index);

            // Set giá trị mặc định
            if (originalName.includes("sku")) {
              input.value = generateAutoSKU(index);
            } else if (originalName.includes("price")) {
              input.value = parseFloat(lastValues.price) || 0;
            } else if (originalName.includes("sale_price")) {
              input.value = lastValues.sale_price;
            } else if (originalName.includes("stock_quantity")) {
              input.value = parseInt(lastValues.stock_quantity) || 0;
            } else if (originalName.includes("weight")) {
              input.value = lastValues.weight;
            }
          });

          // Cập nhật số thứ tự hiển thị
          const variantIndexSpan = clone.querySelector(".variant-index");
          if (variantIndexSpan) {
            variantIndexSpan.textContent = `#${index + 1}`;
          }

          container.appendChild(clone);

          // Thêm event listeners cho preview realtime
          setupVariantPreviewEvents(clone, index);

          // Focus vào ô đầu tiên
          setTimeout(() => {
            const firstSelect = clone.querySelector("select");
            if (firstSelect) {
              firstSelect.focus();
            }
          }, 10);
        }

        // Hàm xóa dòng biến thể
        function removeVariantRow(button) {
          if (confirm("Xóa biến thể này?")) {
            const row = button.closest(".variant-row");
            if (row) {
              row.remove();

              // Đánh lại index cho các dòng còn lại
              renumberVariantRows();
            }
          }
        }

        // Đánh lại số thứ tự các dòng
        function renumberVariantRows() {
          const container = document.getElementById("variants-container");
          const rows = container.querySelectorAll(".variant-row");

          rows.forEach((row, index) => {
            // Cập nhật tên input
            row.querySelectorAll("[name]").forEach(input => {
              const oldName = input.name;
              const newName = oldName.replace(/variants\[\d+\]/, `variants[${index}]`);
              input.name = newName;
            });

            // Cập nhật số thứ tự hiển thị
            const variantIndexSpan = row.querySelector(".variant-index");
            if (variantIndexSpan) {
              variantIndexSpan.textContent = `#${index + 1}`;
            }

            // Cập nhật preview
            updateVariantPreview(row, index);
          });
        }

        // Thiết lập event listeners cho preview
        function setupVariantPreviewEvents(row, index) {
          // Theo dõi thay đổi để cập nhật preview
          const inputs = row.querySelectorAll("input, select");
          inputs.forEach(input => {
            input.addEventListener("input", () => updateVariantPreview(row, index));
            input.addEventListener("change", () => updateVariantPreview(row, index));
          });
        }

        // Cập nhật preview thông tin biến thể
        function updateVariantPreview(row, index) {
          const previewEl = row.querySelector(".variant-preview");
          if (!previewEl) return;

          // Lấy thông tin thuộc tính
          const attributes = [];
          const selects = row.querySelectorAll("select");
          selects.forEach(select => {
            if (select.value) {
              const label = select.previousElementSibling?.textContent?.trim() || '';
              const selectedOption = select.options[select.selectedIndex];
              const value = selectedOption.textContent.split('<')[0].trim();
              attributes.push(`${label}: ${value}`);
            }
          });

          // Lấy thông tin cơ bản
          const price = row.querySelector("input[name*='price']")?.value || 0;
          const stock = row.querySelector("input[name*='stock_quantity']")?.value || 0;

          // Cập nhật preview
          const previewAttributes = previewEl.querySelector(".preview-attributes");
          const previewPrice = previewEl.querySelector(".preview-price");
          const previewStock = previewEl.querySelector(".preview-stock");

          if (previewAttributes) {
            previewAttributes.textContent = attributes.length > 0
              ? attributes.join(", ")
              : "Chưa chọn thuộc tính";
          }
          if (previewPrice) {
            previewPrice.textContent = parseFloat(price).toLocaleString();
          }
          if (previewStock) {
            previewStock.textContent = stock;
          }
        }

        // Hàm kiểm tra trùng lặp thuộc tính
        function checkDuplicateVariants() {
          const rows = document.querySelectorAll(".variant-row");
          const variantSignatures = [];
          const duplicates = [];

          rows.forEach((row, index) => {
            const selects = row.querySelectorAll("select");
            const signature = [];

            selects.forEach(select => {
              if (select.value) {
                signature.push(`${select.name}:${select.value}`);
              }
            });

            // Sắp xếp để so sánh
            signature.sort();
            const sigString = signature.join('|');

            if (sigString) {
              if (variantSignatures.includes(sigString)) {
                duplicates.push(index + 1);
              } else {
                variantSignatures.push(sigString);
              }
            }
          });

          return duplicates;
        }

        // Hàm validate tất cả biến thể
        function validateAllVariants() {
          const rows = document.querySelectorAll(".variant-row");
          const errors = [];

          rows.forEach((row, index) => {
            const priceInput = row.querySelector("input[name*='price']");
            const stockInput = row.querySelector("input[name*='stock_quantity']");

            // Kiểm tra giá
            if (!priceInput.value || parseFloat(priceInput.value) <= 0) {
              errors.push(`Dòng ${index + 1}: Giá phải lớn hơn 0`);
            }

            // Kiểm tra tồn kho
            if (!stockInput.value || parseInt(stockInput.value) < 0) {
              errors.push(`Dòng ${index + 1}: Số lượng tồn không hợp lệ`);
            }
          });

          return errors;
        }

        // Hàm submit tất cả biến thể
        function submitAllVariants() {
          // Kiểm tra validate
          const validationErrors = validateAllVariants();
          if (validationErrors.length > 0) {
            alert("Lỗi kiểm tra:\n\n" + validationErrors.join("\n"));
            return;
          }

          // Kiểm tra trùng lặp
          const duplicates = checkDuplicateVariants();
          if (duplicates.length > 0) {
            const continueAnyway = confirm(`Cảnh báo: Các biến thể ở dòng ${duplicates.join(', ')} có thể bị trùng lặp thuộc tính.\nTiếp tục?`);
            if (!continueAnyway) return;
          }

          // Lấy form data
          const form = document.getElementById("bulk-variant-form");
          const formData = new FormData(form);

          // Hiển thị trạng thái loading
          const status = document.getElementById("variant-status");
          status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
          status.className = "ml-2 text-info";

          // Gửi request
          fetch(window.location.href, {
            method: "POST",
            body: formData
          })
            .then(response => response.text())
            .then(data => {
              // Tìm thông báo trong response
              if (data.includes("alert")) {
                const match = data.match(/alert\("([^"]+)"\)/);
                if (match) {
                  status.innerHTML = `<i class="fas fa-check"></i> ${match[1]}`;
                  status.className = "ml-2 text-success";

                  // Xóa tất cả dòng sau khi lưu thành công
                  const container = document.getElementById("variants-container");
                  container.innerHTML = '';
                  variantCounter += container.querySelectorAll(".variant-row").length;

                  // Reload trang sau 2 giây
                  setTimeout(() => {
                    window.location.reload();
                  }, 2000);
                }
              } else {
                status.innerHTML = '<i class="fas fa-check"></i> Thêm thành công!';
                status.className = "ml-2 text-success";

                // Xóa form
                const container = document.getElementById("variants-container");
                container.innerHTML = '';
                variantCounter = <?php echo count($productVariants); ?>;

                setTimeout(() => {
                  window.location.reload();
                }, 2000);
              }
            })
            .catch(error => {
              status.innerHTML = '<i class="fas fa-times"></i> Thất bại!';
              status.className = "ml-2 text-danger";
              console.error("Error:", error);
            });
        }

        // Hàm toggle form sửa biến thể (giữ nguyên từ code cũ)
        function toggleEditVariant(variantId) {
          const row = document.getElementById("variant-" + variantId);
          if (!row) return;

          const viewCells = row.querySelectorAll(".view-mode");
          const editCell = row.querySelector(".edit-mode");

          if (editCell.style.display === "none") {
            // Chuyển sang mode edit
            viewCells.forEach(cell => cell.style.display = "none");
            editCell.style.display = "table-cell";
            editCell.colSpan = 8;
          } else {
            // Chuyển về mode view
            viewCells.forEach(cell => cell.style.display = "");
            editCell.style.display = "none";
          }
        }

        // Khởi tạo khi trang load
        document.addEventListener("DOMContentLoaded", function() {
          // Khởi tạo 1 dòng biến thể mặc định
          addVariantRow();

          // Thêm CSS cho giao diện
          const style = document.createElement("style");
          style.textContent = `
        .variant-row {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .variant-row .card-header {
            background: #f8f9fa;
            padding: 8px 15px;
        }
        .variant-preview {
            font-size: 12px;
        }
        .color-preview, .color-badge {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-left: 5px;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
        .attribute-display {
            margin-bottom: 3px;
            font-size: 12px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .badge.primary {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
    `;
          document.head.appendChild(style);
        });
      </script>
      <?php break; ?>

      <?php case 'reviews': ?>
        <!-- Tab Đánh giá -->
        <div class="card">
          <div class="card-header">
            <h3>Quản lý đánh giá sản phẩm</h3>
            <?php if ($ratingStats): ?>
              <div class="rating-stats">
                <span class="avg-rating"><?php echo number_format($ratingStats['avg_rating'], 1); ?></span>
                <div class="stars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= round($ratingStats['avg_rating']) ? 'active' : ''; ?>"></i>
                  <?php endfor; ?>
                </div>
                <span class="total-reviews">(<?php echo $ratingStats['total_reviews']; ?> đánh giá)</span>
              </div>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <?php if ($productReviews): ?>
              <div class="reviews-list">
                <?php foreach ($productReviews as $review): ?>
                  <div class="review-item <?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                    <div class="review-header">
                      <div class="reviewer-info">
                        <strong><?php echo htmlspecialchars(isset($review['username']) ? $review['username'] : 'Khách'); ?></strong>
                        <div class="stars">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                          <?php endfor; ?>
                        </div>
                      </div>
                      <div class="review-meta">
                        <span class="review-date"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></span>
                        <span class="review-status <?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                                                    <?php echo $review['is_approved'] ? 'Đã duyệt' : 'Chờ duyệt'; ?>
                                                </span>
                      </div>
                    </div>
                    <?php if ($review['title']): ?>
                      <h4 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h4>
                    <?php endif; ?>
                    <?php if ($review['comment']): ?>
                      <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <?php endif; ?>
                    <div class="review-actions">
                      <?php if (!$review['is_approved']): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="approve_review">
                          <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i> Duyệt
                          </button>
                        </form>
                      <?php endif; ?>
                      <form method="POST" style="display: inline;" onsubmit="return confirm('Xóa đánh giá này?')">
                        <input type="hidden" name="action" value="delete_review">
                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="no-data">Chưa có đánh giá nào</p>
            <?php endif; ?>
          </div>
        </div>
        <?php break; ?>
      <?php endswitch; ?>
  </div>
</div>

<style>
  .product-header {
    flex: 1;
  }

  .product-header h2 {
    margin: 5px 0;
    color: #2c3e50;
    font-size: 1.8rem;
  }

  .product-meta {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
  }

  /* Tabs */
  .tabs {
    display: flex;
    background: white;
    border-radius: 10px;
    padding: 0;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    border: 1px solid #e9ecef;
  }

  .tab {
    padding: 15px 25px;
    text-decoration: none;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    background: #f8f9fa;
  }

  .tab:hover {
    color: #495057;
    background: #e9ecef;
  }

  .tab.active {
    color: #3498db;
    border-bottom-color: #3498db;
    background: white;
    font-weight: 600;
  }

  .tab .badge {
    background: #e74c3c;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
  }

  /* Tab content */
  .tab-content {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    overflow: hidden;
  }

  /* Card styles */
  .card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 0;
    border: none;
  }

  .card-header {
    background: #f8f9fa;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .card-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.4rem;
    flex: 1;
  }

  .card-body {
    padding: 25px;
  }

  /* Images grid */
  .images-grid {
    margin-top: 0;
  }

  .images-grid h4 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.2rem;
    font-weight: 600;
  }

  .images-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
    margin-top: 0;
  }

  .image-item {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
    background: #fafbfc;
  }

  .image-item:hover {
    border-color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
  }

  .image-item.main-image {
    border-color: #3498db;
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
  }

  .image-item img {
    max-width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  .image-actions {
    margin-top: 12px;
    display: flex;
    justify-content: center;
    gap: 8px;
  }

  .main-badge {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
  }

  .image-info {
    margin-top: 8px;
  }

  .image-info small {
    color: #6c757d;
    font-size: 12px;
  }

  .btn-set-main {
    background: #fff3e0;
    color: #f57c00;
    border: 1px solid #ffe0b2;
  }

  .btn-set-main:hover {
    background: #ffe0b2;
    border-color: #f57c00;
  }

  /* Rating */
  .rating-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  .avg-rating {
    font-size: 24px;
    font-weight: bold;
    color: #f39c12;
  }

  .stars {
    display: flex;
    gap: 2px;
  }

  .stars .fa-star {
    color: #ddd;
    font-size: 16px;
  }

  .stars .fa-star.active {
    color: #f39c12;
  }

  .total-reviews {
    color: #6c757d;
    font-size: 14px;
  }

  /* Reviews */
  .review-item {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    background: #fafbfc;
    transition: all 0.3s ease;
  }

  .review-item:hover {
    border-color: #3498db;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .review-item.pending {
    background: linear-gradient(135deg, #fff3e0 0%, #fff8e1 100%);
    border-color: #ffe0b2;
  }

  .review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
  }

  .reviewer-info strong {
    color: #2c3e50;
    font-size: 16px;
  }

  .reviewer-info .stars {
    margin-top: 6px;
  }

  .review-meta {
    text-align: right;
  }

  .review-date {
    color: #6c757d;
    font-size: 13px;
    display: block;
  }

  .review-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 5px;
  }

  .review-status.approved {
    background: #e8f5e8;
    color: #388e3c;
    border: 1px solid #c8e6c9;
  }

  .review-status.pending {
    background: #fff3e0;
    color: #f57c00;
    border: 1px solid #ffe0b2;
  }

  .review-title {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #2c3e50;
    font-weight: 600;
  }

  .review-comment {
    margin: 0 0 15px 0;
    color: #555;
    line-height: 1.5;
  }

  .review-actions {
    display: flex;
    gap: 10px;
  }

  /* Common */
  .no-data {
    text-align: center;
    color: #6c757d;
    padding: 40px;
    font-style: italic;
    background: #fafbfc;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
  }

  .upload-section,
  .add-spec-section,
  .add-variant-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
  }

  .upload-section h4,
  .add-spec-section h4,
  .add-variant-section h4 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.2rem;
    font-weight: 600;
  }

  .upload-form,
  .spec-form,
  .variant-form {
    max-width: 600px;
  }

  /* Form styles */
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #2c3e50;
    font-size: 14px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
  }

  .checkbox-group {
    margin: 0;
    padding-top: 25px;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, #2573a7);
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    transform: translateY(-1px);
  }

  .btn-secondary {
    background: #6c757d;
    color: white;
  }

  .btn-secondary:hover {
    background: #5a6268;
  }

  .btn-sm {
    padding: 8px 16px;
    font-size: 12px;
    border-radius: 6px;
  }

  .btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
  }

  .btn-success:hover {
    background: linear-gradient(135deg, #218838, #1e7e34);
  }

  .btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
  }

  .btn-danger:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
  }

  /* Action buttons */
  .action-buttons {
    display: flex;
    gap: 5px;
  }

  .btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
  }

  .btn-edit {
    background: #e3f2fd;
    color: #1976d2;
    border-color: #bbdefb;
  }

  .btn-edit:hover {
    background: #bbdefb;
    border-color: #1976d2;
  }

  .btn-delete {
    background: #ffebee;
    color: #d32f2f;
    border-color: #ffcdd2;
  }

  .btn-delete:hover {
    background: #ffcdd2;
    border-color: #d32f2f;
  }

  .btn-set-default {
    background: #e8f5e8;
    color: #388e3c;
    border-color: #c8e6c9;
  }

  .btn-set-default:hover {
    background: #c8e6c9;
    border-color: #388e3c;
  }

  /* Table styles */
  .table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }

  .table th,
  .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
  }

  .table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
  }

  .table tbody tr:hover {
    background: #f8f9fa;
  }

  /* Badge */
  .badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge.primary {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
  }

  /* Text */
  .text-muted {
    color: #6c757d;
    font-size: 12px;
  }

   .attributes-section {
     background: #f8f9fa;
     padding: 15px;
     border-radius: 8px;
     margin-bottom: 20px;
     border: 1px solid #e9ecef;
   }

  .attributes-section h5 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
  }

  .variant-attributes {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .attribute-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    border: 1px solid #bbdefb;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    border: 1px solid #ddd;
  }

  .color-preview {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    margin-left: 5px;
    border: 1px solid #ddd;
    vertical-align: middle;
  }

  .basic-info-section {
    margin-bottom: 20px;
  }

  .basic-info-section h5 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
  }



  .selected-image-container {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 10px;
    background: #f8f9fa;
  }

  .selected-image-preview {
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
  }

  .selected-image-preview img {
    transition: transform 0.3s;
  }

  .selected-image-preview img:hover {
    transform: scale(1.05);
  }

  .no-image-selected {
    padding: 15px;
    border: 1px dashed #dee2e6;
    border-radius: 6px;
    background: #f8f9fa;
  }

  .image-option-card.selected {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 2px rgba(40,167,69,0.2);
  }

  .image-option-card .selected-check {
    position: absolute;
    top: 5px;
    right: 5px;
    background: white;
    border-radius: 50%;
    padding: 2px;
  }

  .btn-clear-selection {
    font-size: 12px;
    padding: 2px 8px;
  }

  /*.form-control {*/
  /*  border-radius: 10px;*/
  /*  padding: 10px;*/
  /*  border: 1px solid #bdbdbd;*/
  /*  margin-bottom: 5px;*/
  /*}*/
</style>

<script>
  // Toggle edit form cho ảnh
  function toggleEdit(imageId) {
    const imageItem = document.getElementById('image-' + imageId);
    const editForm = document.getElementById('edit-form-' + imageId);

    if (editForm.style.display === 'none') {
      editForm.style.display = 'block';
    } else {
      editForm.style.display = 'none';
    }
  }

  // Toggle edit form cho thông số
  function toggleEditSpec(specId) {
    const row = document.getElementById('spec-' + specId);
    const viewCells = row.querySelectorAll('.view-mode');
    const editCell = row.querySelector('.edit-mode');

    if (editCell.style.display === 'none') {
      viewCells.forEach(cell => cell.style.display = 'none');
      editCell.style.display = 'table-cell';
      editCell.colSpan = 4;
    } else {
      viewCells.forEach(cell => cell.style.display = 'table-cell');
      editCell.style.display = 'none';
    }
  }
</script>
<script>
  // Debug form submission
  document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.variant-form, .edit-variant-form');

    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        console.log('Form submitted:', this);
        console.log('Form data:', new FormData(this));

        // Kiểm tra xem có attributes nào được chọn không
        const attributeSelects = this.querySelectorAll('select[name^="attribute_"]');
        let hasAttributes = false;

        attributeSelects.forEach(select => {
          if (select.value) {
            hasAttributes = true;
            console.log('Attribute selected:', select.name, '=', select.value);
          }
        });

        if (!hasAttributes) {
          if (!confirm('Bạn chưa chọn thuộc tính nào. Bạn có muốn tiếp tục không?')) {
            e.preventDefault();

          }
        }
      });
    });
  });

  // Đóng tất cả form edit khác khi mở form mới
  function closeOtherEditForms(currentVariantId) {
    const allRows = document.querySelectorAll('tr[id^="variant-"]');
    allRows.forEach(row => {
      const rowVariantId = row.id.replace('variant-', '');
      if (rowVariantId !== currentVariantId) {
        const viewCells = row.querySelectorAll('.view-mode');
        const editCell = row.querySelector('.edit-mode');

        viewCells.forEach(cell => cell.style.display = 'table-cell');
        if (editCell) editCell.style.display = 'none';
      }
    });
  }

  // Sửa lại hàm toggleEditVariant để đóng form khác
  function toggleEditVariant(variantId) {
    document.querySelectorAll('.image-selection-grid').forEach(grid => {
      grid.style.display = 'none';
    });

    // Reset tất cả nút toggle
    document.querySelectorAll('button[onclick^="toggleEditImageList"]').forEach(btn => {
      const match = btn.getAttribute('onclick').match(/toggleEditImageList\(this, (\d+)\)/);
      if (match) {
        const vid = match[1];
        const hasImage = document.getElementById('variantImageId_' + vid)?.value;
        btn.innerHTML = '<i class="fas fa-images mr-1"></i> ' + (hasImage ? 'Đổi ảnh' : 'Chọn ảnh');
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-light');
      }
    });

    closeOtherEditForms(variantId);

    const row = document.getElementById('variant-' + variantId);
    const viewCells = row.querySelectorAll('.view-mode');
    const editCell = row.querySelector('.edit-mode');

    if (editCell.style.display === 'none') {
      viewCells.forEach(cell => cell.style.display = 'none');
      editCell.style.display = 'table-cell';
      editCell.colSpan = 8;
    } else {
      viewCells.forEach(cell => cell.style.display = 'table-cell');
      editCell.style.display = 'none';
    }
  }

  function addAttributeField() {
    const attributeFields = document.getElementById('attributeFields');
    const newField = document.createElement('div');
    newField.className = 'attribute-field-row';
    newField.innerHTML = `
    <div class="form-row">
      <div class="form-group">
        <label>Tên thuộc tính</label>
        <input type="text" name="attribute_names[]" placeholder="VD: Màu sắc, Kích thước">
      </div>
      <div class="form-group">
        <label>Giá trị</label>
        <input type="text" name="attribute_values[]" placeholder="VD: Đỏ, XL">
      </div>
      <div class="form-group">
        <label>Mã màu (nếu là màu)</label>
        <input type="color" name="attribute_colors[]" class="color-input">
      </div>
      <div class="form-group">
        <button type="button" class="btn btn-danger btn-sm" onclick="removeAttributeField(this)" style="margin-top: 25px;">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  `;
    attributeFields.appendChild(newField);
  }

  // Xóa trường thuộc tính
  function removeAttributeField(button) {
    const fieldRow = button.closest('.attribute-field-row');
    if (document.querySelectorAll('.attribute-field-row').length > 1) {
      fieldRow.remove();
    } else {
      alert('Cần ít nhất một thuộc tính!');
    }
  }

  // Auto-detect color fields and show color input
  document.addEventListener('input', function(e) {
    if (e.target.name === 'attribute_names[]') {
      const value = e.target.value.toLowerCase();
      const colorInput = e.target.closest('.form-row').querySelector('.color-input');
      if (value.includes('màu') || value.includes('color')) {
        colorInput.style.display = 'block';
        colorInput.previousElementSibling.style.display = 'block';
      } else {
        colorInput.style.display = 'none';
        colorInput.previousElementSibling.style.display = 'none';
      }
    }
  });
</script>

<!--up nhiều ảnh-->
<script>
  // Hàm thêm form upload ảnh mới
  function addImageUploadForm() {
    const container = document.getElementById("multiple-upload-container");
    const template = document.getElementById("image-upload-template");
    const clone = template.content.cloneNode(true);

    // Tăng số thứ tự
    const index = container.querySelectorAll(".image-upload-row").length;

    // Cập nhật tên và ID cho tất cả các input
    clone.querySelectorAll("[name]").forEach(input => {
      const originalName = input.name;
      input.name = originalName.replace("[]", "[" + index + "]");

      // Cập nhật ID để preview ảnh
      if (input.type === "file") {
        const previewId = "image-preview-" + index;
        input.setAttribute("onchange", "previewImage(this, \'" + previewId + "\')");
        input.id = "image-input-" + index;
      }
    });

    // Cập nhật ID cho preview img
    const previewImg = clone.querySelector(".image-preview");
    previewImg.id = "image-preview-" + index;

    // Cập nhật ID cho checkbox
    const checkbox = clone.querySelector("input[type=\'checkbox\']");
    if (checkbox) {
      checkbox.id = "is-main-" + index;
      const label = clone.querySelector("label[for=\'is-main\']");
      if (label) {
        label.setAttribute("for", "is-main-" + index);
      }
    }

    container.appendChild(clone);

    // Cuộn đến form mới thêm
    const newRow = container.lastElementChild;
    newRow.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  // Hàm xóa form upload
  function removeUploadForm(button) {
    const row = button.closest(".image-upload-row");
    if (row) {
      row.remove();

      // Đánh lại index cho các form còn lại
      const container = document.getElementById("multiple-upload-container");
      const rows = container.querySelectorAll(".image-upload-row");

      rows.forEach((row, index) => {
        const inputs = row.querySelectorAll("[name]");
        inputs.forEach(input => {
          const name = input.name;
          // Tìm và thay thế index cũ bằng index mới
          const newName = name.replace(/\[\d+\]/, "[" + index + "]");
          input.name = newName;
        });

        // Cập nhật ID cho file input và preview
        const fileInput = row.querySelector("input[type=\'file\']");
        if (fileInput) {
          const previewId = "image-preview-" + index;
          fileInput.setAttribute("onchange", "previewImage(this, \'" + previewId + "\')");
          fileInput.id = "image-input-" + index;

          const previewImg = row.querySelector(".image-preview");
          if (previewImg) {
            previewImg.id = previewId;
          }
        }

        // Cập nhật ID cho checkbox
        const checkbox = row.querySelector("input[type=\'checkbox\']");
        if (checkbox) {
          checkbox.id = "is-main-" + index;
          const label = row.querySelector("label[for^=\'is-main\']");
          if (label) {
            label.setAttribute("for", "is-main-" + index);
          }
        }
      });
    }
  }

  // Hàm preview ảnh
  function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = "block";
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.style.display = "none";
    }
  }

  // Hàm toggle form sửa ảnh (giữ nguyên từ code cũ)
  function toggleEdit(imageId) {
    const editForm = document.getElementById("edit-form-" + imageId);
    const imageItem = document.getElementById("image-" + imageId);

    if (editForm.style.display === "none" || !editForm.style.display) {
      editForm.style.display = "block";
      imageItem.classList.add("editing");
    } else {
      editForm.style.display = "none";
      imageItem.classList.remove("editing");
    }
  }

  function getLastSortOrder() {
    const rows = document.querySelectorAll(".spec-row");
    if (rows.length === 0) return 0;

    const lastRow = rows[rows.length - 1];
    const lastSortInput = lastRow.querySelector("input[name*='[sort_order]']");

    if (lastSortInput) {
      return parseInt(lastSortInput.value) || 0;
    }

    return 0;
  }

  // Thêm CSS động
  document.addEventListener("DOMContentLoaded", function() {
    const style = document.createElement("style");
    style.textContent = `
        .image-upload-row {
            transition: all 0.3s ease;
            border: 2px dashed #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .image-upload-row:hover {
            border-color: #007bff;
            background: #e7f3ff;
        }
        .image-preview {
            max-width: 120px;
            max-height: 120px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 3px;
        }
        .btn-remove-upload {
            margin-top: 10px;
        }
        .upload-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .btn-add-more {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .image-item.editing {
            border: 2px solid #28a745;
            background: #f0fff4;
        }
    `;
    document.head.appendChild(style);
  });




  // Mở/đóng danh sách ảnh
  function toggleImageList(button) {
    const container = button.closest('.form-group');
    const imageGrid = container.querySelector('.image-selection-grid');

    // Toggle hiển thị
    if (imageGrid.style.display === 'none') {
      imageGrid.style.display = 'block';
      button.innerHTML = '<i class="fas fa-times mr-1"></i> Đóng chọn ảnh';
      button.classList.remove('btn-light');
      button.classList.add('btn-outline-secondary');
    } else {
      imageGrid.style.display = 'none';
      button.innerHTML = '<i class="fas fa-images mr-1"></i> Chọn ảnh';
      button.classList.remove('btn-outline-secondary');
      button.classList.add('btn-light');
    }
  }

  // Đóng danh sách ảnh
  function closeImageList(button) {
    const container = button.closest('.form-group');
    const toggleBtn = container.querySelector('button[onclick^="toggleImageList"]');
    toggleImageList(toggleBtn);
  }

  // Chọn ảnh
  function selectImage(card, imageId, imageUrl, imageAlt) {
    const container = card.closest('.form-group');

    // Reset tất cả cards trong container này
    container.querySelectorAll('.image-option-card').forEach(c => {
      c.style.borderColor = '#dee2e6';
      c.style.backgroundColor = '';
      c.querySelector('.selected-check').style.display = 'none';
    });

    // Highlight card được chọn
    card.style.borderColor = '#28a745';
    card.style.backgroundColor = 'rgba(40,167,69,0.1)';
    card.querySelector('.selected-check').style.display = 'block';

    // Cập nhật input hidden
    const hiddenInput = container.querySelector('.variant-image-id');
    hiddenInput.value = imageId;

    // Cập nhật preview
    const selectedContainer = container.querySelector('.selected-image-container');
    const noImageDiv = container.querySelector('.no-image-selected');

    selectedContainer.style.display = 'block';
    noImageDiv.style.display = 'none';

    selectedContainer.querySelector('img').src = imageUrl;
    selectedContainer.querySelector('img').alt = imageAlt;

    // Đóng danh sách sau 0.5 giây
    setTimeout(() => {
      closeImageList(card);
    }, 500);
  }

  // Hủy chọn ảnh (từ preview)
  function clearImageSelection(button) {
    const container = button.closest('.form-group');
    clearSelectionInContainer(container);
  }

  // Hủy chọn ảnh (từ trong danh sách)
  function clearImageSelectionFromList(button) {
    const container = button.closest('.form-group');
    clearSelectionInContainer(container);
    closeImageList(button);
  }

  // Hàm chung để hủy chọn
  function clearSelectionInContainer(container) {
    // Reset input hidden
    const hiddenInput = container.querySelector('.variant-image-id');
    hiddenInput.value = '';

    // Ẩn container đã chọn, hiện thông báo chưa chọn
    const selectedContainer = container.querySelector('.selected-image-container');
    const noImageDiv = container.querySelector('.no-image-selected');

    selectedContainer.style.display = 'none';
    noImageDiv.style.display = 'block';

    // Reset tất cả cards
    container.querySelectorAll('.image-option-card').forEach(card => {
      card.style.borderColor = '#dee2e6';
      card.style.backgroundColor = '';
      card.querySelector('.selected-check').style.display = 'none';
    });

    // Reset nút toggle
    const toggleBtn = container.querySelector('button[onclick^="toggleImageList"]');
    toggleBtn.innerHTML = '<i class="fas fa-images mr-1"></i> Chọn ảnh';
    toggleBtn.classList.remove('btn-outline-secondary');
    toggleBtn.classList.add('btn-light');
  }

  // Xử lý khi thêm variant mới
  if (typeof addNewVariant === 'function') {
    const originalAddNewVariant = addNewVariant;
    window.addNewVariant = function() {
      const newIndex = originalAddNewVariant();

      // Gắn sự kiện cho các phần tử mới
      const newContainer = document.querySelector('.basic-info-section:last-child');

      // Gắn sự kiện cho các image cards mới
      newContainer.querySelectorAll('.image-option-card').forEach(card => {
        card.onclick = function() {
          const imageId = this.getAttribute('onclick').match(/selectImage\(this, (\d+),/)[1];
          const imageUrl = this.getAttribute('onclick').match(/'([^']+)'/)[1];
          const imageAlt = this.getAttribute('onclick').match(/'([^']+)'/)[3];
          selectImage(this, imageId, imageUrl, imageAlt);
        };
      });

      // Gắn sự kiện cho nút hủy
      const clearButtons = newContainer.querySelectorAll('[onclick^="clearImageSelection"]');
      clearButtons.forEach(btn => {
        btn.onclick = function() { clearImageSelection(this); };
      });

      return newIndex;
    };
  }



  // ========== FUNCTIONS CHO FORM SỬA ==========

  // Mở/đóng danh sách ảnh trong form sửa
  function toggleEditImageList(button, variantId) {
    const container = button.closest('.form-group');
    const imageGrid = document.getElementById('imageSelectionGrid_' + variantId);

    // Đóng tất cả grid khác trước
    document.querySelectorAll('.image-selection-grid').forEach(grid => {
      if (grid.id !== 'imageSelectionGrid_' + variantId) {
        grid.style.display = 'none';
        const correspondingBtn = grid.closest('.form-group').querySelector('button[onclick^="toggleEditImageList"]');
        if (correspondingBtn) {
          correspondingBtn.innerHTML = '<i class="fas fa-images mr-1"></i> ' +
            (document.getElementById('variantImageId_' + variantId.replace('imageSelectionGrid_', '')).value ? 'Đổi ảnh' : 'Chọn ảnh');
          correspondingBtn.classList.remove('btn-outline-secondary');
          correspondingBtn.classList.add('btn-light');
        }
      }
    });

    // Toggle hiển thị
    if (imageGrid.style.display === 'none') {
      imageGrid.style.display = 'block';
      button.innerHTML = '<i class="fas fa-times mr-1"></i> Đóng chọn ảnh';
      button.classList.remove('btn-light');
      button.classList.add('btn-outline-secondary');
    } else {
      imageGrid.style.display = 'none';
      button.innerHTML = '<i class="fas fa-images mr-1"></i> ' +
        (document.getElementById('variantImageId_' + variantId).value ? 'Đổi ảnh' : 'Chọn ảnh');
      button.classList.remove('btn-outline-secondary');
      button.classList.add('btn-light');
    }
  }

  // Đóng danh sách ảnh trong form sửa
  function closeEditImageList(variantId) {
    const button = document.querySelector('#imageSelectionGrid_' + variantId).closest('.form-group').querySelector('button[onclick^="toggleEditImageList"]');
    toggleEditImageList(button, variantId);
  }

  // Chọn ảnh trong form sửa
  function selectEditImage(card, imageId, imageUrl, imageAlt, variantId) {
    const container = card.closest('.form-group');

    // Reset tất cả cards trong container này
    container.querySelectorAll('.image-option-card').forEach(c => {
      c.style.borderColor = '#dee2e6';
      c.style.backgroundColor = '';
      c.querySelector('.selected-check').style.display = 'none';
      c.classList.remove('selected');
    });

    // Highlight card được chọn
    card.style.borderColor = '#28a745';
    card.style.backgroundColor = 'rgba(40,167,69,0.1)';
    card.classList.add('selected');
    card.querySelector('.selected-check').style.display = 'block';

    // Cập nhật input hidden
    const hiddenInput = document.getElementById('variantImageId_' + variantId);
    hiddenInput.value = imageId;

    // Cập nhật preview
    const selectedContainer = document.getElementById('selectedImageContainer_' + variantId);
    const noImageDiv = document.getElementById('noImageSelected_' + variantId);

    selectedContainer.style.display = 'block';
    noImageDiv.style.display = 'none';

    selectedContainer.querySelector('img').src = imageUrl;
    selectedContainer.querySelector('img').alt = imageAlt;

    // Cập nhật text nút toggle
    const toggleBtn = container.querySelector('button[onclick^="toggleEditImageList"]');
    toggleBtn.innerHTML = '<i class="fas fa-images mr-1"></i> Đổi ảnh';

    // Đóng danh sách sau 0.5 giây
    setTimeout(() => {
      closeEditImageList(variantId);
    }, 500);
  }

  // Hủy chọn ảnh trong form sửa
  function clearEditImageSelection(variantId) {
    // Reset input hidden
    const hiddenInput = document.getElementById('variantImageId_' + variantId);
    hiddenInput.value = '';

    // Ẩn container đã chọn, hiện thông báo chưa chọn
    const selectedContainer = document.getElementById('selectedImageContainer_' + variantId);
    const noImageDiv = document.getElementById('noImageSelected_' + variantId);

    if (selectedContainer) selectedContainer.style.display = 'none';
    if (noImageDiv) noImageDiv.style.display = 'block';

    // Reset tất cả cards trong form này
    const container = document.getElementById('imageSelectionGrid_' + variantId);
    if (container) {
      container.querySelectorAll('.image-option-card').forEach(card => {
        card.style.borderColor = '#dee2e6';
        card.style.backgroundColor = '';
        card.querySelector('.selected-check').style.display = 'none';
        card.classList.remove('selected');
      });
    }

    // Cập nhật text nút toggle
    const toggleBtn = document.querySelector('#variantImageId_' + variantId).closest('.form-group').querySelector('button[onclick^="toggleEditImageList"]');
    if (toggleBtn) {
      toggleBtn.innerHTML = '<i class="fas fa-images mr-1"></i> Chọn ảnh';
      toggleBtn.classList.remove('btn-outline-secondary');
      toggleBtn.classList.add('btn-light');
    }

    // Đóng grid nếu đang mở
    const imageGrid = document.getElementById('imageSelectionGrid_' + variantId);
    if (imageGrid && imageGrid.style.display === 'block') {
      imageGrid.style.display = 'none';
    }
  }

  // ========== FUNCTION TẮT/MỞ FORM SỬA (nếu có) ==========
  // function toggleEditVariant(variantId) {
  //   // Đóng tất cả danh sách ảnh đang mở
  //   document.querySelectorAll('.image-selection-grid').forEach(grid => {
  //     grid.style.display = 'none';
  //   });
  //
  //   // Reset tất cả nút toggle
  //   document.querySelectorAll('button[onclick^="toggleEditImageList"]').forEach(btn => {
  //     const match = btn.getAttribute('onclick').match(/toggleEditImageList\(this, (\d+)\)/);
  //     if (match) {
  //       const vid = match[1];
  //       const hasImage = document.getElementById('variantImageId_' + vid)?.value;
  //       btn.innerHTML = '<i class="fas fa-images mr-1"></i> ' + (hasImage ? 'Đổi ảnh' : 'Chọn ảnh');
  //       btn.classList.remove('btn-outline-secondary');
  //       btn.classList.add('btn-light');
  //     }
  //   });
  //
  //   // Code toggle form sửa của bạn
  //   // ... (giữ nguyên code toggle form của bạn)
  // }
</script>
