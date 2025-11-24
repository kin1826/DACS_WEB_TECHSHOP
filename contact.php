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
  <link rel="stylesheet" href="css/contact.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>

<body>

<!-- contact.php -->
<div class="contact-page">
  <!-- Hero Section -->
  <section class="contact-hero">
    <div class="container">
      <div class="hero-content">
        <h1>Liên Hệ Với Chúng Tôi</h1>
        <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7</p>
      </div>
    </div>
  </section>

  <!-- Main Contact Section -->
  <section class="main-contact">
    <div class="container">
      <div class="contact-layout">
        <!-- Contact Form -->
        <div class="contact-form-section">
          <h2>Gửi Tin Nhắn Cho Chúng Tôi</h2>
          <p>Điền thông tin bên dưới, chúng tôi sẽ phản hồi trong vòng 24h</p>

          <form class="contact-form" id="contactForm">
            <div class="form-row">
              <div class="form-group">
                <label for="name">Họ và tên *</label>
                <input type="text" id="name" name="name" required placeholder="Nhập họ và tên của bạn">
              </div>
              <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required placeholder="Nhập email của bạn">
              </div>
            </div>

            <div class="form-group">
              <label for="phone">Số điện thoại</label>
              <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại">
            </div>

            <div class="form-group">
              <label for="subject">Chủ đề *</label>
              <select id="subject" name="subject" required>
                <option value="">Chọn chủ đề liên hệ</option>
                <option value="support">Hỗ trợ kỹ thuật</option>
                <option value="sales">Tư vấn mua hàng</option>
                <option value="warranty">Bảo hành & Sửa chữa</option>
                <option value="cooperation">Hợp tác kinh doanh</option>
                <option value="feedback">Góp ý & Khiếu nại</option>
                <option value="other">Khác</option>
              </select>
            </div>

            <div class="form-group">
              <label for="message">Nội dung tin nhắn *</label>
              <textarea id="message" name="message" rows="6" required placeholder="Mô tả chi tiết vấn đề của bạn..."></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Gửi Tin Nhắn
              </button>
            </div>
          </form>
        </div>

        <!-- Contact Info -->
        <div class="contact-info-section">
          <h2>Thông Tin Liên Hệ</h2>

          <div class="contact-info">
            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div class="info-content">
                <h4>Địa chỉ</h4>
                <p>123 Trần Duy Hưng, Cầu Giấy, Hà Nội</p>
                <p>456 Nguyễn Văn Linh, Quận 7, TP.HCM</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-phone"></i>
              </div>
              <div class="info-content">
                <h4>Điện thoại</h4>
                <p>Hà Nội: <a href="tel:02412345678">024 1234 5678</a></p>
                <p>TP.HCM: <a href="tel:02887654321">028 8765 4321</a></p>
                <p>Hotline: <a href="tel:18001234" class="hotline">1800 1234</a></p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="info-content">
                <h4>Email</h4>
                <p><a href="mailto:support@techstore.vn">support@techstore.vn</a></p>
                <p><a href="mailto:sales@techstore.vn">sales@techstore.vn</a></p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="info-content">
                <h4>Giờ làm việc</h4>
                <p>Thứ 2 - Thứ 6: 8:00 - 22:00</p>
                <p>Thứ 7 - Chủ nhật: 8:00 - 21:00</p>
              </div>
            </div>
          </div>

          <!-- Quick FAQ -->
          <div class="quick-faq">
            <h3>Câu Hỏi Thường Gặp</h3>
            <div class="faq-list">
              <button class="faq-question" data-answer="Chúng tôi có chính sách đổi trả trong vòng 30 ngày với sản phẩm còn nguyên seal, đầy đủ phụ kiện và hóa đơn mua hàng.">
                Chính sách đổi trả như thế nào?
              </button>
              <button class="faq-question" data-answer="Thời gian bảo hành từ 12-24 tháng tùy sản phẩm. Mang sản phẩm kèm hóa đơn đến bất kỳ showroom nào của TechStore.">
                Bảo hành sản phẩm trong bao lâu?
              </button>
              <button class="faq-question" data-answer="Miễn phí giao hàng toàn quốc với đơn từ 2 triệu. Nội thành Hà Nội & TP.HCM: 2-4 giờ, các tỉnh thành khác: 24-48 giờ.">
                Thời gian giao hàng bao lâu?
              </button>
              <button class="faq-question" data-answer="Chúng tôi có chương trình trả góp 0% qua thẻ tín dụng của các ngân hàng đối tác. Liên hệ hotline để được tư vấn cụ thể.">
                Có hỗ trợ trả góp không?
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Google Maps Section -->
  <section class="maps-section">
    <div class="container">
      <h2>Hệ Thống Showroom</h2>
      <div class="maps-container">
        <div class="map-wrapper">
          <div class="map-placeholder">
            <i class="fas fa-map-marked-alt"></i>
            <h4>Showroom Hà Nội</h4>
            <p>123 Trần Duy Hưng, Cầu Giấy</p>
            <div class="map-image">
              <img src="https://images.unsplash.com/photo-1569336415962-a4bd9f69cd83?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Hanoi Showroom">
            </div>
          </div>
        </div>
        <div class="map-wrapper">
          <div class="map-placeholder">
            <i class="fas fa-map-marked-alt"></i>
            <h4>Showroom TP.HCM</h4>
            <p>456 Nguyễn Văn Linh, Quận 7</p>
            <div class="map-image">
              <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="HCMC Showroom">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Support Stats -->
  <section class="support-stats">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-headset"></i>
          </div>
          <div class="stat-content">
            <h3>24/7</h3>
            <p>Hỗ trợ trực tuyến</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <h3>15 phút</h3>
            <p>Phản hồi trung bình</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-thumbs-up"></i>
          </div>
          <div class="stat-content">
            <h3>98%</h3>
            <p>Hài lòng với dịch vụ</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-content">
            <h3>50K+</h3>
            <p>Khách hàng tin tưởng</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Chat Support Modal -->
