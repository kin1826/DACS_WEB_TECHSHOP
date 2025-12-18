document.addEventListener('DOMContentLoaded', function() {
  // Hero Slider
  const slides = document.querySelector('.slides');
  const slideCount = document.querySelectorAll('.slide').length;
  const dots = document.querySelectorAll('.slider-dot');
  const prevBtn = document.querySelector('.slider-arrow.prev');
  const nextBtn = document.querySelector('.slider-arrow.next');
  let currentSlide = 0;
  let slideInterval;

  // Tạo sản phẩm ảo
  const newProductsTrack = document.getElementById('newProductsTrack');
  const products = [

  ];

  // Tạo HTML cho sản phẩm
  products.forEach(product => {
    const productCard = document.createElement('div');
    productCard.className = 'product-card';
    productCard.innerHTML = `
                    <div class="product-img">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="product-info">
                        <div class="product-category">${product.category}</div>
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">
                            <span class="current-price">${product.price}</span>
                            <span class="original-price">${product.oldPrice}</span>
                        </div>
                        <div class="product-rating">
                            <div class="stars">
                                ${generateStars(product.rating)}
                            </div>
                            <span class="rating-count">(${product.reviews})</span>
                        </div>
                        <button class="add-to-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Thêm Vào Giỏ
                        </button>
                    </div>
                `;
    newProductsTrack.appendChild(productCard);
  });

  // Nhân đôi sản phẩm để tạo hiệu ứng vô hạn
  const productCards = document.querySelectorAll('.product-card');
  productCards.forEach(card => {
    const clone = card.cloneNode(true);
    newProductsTrack.appendChild(clone);
  });

  // Hàm tạo sao đánh giá
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

  // Hàm chuyển slide
  function goToSlide(index) {
    currentSlide = (index + slideCount) % slideCount;
    slides.style.transform = `translateX(-${currentSlide * 25}%)`;

    // Cập nhật dots
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === currentSlide);
    });
  }

  // Hàm chuyển slide tiếp theo
  function nextSlide() {
    goToSlide(currentSlide + 1);
  }

  // Hàm chuyển slide trước đó
  function prevSlide() {
    goToSlide(currentSlide - 1);
  }

  // Sự kiện cho nút điều hướng
  nextBtn.addEventListener('click', nextSlide);
  prevBtn.addEventListener('click', prevSlide);

  // Sự kiện cho dots
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      goToSlide(i);
      resetSlideInterval();
    });
  });

  // Tự động chuyển slide
  function startSlideInterval() {
    slideInterval = setInterval(nextSlide, 5000);
  }

  function resetSlideInterval() {
    clearInterval(slideInterval);
    startSlideInterval();
  }

  startSlideInterval();

  // Dừng tự động chuyển slide khi hover
  const heroSlider = document.querySelector('.hero-slider');
  heroSlider.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
  });

  heroSlider.addEventListener('mouseleave', () => {
    startSlideInterval();
  });

  // Cuộn ngang sản phẩm
  const productsScroll = document.querySelector('.products-scroll');
  const productsTrack = document.querySelector('.products-track');
  let isDown = false;
  let startX;
  let scrollLeft;

  productsScroll.addEventListener('mousedown', (e) => {
    isDown = true;
    productsScroll.classList.add('active');
    startX = e.pageX - productsScroll.offsetLeft;
    scrollLeft = productsScroll.scrollLeft;
  });

  productsScroll.addEventListener('mouseleave', () => {
    isDown = false;
    productsScroll.classList.remove('active');
  });

  productsScroll.addEventListener('mouseup', () => {
    isDown = false;
    productsScroll.classList.remove('active');
  });

  productsScroll.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - productsScroll.offsetLeft;
    const walk = (x - startX) * 2;
    productsScroll.scrollLeft = scrollLeft - walk;
  });

  // Tự động cuộn sản phẩm
  let productScrollInterval = setInterval(() => {
    if (!isDown) {
      productsScroll.scrollLeft += 2;

      // Reset về đầu khi cuộn hết
      if (productsScroll.scrollLeft >= productsTrack.scrollWidth / 2) {
        productsScroll.scrollLeft = 0;
      }
    }
  }, 30);

  // Dừng tự động cuộn khi hover
  productsScroll.addEventListener('mouseenter', () => {
    clearInterval(productScrollInterval);
  });

  productsScroll.addEventListener('mouseleave', () => {
    productScrollInterval = setInterval(() => {
      if (!isDown) {
        productsScroll.scrollLeft += 2;

        // Reset về đầu khi cuộn hết
        if (productsScroll.scrollLeft >= productsTrack.scrollWidth / 2) {
          productsScroll.scrollLeft = 0;
        }
      }
    }, 30);
  });
});

// Thêm hiệu ứng cho review cards
const reviewCards = document.querySelectorAll('.review-card');
reviewCards.forEach(card => {
  card.addEventListener('mouseenter', () => {
    card.style.transform = 'translateY(-10px) scale(1.02)';
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'translateY(0) scale(1)';
  });
});

