<!-- cornerButton.php -->
<div class="corner-container">
  <!-- Nút chính ở góc -->
  <button class="corner-main-btn" id="cornerMainBtn">
    <i class="fa-brands fa-dropbox"></i>
  </button>
<!--  <button class="corner-main-btn" id="goTopBtn" style="margin-top: 10px;">-->
<!--    <i class="fa-solid fa-arrow-up"></i>-->
<!--  </button>-->

  <!-- Menu chức năng -->
  <div class="corner-menu" id="cornerMenu">
    <button class="corner-menu-btn search-btn" title="Tìm kiếm">
      <i class="fas fa-search"></i>
    </button>
    <button class="corner-menu-btn chat-btn" title="Chat hỗ trợ">
      <i class="fas fa-comment"></i>
    </button>
    <button class="corner-menu-btn compare-btn" title="So sánh">
      <i class="fa-solid fa-code-compare"></i>
    </button>
  </div>

  <!-- Popup tìm kiếm -->
  <div class="search-popup" id="searchPopup">
    <div class="search-header">
      <h3>Tìm kiếm</h3>
      <button class="close-btn">&times;</button>
    </div>
    <div class="search-input-container">
      <input type="text" id="searchInput" placeholder="Nhập từ khóa tìm kiếm...">
      <button class="search-submit-btn"><i class="fas fa-search"></i></button>
    </div>
    <div class="search-results" id="searchResults">
      <!-- Kết quả tìm kiếm sẽ hiển thị ở đây -->
    </div>
  </div>

  <!-- Popup chat -->
  <div class="chat-popup" id="chatPopup">
    <div class="chat-header">
      <h3>Chat hỗ trợ</h3>
      <button class="close-btn">&times;</button>
    </div>
    <div class="chat-messages" id="chatMessages">
      <!-- Tin nhắn sẽ hiển thị ở đây -->
      <div class="message bot-message">
        <div class="message-content">
          Xin chào! Tôi có thể giúp gì cho bạn?
        </div>
        <div class="message-time">10:00</div>
      </div>
    </div>
    <div class="chat-input-container">
      <input type="text" id="chatInput" placeholder="Nhập tin nhắn...">
      <button class="chat-send-btn"><i class="fas fa-paper-plane"></i></button>
    </div>
  </div>

  <div class="compare-popup" id="comparePopup">
    <div class="chat-header">
      <h3>So sánh sản phẩm</h3>
      <button class="close-btn" onclick="closeCurrentPopup()">&times;</button>
    </div>

    <div class="compare-body">
      <div class="compare-slot" data-slot="1" id="compareSlot1">
        <div class="compare-placeholder">
          <i class="fa-solid fa-plus"></i>
          <p>Chọn sản phẩm 1</p>
        </div>
      </div>

      <div class="compare-slot" data-slot="2" id="compareSlot2">
        <div class="compare-placeholder">
          <i class="fa-solid fa-plus"></i>
          <p>Chọn sản phẩm 2</p>
        </div>
      </div>
    </div>

    <div class="compare-actions">
      <button id="goCompareBtn" disabled>So sánh</button>
      <button id="clearCompareBtn">Xóa sản phẩm</button>
    </div>
  </div>



  <!-- Overlay -->
  <div class="corner-overlay" id="cornerOverlay"></div>
</div>

<link rel="stylesheet" href="css/cornerButton.css">

