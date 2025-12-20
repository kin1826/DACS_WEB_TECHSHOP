<!DOCTYPE html>
<html>
<head>
  <title>Test Page</title>
</head>
<body>
<h1>Test Popup Notification</h1>

<button onclick="showSuccess('Lưu thành công!')">Success</button>
<button onclick="showError('Có lỗi xảy ra')">Error</button>
<button onclick="showInfo('Đang xử lý...')">Info</button>

<!-- NOTIFICATION POPUP - COPY THIS ENTIRE BLOCK -->
<style>
  /* CSS for Notification Popup */
  #notificationPopup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    backdrop-filter: blur(5px);
  }

  #notificationContainer {
    position: relative;
    width: 320px;
    max-width: 90vw;
    padding: 30px 25px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37),
    inset 0 4px 20px rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    text-align: center;
    overflow: hidden;
    animation: liquidDrop 0.6s ease-out;
  }

  /* Water drop effect */
  @keyframes liquidDrop {
    0% {
      transform: scale(0) translateY(-50px);
      opacity: 0;
      border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    50% {
      transform: scale(1.1) translateY(10px);
      border-radius: 50% 50% 30% 70% / 50% 40% 60% 50%;
    }
    100% {
      transform: scale(1) translateY(0);
      opacity: 1;
      border-radius: 20px;
    }
  }

  .liquid-effect {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: liquidFlow 3s infinite linear;
    opacity: 0.3;
  }

  @keyframes liquidFlow {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  .notification-icon {
    font-size: 48px;
    margin-bottom: 15px;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }

  .notification-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  }

  .notification-message {
    font-size: 16px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 20px;
  }

  .notification-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .notification-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  /* Colors */
  .notification-success .notification-icon { color: #4ade80; }
  .notification-error .notification-icon { color: #f87171; }
  .notification-warning .notification-icon { color: #fbbf24; }
  .notification-info .notification-icon { color: #60a5fa; }
</style>

<!-- HTML Structure -->
<div id="notificationPopup">
  <div id="notificationContainer" class="notification-success">
    <div class="liquid-effect"></div>
    <button class="notification-close" id="notificationCloseBtn">✕</button>
    <div class="notification-icon">
      <i id="notificationIcon" class="fas fa-check-circle"></i>
    </div>
    <h3 id="notificationTitle" class="notification-title">Thành công!</h3>
    <p id="notificationMessage" class="notification-message">Thao tác đã hoàn thành!</p>
  </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
  // SIMPLE NOTIFICATION SYSTEM - GUARANTEED TO WORK
  (function() {
    'use strict';

    // Create popup if it doesn't exist
    function ensurePopupExists() {
      if (!document.getElementById('notificationPopup')) {
        const popupHTML = `
            <div id="notificationPopup">
                <div id="notificationContainer" class="notification-success">
                    <div class="liquid-effect"></div>
                    <button class="notification-close" id="notificationCloseBtn">✕</button>
                    <div class="notification-icon">
                        <i id="notificationIcon" class="fas fa-check-circle"></i>
                    </div>
                    <h3 id="notificationTitle" class="notification-title">Thành công!</h3>
                    <p id="notificationMessage" class="notification-message">Thao tác đã hoàn thành!</p>
                </div>
            </div>`;

        const div = document.createElement('div');
        div.innerHTML = popupHTML;
        document.body.appendChild(div.firstChild);

        // Add close event
        document.getElementById('notificationCloseBtn').onclick = hideNotification;
      }
    }

    // Show notification function
    window.showNotification = function(options = {}) {
      ensurePopupExists();

      const popup = document.getElementById('notificationPopup');
      const container = document.getElementById('notificationContainer');
      const title = document.getElementById('notificationTitle');
      const message = document.getElementById('notificationMessage');
      const icon = document.getElementById('notificationIcon');

      // Default options
      const config = {
        title: 'Thành công!',
        message: 'Thao tác đã hoàn thành!',
        type: 'success',
        duration: 3000,
        icon: null,
        ...options
      };

      // Set icon based on type
      const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
      };

      // Apply styles and content
      container.className = 'notification-' + config.type;
      icon.className = config.icon || icons[config.type] || icons.success;
      title.textContent = config.title;
      message.textContent = config.message;

      // Show with animation
      popup.style.display = 'flex';
      container.style.animation = 'none';
      setTimeout(() => {
        container.style.animation = 'liquidDrop 0.6s ease-out';
      }, 10);

      // Auto close if duration > 0
      if (config.duration > 0) {
        clearTimeout(window.notificationTimer);
        window.notificationTimer = setTimeout(hideNotification, config.duration);
      }
    };

    // Hide notification
    window.hideNotification = function() {
      const popup = document.getElementById('notificationPopup');
      if (popup) {
        popup.style.display = 'none';
      }
    };

    // Quick functions
    window.showSuccess = function(message, title = 'Thành công!', duration = 3000) {
      showNotification({ title, message, type: 'success', duration });
      console.log("ádf")
    };

    window.showError = function(message, title = 'Lỗi!', duration = 4000) {
      showNotification({ title, message, type: 'error', duration });
    };

    window.showWarning = function(message, title = 'Cảnh báo!', duration = 3500) {
      showNotification({ title, message, type: 'warning', duration });
    };

    window.showInfo = function(message, title = 'Thông tin', duration = 3000) {
      showNotification({ title, message, type: 'info', duration });
    };

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
      ensurePopupExists();

      // Close on background click
      document.getElementById('notificationPopup').onclick = function(e) {
        if (e.target === this) hideNotification();
      };

      // Close on ESC key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') hideNotification();
      });
    });

    // Also initialize if already loaded
    if (document.readyState === 'complete') {
      ensurePopupExists();
    }
  })();
</script>
<!-- END NOTIFICATION POPUP -->
</body>
</html>