// Hiệu ứng cho combo cards
const comboCards = document.querySelectorAll('.combo-card');
comboCards.forEach(card => {
  card.addEventListener('mouseenter', () => {
    card.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.15)';
  });

  card.addEventListener('mouseleave', () => {
    card.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
  });
});

// Countdown Timer cho Flash Sale
function updateCountdown() {
  const now = new Date();
  const target = new Date();
  target.setHours(24, 0, 0, 0); // Set target to tomorrow 00:00:00

  const diff = target - now;

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
  document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
  document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
}

// Update countdown every second
setInterval(updateCountdown, 1000);
updateCountdown(); // Initial call

// Flash Sale Slider
document.addEventListener('DOMContentLoaded', function() {
  const saleProducts = document.getElementById('saleProducts');
  const salePrev = document.getElementById('salePrev');
  const saleNext = document.getElementById('saleNext');
  const saleDots = document.getElementById('saleDots');

  const productWidth = 280 + 25; // width + gap
  const visibleProducts = 4;
  let currentPosition = 0;
  let totalProducts = document.querySelectorAll('.sale-product').length;
  let totalSlides = Math.ceil(totalProducts / visibleProducts);

  // Tạo dots
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement('div');
    dot.className = 'sale-dot' + (i === 0 ? ' active' : '');
    dot.addEventListener('click', () => goToSlide(i));
    saleDots.appendChild(dot);
  }

  // Cập nhật trạng thái nút điều hướng
  function updateNavButtons() {
    salePrev.classList.toggle('disabled', currentPosition === 0);
    saleNext.classList.toggle('disabled', currentPosition >= totalProducts - visibleProducts);

    // Cập nhật active dot
    const activeDotIndex = Math.floor(currentPosition / visibleProducts);
    document.querySelectorAll('.sale-dot').forEach((dot, index) => {
      dot.classList.toggle('active', index === activeDotIndex);
    });
  }

  // Chuyển đến slide cụ thể
  function goToSlide(slideIndex) {
    currentPosition = slideIndex * visibleProducts;
    saleProducts.scrollLeft = currentPosition * productWidth;
    updateNavButtons();
  }

  // Sự kiện nút previous
  salePrev.addEventListener('click', function() {
    if (currentPosition > 0) {
      currentPosition--;
      saleProducts.scrollLeft = currentPosition * productWidth;
      updateNavButtons();
    }
  });

  // Sự kiện nút next
  saleNext.addEventListener('click', function() {
    if (currentPosition < totalProducts - visibleProducts) {
      currentPosition++;
      saleProducts.scrollLeft = currentPosition * productWidth;
      updateNavButtons();
    }
  });

  // Sự kiện scroll bằng chuột
  let isDragging = false;
  let startX;
  let scrollLeft;

  saleProducts.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.pageX - saleProducts.offsetLeft;
    scrollLeft = saleProducts.scrollLeft;
  });

  saleProducts.addEventListener('mouseleave', () => {
    isDragging = false;
  });

  saleProducts.addEventListener('mouseup', () => {
    isDragging = false;
  });

  saleProducts.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - saleProducts.offsetLeft;
    const walk = (x - startX) * 2;
    saleProducts.scrollLeft = scrollLeft - walk;
  });

  // Cập nhật vị trí khi scroll
  saleProducts.addEventListener('scroll', () => {
    currentPosition = Math.round(saleProducts.scrollLeft / productWidth);
    updateNavButtons();
  });

  // Tự động chuyển slide mỗi 5 giây
  let autoSlide = setInterval(() => {
    if (currentPosition < totalProducts - visibleProducts) {
      currentPosition++;
      saleProducts.scrollLeft = currentPosition * productWidth;
      updateNavButtons();
    } else {
      currentPosition = 0;
      saleProducts.scrollLeft = 0;
      updateNavButtons();
    }
  }, 5000);

  // Dừng tự động chuyển khi hover
  saleProducts.addEventListener('mouseenter', () => {
    clearInterval(autoSlide);
  });

  saleProducts.addEventListener('mouseleave', () => {
    autoSlide = setInterval(() => {
      if (currentPosition < totalProducts - visibleProducts) {
        currentPosition++;
        saleProducts.scrollLeft = currentPosition * productWidth;
        updateNavButtons();
      } else {
        currentPosition = 0;
        saleProducts.scrollLeft = 0;
        updateNavButtons();
      }
    }, 5000);
  });

  // Khởi tạo
  updateNavButtons();
});

// Thêm hiệu ứng cho sale products
const saleProducts = document.querySelectorAll('.sale-product');
saleProducts.forEach(product => {
  product.addEventListener('mouseenter', () => {
    product.style.transform = 'translateY(-10px)';
    product.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.3)';
  });

  product.addEventListener('mouseleave', () => {
    product.style.transform = 'translateY(-5px)';
    product.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.2)';
  });
});