<script>
  function formatCurrency(number) {
    if (number === null || number === undefined) return '';
    return new Intl.NumberFormat('vi-VN').format(Number(number)) + '₫';
  }

  // Tin nhắn mẫu cho chat
  const chatMessages = [
    {
      id: 1,
      type: "bot",
      content: "Xin chào! Tôi có thể giúp gì cho bạn?",
      time: "10:00"
    },
    {
      id: 2,
      type: "user",
      content: "Tôi muốn hỏi về chính sách vận chuyển",
      time: "10:01"
    },
    {
      id: 3,
      type: "bot",
      content: "Chúng tôi miễn phí vận chuyển cho đơn hàng từ 500K. Thời gian giao hàng từ 2-5 ngày làm việc.",
      time: "10:02"
    }
  ];

  // Biến trạng thái
  let isMenuOpen = false;
  let currentPopup = null;

  // DOM Elements
  const cornerMainBtn = document.getElementById('cornerMainBtn');
  const cornerMenu = document.getElementById('cornerMenu');
  const searchPopup = document.getElementById('searchPopup');
  const chatPopup = document.getElementById('chatPopup');
  const comparePopup = document.getElementById("comparePopup")
  const cornerOverlay = document.getElementById('cornerOverlay');
  const searchInput = document.getElementById('searchInput');
  const searchResults = document.getElementById('searchResults');
  const chatInput = document.getElementById('chatInput');
  const chatMessagesContainer = document.getElementById('chatMessages');

  // Mở/đóng menu chính
  cornerMainBtn.addEventListener('click', () => {
    isMenuOpen = !isMenuOpen;
    cornerMainBtn.classList.toggle('active', isMenuOpen);
    cornerMenu.classList.toggle('active', isMenuOpen);

    if (currentPopup) {
      closeCurrentPopup();
    }
  });

  // Mở popup tìm kiếm
  // document.querySelector('.search-btn').addEventListener('click', () => {
  //   openPopup('search');
  // });
  // document.querySelector('.search-btn-header').addEventListener('click', () => {
  //   openPopup('search');
  // });

  function bindSearchButtons() {
    document.querySelectorAll('.search-btn, .dh__search-float')
      .forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          openPopup('search');
        });
      });
  }

  bindSearchButtons();

  // Mở popup chat
  document.querySelector('.chat-btn').addEventListener('click', () => {
    openPopup('chat');
    renderChatMessages();
  });

  // Đóng popup bằng nút close
  document.querySelectorAll('.close-btn').forEach(btn => {
    btn.addEventListener('click', closeCurrentPopup);
  });

  // Đóng bằng overlay
  cornerOverlay.addEventListener('click', closeCurrentPopup);

  // Tìm kiếm khi nhấn Enter
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      performSearch();
    }
  });

  // Tìm kiếm khi nhấn nút
  document.querySelector('.search-submit-btn').addEventListener('click', performSearch);

  // Gửi tin nhắn khi nhấn Enter
  chatInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      sendMessage();
    }
  });

  // Gửi tin nhắn khi nhấn nút
  document.querySelector('.chat-send-btn').addEventListener('click', sendMessage);

  let searchTimer;

  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(performSearch, 300);
  });


  // Hàm mở popup
  function openPopup(type) {
    closeCurrentPopup();

    if (type === 'search') {
      searchPopup.classList.add('active');
      currentPopup = searchPopup;
      searchInput.focus();
    } else if (type === 'chat') {
      chatPopup.classList.add('active');
      currentPopup = chatPopup;
      chatInput.focus();
    } else if (type === "compare") {
      comparePopup.classList.add('active');
      currentPopup = comparePopup;
    }
    cornerOverlay.style.display = 'block';
    cornerMenu.classList.remove('active');
    cornerMainBtn.classList.remove('active');
    isMenuOpen = false;
  }

  // Hàm đóng popup hiện tại
  function closeCurrentPopup() {
    if (currentPopup) {
      currentPopup.classList.remove('active');
      currentPopup = null;
    }
    cornerOverlay.style.display = 'none';
  }

  // Hàm thực hiện tìm kiếm
  function performSearch() {
    const query = searchInput.value.trim();

    if (!query) {
      searchResults.innerHTML = '<div class="no-results">Vui lòng nhập từ khóa tìm kiếm</div>';
      return;
    }

    searchResults.innerHTML = '<div class="no-results">Đang tìm kiếm...</div>';

    fetch(`/apiPrivate/search_product.php?q=${encodeURIComponent(query)}`)
      .then(res => res.json())
      .then(results => {
        if (!results.length) {
          searchResults.innerHTML = '<div class="no-results">Không tìm thấy sản phẩm</div>';
          return;
        }

        let html = '';
        results.forEach(item => {
          let badge = '';

          if (item.type === 'brand') badge = '<span class="tag brand">Thương hiệu</span>';
          if (item.type === 'category') badge = '<span class="tag category">Danh mục</span>';

          html += `
            <div class="search-result-item" onclick="window.location.href='${item.url}'">
              ${item.image ? `<img src="${item.image}">` : ''}
              <div class="result-info">
                <div class="result-title">
                  ${item.title}
                  ${badge}
                </div>
                ${item.price ? `<div class="result-price">${formatCurrency(item.price)}</div>` : ''}
              </div>
            </div>
          `;
        });


        searchResults.innerHTML = html;
      })
      .catch(() => {
        searchResults.innerHTML = '<div class="no-results">Có lỗi khi tìm kiếm</div>';
      });
  }


  // Hàm render tin nhắn chat
  function renderChatMessages() {
    let html = '';
    chatMessages.forEach(msg => {
      html += `
            <div class="message ${msg.type}-message">
                <div class="message-content">${msg.content}</div>
                <div class="message-time">${msg.time}</div>
            </div>
        `;
    });
    chatMessagesContainer.innerHTML = html;
    scrollChatToBottom();
  }

  // Hàm gửi tin nhắn
  function sendMessage() {
    const message = chatInput.value.trim();

    if (!message) return;

    // Thêm tin nhắn người dùng
    const userMessage = {
      id: chatMessages.length + 1,
      type: 'user',
      content: message,
      time: getCurrentTime()
    };

    chatMessages.push(userMessage);
    renderChatMessages();
    chatInput.value = '';

    // Phản hồi tự động (simulate bot)
    setTimeout(() => {
      const botResponse = {
        id: chatMessages.length + 1,
        type: 'bot',
        content: getBotResponse(message),
        time: getCurrentTime()
      };

      chatMessages.push(botResponse);
      renderChatMessages();
    }, 1000);
  }

  // Hàm lấy phản hồi từ bot (đơn giản)
  function getBotResponse(message) {
    const msg = message.toLowerCase();

    if (msg.includes('chào') || msg.includes('hello') || msg.includes('hi')) {
      return 'Xin chào! Rất vui được hỗ trợ bạn.';
    } else if (msg.includes('giá') || msg.includes('giảm giá') || msg.includes('sale')) {
      return 'Hiện chúng tôi đang có chương trình giảm giá 10% cho đơn hàng đầu tiên. Mã: WELCOME10';
    } else if (msg.includes('vận chuyển') || msg.includes('giao hàng')) {
      return 'Miễn phí vận chuyển cho đơn hàng từ 500K. Thời gian giao: 2-5 ngày làm việc.';
    } else if (msg.includes('thanh toán')) {
      return 'Chúng tôi hỗ trợ thanh toán COD, chuyển khoản ngân hàng và ví điện tử.';
    } else if (msg.includes('cảm ơn') || msg.includes('thanks')) {
      return 'Không có gì! Nếu cần thêm hỗ trợ, cứ hỏi nhé!';
    } else {
      return 'Cảm ơn bạn đã liên hệ. Tôi sẽ chuyển thông tin này cho bộ phận hỗ trợ để được giải đáp chi tiết hơn.';
    }
  }

  // Hàm lấy thời gian hiện tại
  function getCurrentTime() {
    const now = new Date();
    return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
  }

  // Cuộn chat xuống dưới cùng
  function scrollChatToBottom() {
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
  }

  // Tìm kiếm mẫu khi mở popup
  searchInput.addEventListener('focus', () => {
    if (!searchInput.value) {
      searchResults.innerHTML = `

        `;
    }
  });

  // Đóng bằng phím ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeCurrentPopup();
    }
  });