<div class="chat-modal" id="chatModal">
  <div class="chat-header">
    <div class="chat-title">
      <i class="fas fa-headset"></i>
      <span>Hỗ Trợ Trực Tuyến</span>
    </div>
    <button class="chat-close" id="closeChat">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="chat-body">
    <div class="chat-messages" id="chatMessages">
      <div class="message bot-message">
        <div class="message-avatar">
          <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
          <p>Xin chào! Tôi có thể giúp gì cho bạn?</p>
          <span class="message-time">Bây giờ</span>
        </div>
      </div>
    </div>

    <div class="quick-questions">
      <p>Câu hỏi nhanh:</p>
      <div class="quick-buttons">
        <button class="quick-btn" data-question="Tôi muốn tư vấn mua laptop">💻 Tư vấn laptop</button>
        <button class="quick-btn" data-question="Tôi cần hỗ trợ kỹ thuật">🔧 Hỗ trợ kỹ thuật</button>
        <button class="quick-btn" data-question="Kiểm tra tình trạng đơn hàng">📦 Kiểm tra đơn hàng</button>
        <button class="quick-btn" data-question="Tôi muốn khiếu nại dịch vụ">😠 Khiếu nại dịch vụ</button>
      </div>
    </div>
  </div>

  <div class="chat-footer">
    <div class="chat-input">
      <input type="text" id="chatInput" placeholder="Nhập tin nhắn của bạn...">
      <button id="sendMessage">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </div>
</div>

<!-- Floating Chat Button -->
<button class="floating-chat-btn" id="floatingChatBtn">
  <i class="fas fa-comments"></i>
  <span class="chat-badge">1</span>
</button>

<?php include 'footer.php'?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Contact Form Submission
    const contactForm = document.getElementById('contactForm');
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();

      // Lấy dữ liệu form
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);

      // Hiển thị loading
      const submitBtn = this.querySelector('.submit-btn');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
      submitBtn.disabled = true;

      // Giả lập gửi email (trong thực tế sẽ gọi API)
      setTimeout(() => {
        alert('Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ phản hồi trong vòng 24h.');
        contactForm.reset();
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }, 2000);
    });

    // FAQ Questions
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
      question.addEventListener('click', function() {
        const answer = this.getAttribute('data-answer');
        alert(answer);
      });
    });

    // Chat Modal
    const chatModal = document.getElementById('chatModal');
    const floatingChatBtn = document.getElementById('floatingChatBtn');
    const closeChat = document.getElementById('closeChat');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendMessage = document.getElementById('sendMessage');
    const quickButtons = document.querySelectorAll('.quick-btn');

    // Toggle chat modal
    floatingChatBtn.addEventListener('click', function() {
      chatModal.classList.add('show');
      this.style.display = 'none';
    });

    closeChat.addEventListener('click', function() {
      chatModal.classList.remove('show');
      floatingChatBtn.style.display = 'flex';
    });

    // Quick questions buttons
    quickButtons.forEach(button => {
      button.addEventListener('click', function() {
        const question = this.getAttribute('data-question');
        addUserMessage(question);
        simulateBotResponse(question);
      });
    });

    // Send message
    sendMessage.addEventListener('click', sendUserMessage);
    chatInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        sendUserMessage();
      }
    });

    function sendUserMessage() {
      const message = chatInput.value.trim();
      if (message) {
        addUserMessage(message);
        chatInput.value = '';
        simulateBotResponse(message);
      }
    }

    function addUserMessage(message) {
      const messageDiv = document.createElement('div');
      messageDiv.className = 'message user-message';
      messageDiv.innerHTML = `
            <div class="message-content">
                <p>${message}</p>
                <span class="message-time">Bây giờ</span>
            </div>
            <div class="message-avatar">
                <i class="fas fa-user"></i>
            </div>
        `;
      chatMessages.appendChild(messageDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addBotMessage(message) {
      const messageDiv = document.createElement('div');
      messageDiv.className = 'message bot-message';
      messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <p>${message}</p>
                <span class="message-time">Bây giờ</span>
            </div>
        `;
      chatMessages.appendChild(messageDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function simulateBotResponse(userMessage) {
      setTimeout(() => {
        let response = '';

        if (userMessage.includes('laptop') || userMessage.includes('mua')) {
          response = 'Để tư vấn laptop phù hợp, bạn có thể:\n• Gọi hotline 1800 1234\n• Để lại số điện thoại, chúng tôi sẽ gọi lại\n• Ghé showroom để trải nghiệm trực tiếp';
        } else if (userMessage.includes('kỹ thuật') || userMessage.includes('hỗ trợ')) {
          response = 'Đội ngũ kỹ thuật của chúng tôi sẵn sàng hỗ trợ bạn. Vui lòng cung cấp:\n• Mã sản phẩn\n• Mô tả vấn đề\n• Hình ảnh/video (nếu có)';
        } else if (userMessage.includes('đơn hàng')) {
          response = 'Để kiểm tra đơn hàng, vui lòng:\n• Cung cấp mã đơn hàng\n• Hoặc số điện thoại đặt hàng\n• Truy cập trang "Tra cứu đơn hàng" trên website';
        } else if (userMessage.includes('khiếu nại')) {
          response = 'Rất tiếc về trải nghiệm của bạn. Vui lòng liên hệ:\n• Hotline 1800 1234 (phím 3)\n• Email: support@techstore.vn\n• Đến trực tiếp showroom';
        } else {
          response = 'Cảm ơn bạn đã liên hệ! Chuyên viên sẽ phản hồi trong ít phút. Trong thời gian chờ đợi, bạn có thể:\n• Xem Câu hỏi thường gặp\n• Gọi hotline 1800 1234\n• Để lại số điện thoại';
        }

        addBotMessage(response);
      }, 1000);
    }

    // Auto-open chat after 30 seconds
    setTimeout(() => {
      if (!chatModal.classList.contains('show')) {
        floatingChatBtn.style.animation = 'pulse 2s infinite';
      }
    }, 30000);
  });
</script>

</body>
</html>