</script>

<script>
  let compareState = {
    category_id: null,
    products: []
  };

  /* =========================
     MỞ POPUP TỪ NÚT GÓC
  ========================== */
  document.querySelector('.compare-btn').addEventListener('click', () => {
    openPopup('compare');
    loadCompareSession();
  });

  /* =========================
     CLICK SLOT → ĐI CHỌN SP
  ========================== */
  document.getElementById('compareSlot1').onclick = () => {
    window.location.href = 'products.php?select_compare=1';
  };

  document.getElementById('compareSlot2').onclick = () => {
    if (!compareState.category_id) {
      alert('Vui lòng chọn sản phẩm 1 trước');
      return;
    }
    window.location.href =
      `products.php?category=${compareState.category_id}&select_compare=2`;
  };

  /* =========================
     LOAD SESSION ĐỂ HIỂN THỊ
  ========================== */
  function loadCompareSession() {
    fetch('/apiPrivate/compare_get.php')
      .then(res => res.json())
      .then(data => {
        compareState = data;
        updateCompareUI();
      });
  }

  function fetchProduct(productId) {
    return fetch(`/apiPrivate/pro_get.php?id=${productId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          throw new Error(data.message);
        }
        return data.product;
      });
  }


  function updateCompareUI() {
    ['compareSlot1', 'compareSlot2'].forEach((id, index) => {
      const slot = document.getElementById(id);
      const pid = compareState.products[index];

      // CHƯA CÓ SẢN PHẨM
      if (!pid) {
        slot.classList.remove('filled');
        slot.innerHTML = `
        <div class="compare-placeholder">
          <i class="fa-solid fa-plus"></i>
          <p>Chọn sản phẩm ${index + 1}</p>
        </div>
      `;
        return;
      }

      // ĐANG LOAD
      slot.classList.add('filled');
      slot.innerHTML = `
      <div class="compare-loading">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>
    `;

      // CÓ SẢN PHẨM → LẤY DATA
      fetchProduct(pid).then(product => {
        slot.innerHTML = `
        <div class="compare-product">
          <img src="img/adminUP/products/${product.image_url}" alt="${product.alt_text || product.name_pr}">
          <div class="compare-name">${product.name_pr}</div>
          <div class="compare-price">${formatCurrency(product.sale_price)}</div>
        </div>
      `;
      }).catch(() => {
        slot.innerHTML = `
        <div class="compare-error">
          Không tải được sản phẩm
        </div>
      `;
      });
    });

    document.getElementById('goCompareBtn').disabled =
      compareState.products.length < 2;
    document.getElementById('clearCompareBtn').disabled =
      compareState.products.length === 0;
  }

  function clearCompare() {
    // reset state phía client
    compareState = {
      category_id: null,
      products: []
    };

    // gọi API xoá session
    fetch('/apiPrivate/compare_clear.php')
      .finally(() => {
        updateCompareUI();
      });
  }

  document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('goCompareBtn').onclick = () => {
      // window.location.href = 'compare.php';
      // e.preventDefault();
      window.open('compare.php', '_blank', 'noopener');
    };

    document.getElementById('clearCompareBtn')
      .addEventListener('click', clearCompare);

  });

</script>


