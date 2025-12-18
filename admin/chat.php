<?php
require_once 'class/user.php';
?>

<style>
  /* Chat Layout */
  .chat-admin-container {
    display: flex;
    height: calc(100vh - 150px);
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  /* Users List Sidebar */
  .chat-users-sidebar {
    width: 300px;
    border-right: 1px solid #eee;
    display: flex;
    flex-direction: column;
  }

  .users-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
  }

  .users-header h3 {
    margin: 0;
    color: #2c3e50;
  }

  .search-users {
    margin-top: 15px;
  }

  .search-users input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
  }

  .users-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
  }

  .user-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
    margin-bottom: 8px;
  }

  .user-item:hover {
    background: #f8f9fa;
  }

  .user-item.active {
    background: #e3f2fd;
    border-left: 3px solid #2196f3;
  }

  .user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
  }

  .user-info {
    flex: 1;
  }

  .user-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
  }

  .user-email {
    font-size: 12px;
    color: #7f8c8d;
  }

  .user-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
  }

  .status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-bottom: 5px;
  }

  .status-indicator.online {
    background: #2ecc71;
  }

  .status-indicator.offline {
    background: #95a5a6;
  }

  .last-message-time {
    font-size: 11px;
    color: #95a5a6;
  }

  .unread-count {
    background: #e74c3c;
    color: white;
    font-size: 11px;
    font-weight: bold;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
  }

  /* Chat Main Area */
  .chat-main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .chat-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .chat-user-info {
    display: flex;
    align-items: center;
  }

  .chat-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
  }

  .chat-header h4 {
    margin: 0;
    color: #2c3e50;
  }

  .chat-user-status {
    font-size: 12px;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .chat-actions {
    display: flex;
    gap: 10px;
  }

  .chat-action-btn {
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    padding: 8px;
    border-radius: 5px;
    transition: background 0.3s;
  }

  .chat-action-btn:hover {
    background: #f8f9fa;
    color: #2c3e50;
  }

  /* Messages Area */
  .messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
  }

  .no-chat-selected {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #95a5a6;
  }

  .no-chat-selected i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #bdc3c7;
  }

  .message-date-divider {
    text-align: center;
    margin: 20px 0;
  }

  .date-label {
    background: white;
    padding: 5px 15px;
    border-radius: 15px;
    font-size: 12px;
    color: #7f8c8d;
    border: 1px solid #eee;
  }

  /* Message Bubbles */
  .message-item {
    display: flex;
    margin-bottom: 15px;
    /*max-width: 70%;*/
  }

  .message-item.admin {
    align-self: flex-end;
    flex-direction: row-reverse;
  }

  .message-item.user {
    align-self: flex-start;
  }

  .message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
    margin: 0 8px;
  }

  .admin .message-avatar {
    background: #3498db;
  }

  .user .message-avatar {
    background: #2ecc71;
  }

  .message-content {
    padding: 12px 15px;
    border-radius: 18px;
    position: relative;
  }

  .admin .message-content {
    background: #3498db;
    color: white;
    border-bottom-right-radius: 5px;
  }

  .user .message-content {
    background: white;
    color: #2c3e50;
    border-bottom-left-radius: 5px;
    border: 1px solid #eee;
  }

  .message-text {
    margin: 0;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.4;
  }

  .message-time {
    font-size: 11px;
    margin-top: 5px;
    opacity: 0.7;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
  }

  .user .message-time {
    color: #95a5a6;
  }

  .admin .message-time {
    color: rgba(255,255,255,0.8);
  }

  .message-status {
    font-size: 10px;
  }

  /* Message Input */
  .message-input-container {
    padding: 20px;
    border-top: 1px solid #eee;
    background: white;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .message-input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 25px;
    font-size: 14px;
    resize: none;
    min-height: 20px;
    max-height: 100px;
    font-family: inherit;
  }

  .message-input:focus {
    outline: none;
    border-color: #3498db;
  }

  .input-actions {
    display: flex;
    gap: 10px;
  }

  .send-btn {
    background: #3498db;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
  }

  .send-btn:hover {
    background: #2980b9;
  }

  .send-btn:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
  }

  .attachment-btn {
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
  }

  .attachment-btn:hover {
    background: #f8f9fa;
  }

  /* Chat Stats */
  .chat-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
  }

  .stat-card-chat {
    background: white;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }

  .stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  .stat-label {
    font-size: 12px;
    color: #7f8c8d;
  }

  /* Typing Indicator */
  .typing-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px 15px;
    background: white;
    border-radius: 15px;
    border: 1px solid #eee;
    align-self: flex-start;
    margin-bottom: 10px;
    width: fit-content;
  }

  .typing-dots {
    display: flex;
    gap: 4px;
  }

  .typing-dot {
    width: 6px;
    height: 6px;
    background: #95a5a6;
    border-radius: 50%;
    animation: typing 1.4s infinite;
  }

  .typing-dot:nth-child(2) {
    animation-delay: 0.2s;
  }

  .typing-dot:nth-child(3) {
    animation-delay: 0.4s;
  }

  @keyframes typing {
    0%, 60%, 100% {
      transform: translateY(0);
    }
    30% {
      transform: translateY(-5px);
    }
  }

  /* Responsive */
  @media (max-width: 992px) {
    .chat-admin-container {
      flex-direction: column;
      height: calc(100vh - 200px);
    }

    .chat-users-sidebar {
      width: 100%;
      height: 250px;
      border-right: none;
      border-bottom: 1px solid #eee;
    }

    .users-list {
      max-height: 180px;
    }
  }

  @media (max-width: 576px) {
    .message-item {
      max-width: 85%;
    }

    .chat-stats {
      grid-template-columns: repeat(2, 1fr);
    }
  }
</style>

<div class="admin-chat-page">
  <!-- Chat Statistics -->
<!--  <div class="chat-stats">-->
<!--    <div class="stat-card-chat">-->
<!--      <div class="stat-number" id="onlineUsersCount">0</div>-->
<!--      <div class="stat-label">Đang online</div>-->
<!--    </div>-->
<!--    <div class="stat-card-chat">-->
<!--      <div class="stat-number" id="unreadMessagesCount">0</div>-->
<!--      <div class="stat-label">Tin nhắn chưa đọc</div>-->
<!--    </div>-->
<!--    <div class="stat-card-chat">-->
<!--      <div class="stat-number" id="totalUsersCount">0</div>-->
<!--      <div class="stat-label">Tổng người dùng</div>-->
<!--    </div>-->
<!--    <div class="stat-card-chat">-->
<!--      <div class="stat-number" id="activeChatsCount">0</div>-->
<!--      <div class="stat-label">Cuộc hội thoại</div>-->
<!--    </div>-->
<!--  </div>-->

  <!-- Chat Container -->
  <div class="chat-admin-container">
    <!-- Users List Sidebar -->
    <div class="chat-users-sidebar">
      <div class="users-header">
        <h3><i class="fas fa-comments"></i> Tin nhắn</h3>
        <div class="search-users">
          <input type="text" id="searchUsers" placeholder="Tìm kiếm người dùng...">
        </div>
      </div>
      <div class="users-list" id="usersList">
        <!-- Users will be loaded here via JavaScript -->
      </div>
    </div>

    <!-- Chat Main Area -->
    <div class="chat-main-area">
      <!-- Chat Header (visible when user is selected) -->
      <div class="chat-header" id="chatHeader" style="display: none;">
        <div class="chat-user-info">
          <div class="chat-user-avatar" id="currentUserAvatar">U</div>
          <div>
            <h4 id="currentUserName">Người dùng</h4>
            <div class="chat-user-status">
              <span id="currentUserStatus">Online</span>
              <span id="currentUserLastSeen"></span>
            </div>
          </div>
        </div>
        <div class="chat-actions">
          <button class="chat-action-btn" title="Thông tin người dùng" onclick="showUserInfo()">
            <i class="fas fa-info-circle"></i>
          </button>
          <button class="chat-action-btn" title="Tải file đính kèm" onclick="toggleAttachments()">
            <i class="fas fa-paperclip"></i>
          </button>
          <button class="chat-action-btn" title="Xoá đoạn chat" onclick="clearChat()">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="messages-container" id="messagesContainer">
        <!-- No chat selected message -->
        <div class="no-chat-selected" id="noChatSelected">
          <i class="fas fa-comments"></i>
          <h3>Chọn một cuộc hội thoại</h3>
          <p>Chọn người dùng từ danh sách bên trái để bắt đầu trò chuyện</p>
        </div>

        <!-- Messages will be loaded here via JavaScript -->
        <div id="chatMessages" style="display: none;"></div>
      </div>

      <!-- Message Input (visible when user is selected) -->
      <div class="message-input-container" id="messageInputContainer" style="display: none;">
        <button class="attachment-btn" title="Đính kèm file" onclick="attachFile()">
          <i class="fas fa-paperclip"></i>
        </button>
        <textarea
          class="message-input"
          id="messageInput"
          placeholder="Nhập tin nhắn..."
          rows="1"
          oninput="autoResizeTextarea(this)"
        ></textarea>
        <div class="input-actions">
          <button class="send-btn" id="sendMessageBtn" onclick="sendMessage()">
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- User Info Modal -->
<!--<div id="userInfoModal" class="modal">-->
<!--  <div class="modal-content" style="max-width: 400px;">-->
<!--    <div class="modal-header">-->
<!--      <h3>Thông tin người dùng</h3>-->
<!--      <button type="button" onclick="closeUserInfoModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; position: absolute; right: 20px; top: 20px;">&times;</button>-->
<!--    </div>-->
<!--    <div class="modal-body" id="userInfoContent">-->
      <!-- User info will be loaded here -->
<!--    </div>-->
<!--    <div class="modal-footer">-->
<!--      <button type="button" class="btn btn-secondary" onclick="closeUserInfoModal()">Đóng</button>-->
<!--      <button type="button" class="btn btn-primary" onclick="viewUserDetails()">Xem chi tiết</button>-->
<!--    </div>-->
<!--  </div>-->
<!--</div>-->

<script>
  // Demo Data
  const demoUsers = [
    {
      id: 1,
      username: "Nguyễn Văn A",
      email: "nguyenvana@email.com",
      avatar: "NV",
      online: true,
      lastSeen: "Vừa xong",
      unread: 3,
      lastMessage: "Xin chào, tôi cần hỗ trợ về sản phẩm",
      lastMessageTime: "10:30",
      phone: "0912345678",
      joinDate: "15/01/2024",
      orderCount: 5
    },
    {
      id: 2,
      username: "Trần Thị B",
      email: "tranthib@email.com",
      avatar: "TB",
      online: true,
      lastSeen: "2 phút trước",
      unread: 0,
      lastMessage: "Cảm ơn đã hỗ trợ",
      lastMessageTime: "09:15",
      phone: "0987654321",
      joinDate: "20/02/2024",
      orderCount: 2
    },
    {
      id: 3,
      username: "Lê Văn C",
      email: "levanc@email.com",
      avatar: "LC",
      online: false,
      lastSeen: "2 giờ trước",
      unread: 1,
      lastMessage: "Khi nào có hàng mới?",
      lastMessageTime: "Hôm qua",
      phone: "0909123456",
      joinDate: "05/03/2024",
      orderCount: 8
    },
    {
      id: 4,
      username: "Phạm Thị D",
      email: "phamthid@email.com",
      avatar: "PD",
      online: true,
      lastSeen: "Đang hoạt động",
      unread: 0,
      lastMessage: "Đơn hàng #1234 của tôi thế nào rồi?",
      lastMessageTime: "14:20",
      phone: "0911222333",
      joinDate: "10/01/2024",
      orderCount: 3
    },
    {
      id: 5,
      username: "Hoàng Văn E",
      email: "hoangvane@email.com",
      avatar: "HE",
      online: false,
      lastSeen: "1 ngày trước",
      unread: 5,
      lastMessage: "Tôi muốn đổi trả sản phẩm",
      lastMessageTime: "2 ngày trước",
      phone: "0988777666",
      joinDate: "25/02/2024",
      orderCount: 1
    },
    {
      id: 6,
      username: "Đặng Thị F",
      email: "dangthif@email.com",
      avatar: "DF",
      online: true,
      lastSeen: "Vừa xong",
      unread: 0,
      lastMessage: "Sản phẩm rất tốt, cảm ơn shop",
      lastMessageTime: "11:45",
      phone: "0912333444",
      joinDate: "08/03/2024",
      orderCount: 4
    },
    {
      id: 7,
      username: "Bùi Văn G",
      email: "buivang@email.com",
      avatar: "BG",
      online: false,
      lastSeen: "3 ngày trước",
      unread: 0,
      lastMessage: "Giá sản phẩm có thể giảm không?",
      lastMessageTime: "Tuần trước",
      phone: "0908111222",
      joinDate: "15/03/2024",
      orderCount: 0
    },
    {
      id: 8,
      username: "Vũ Thị H",
      email: "vuthih@email.com",
      avatar: "VH",
      online: true,
      lastSeen: "Đang gõ...",
      unread: 2,
      lastMessage: "Tôi cần tư vấn về bảo hành",
      lastMessageTime: "10:05",
      phone: "0988999888",
      joinDate: "01/04/2024",
      orderCount: 7
    }
  ];

  const demoMessages = {
    1: [
      { id: 1, sender: 'user', text: 'Xin chào, tôi cần hỗ trợ về sản phẩm iPhone 15', time: '10:30', date: 'Hôm nay' },
      { id: 2, sender: 'admin', text: 'Chào bạn, tôi có thể giúp gì cho bạn?', time: '10:31', date: 'Hôm nay' },
      { id: 3, sender: 'user', text: 'Sản phẩm có còn hàng không? Và thời gian giao hàng bao lâu?', time: '10:32', date: 'Hôm nay' },
      { id: 4, sender: 'admin', text: 'iPhone 15 vẫn còn hàng. Thời gian giao hàng từ 2-3 ngày làm việc trong TP.HCM.', time: '10:33', date: 'Hôm nay' },
      { id: 5, sender: 'user', text: 'Cảm ơn, tôi sẽ đặt hàng ngay', time: '10:35', date: 'Hôm nay' }
    ],
    2: [
      { id: 1, sender: 'admin', text: 'Chào bạn, đơn hàng #1234 đã được xác nhận', time: '09:00', date: 'Hôm nay' },
      { id: 2, sender: 'user', text: 'Cảm ơn bạn, khi nào hàng sẽ được giao?', time: '09:05', date: 'Hôm nay' },
      { id: 3, sender: 'admin', text: 'Đơn hàng sẽ được giao vào chiều nay bạn nhé', time: '09:10', date: 'Hôm nay' },
      { id: 4, sender: 'user', text: 'Tuyệt vời, cảm ơn đã hỗ trợ', time: '09:15', date: 'Hôm nay' }
    ],
    3: [
      { id: 1, sender: 'user', text: 'Khi nào có hàng mới Macbook Pro M3?', time: '14:00', date: 'Hôm qua' },
      { id: 2, sender: 'admin', text: 'Dự kiến tuần sau sẽ có hàng bạn nhé', time: '14:05', date: 'Hôm qua' },
      { id: 3, sender: 'user', text: 'Cảm ơn, tôi sẽ đợi', time: '14:10', date: 'Hôm qua' }
    ],
    8: [
      { id: 1, sender: 'user', text: 'Tôi cần tư vấn về chính sách bảo hành sản phẩm', time: '10:00', date: 'Hôm nay' },
      { id: 2, sender: 'admin', text: 'Chào bạn, sản phẩm được bảo hành 12 tháng chính hãng', time: '10:02', date: 'Hôm nay' },
      { id: 3, sender: 'user', text: 'Nếu sản phẩm lỗi thì đổi trả trong bao lâu?', time: '10:05', date: 'Hôm nay' }
    ]
  };

  // State
  let currentUser = null;
  let filteredUsers = [...demoUsers];

  // DOM Elements
  const usersListEl = document.getElementById('usersList');
  const chatHeaderEl = document.getElementById('chatHeader');
  const messagesContainerEl = document.getElementById('messagesContainer');
  const chatMessagesEl = document.getElementById('chatMessages');
  const noChatSelectedEl = document.getElementById('noChatSelected');
  const messageInputContainerEl = document.getElementById('messageInputContainer');
  const messageInputEl = document.getElementById('messageInput');
  const sendMessageBtnEl = document.getElementById('sendMessageBtn');
  const searchUsersEl = document.getElementById('searchUsers');

  // Stats Elements
  const onlineUsersCountEl = document.getElementById('onlineUsersCount');
  const unreadMessagesCountEl = document.getElementById('unreadMessagesCount');
  const totalUsersCountEl = document.getElementById('totalUsersCount');
  const activeChatsCountEl = document.getElementById('activeChatsCount');

  // Initialize
  function init() {
    renderUsersList();
    updateStats();

    // Add event listeners
    searchUsersEl.addEventListener('input', filterUsers);
    messageInputEl.addEventListener('keypress', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    // Auto-refresh messages (simulate real-time)
    setInterval(updateOnlineStatus, 30000);
  }

  // Render users list
  function renderUsersList() {
    usersListEl.innerHTML = '';

    filteredUsers.forEach(user => {
      const userEl = document.createElement('div');
      userEl.className = `user-item ${currentUser?.id === user.id ? 'active' : ''}`;
      userEl.onclick = () => selectUser(user);

      const statusClass = user.online ? 'online' : 'offline';
      const unreadBadge = user.unread > 0
        ? `<div class="unread-count">${user.unread}</div>`
        : '';

      userEl.innerHTML = `
                <div class="user-avatar" style="background: ${getAvatarColor(user.id)};">
                    ${user.avatar}
                </div>
                <div class="user-info">
                    <div class="user-name">${user.username}</div>
                    <div class="user-email">${user.email}</div>
                    <div class="last-message">${user.lastMessage}</div>
                </div>
                <div class="user-status">
                    <div class="status-indicator ${statusClass}"></div>
                    <div class="last-message-time">${user.lastMessageTime}</div>
                    ${unreadBadge}
                </div>
            `;

      usersListEl.appendChild(userEl);
    });
  }

  // Filter users
  function filterUsers() {
    const searchTerm = searchUsersEl.value.toLowerCase();
    filteredUsers = demoUsers.filter(user =>
      user.username.toLowerCase().includes(searchTerm) ||
      user.email.toLowerCase().includes(searchTerm)
    );
    renderUsersList();
  }

  // Select user
  function selectUser(user) {
    currentUser = user;

    // Update UI
    document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');

    // Update chat header
    document.getElementById('currentUserAvatar').textContent = user.avatar;
    document.getElementById('currentUserAvatar').style.background = getAvatarColor(user.id);
    document.getElementById('currentUserName').textContent = user.username;
    document.getElementById('currentUserStatus').textContent = user.online ? 'Online' : 'Offline';
    document.getElementById('currentUserLastSeen').textContent = user.online ? '' : ` • ${user.lastSeen}`;

    // Show chat interface
    chatHeaderEl.style.display = 'flex';
    noChatSelectedEl.style.display = 'none';
    chatMessagesEl.style.display = 'block';
    messageInputContainerEl.style.display = 'flex';

    // Load messages
    loadMessages(user.id);

    // Clear unread count
    user.unread = 0;
    updateStats();
    renderUsersList();
  }

  // Load messages for user
  function loadMessages(userId) {
    const messages = demoMessages[userId] || [];
    chatMessagesEl.innerHTML = '';

    let currentDate = '';

    messages.forEach(msg => {
      // Add date divider if date changed
      if (msg.date !== currentDate) {
        currentDate = msg.date;
        const dateDivider = document.createElement('div');
        dateDivider.className = 'message-date-divider';
        dateDivider.innerHTML = `<span class="date-label">${msg.date}</span>`;
        chatMessagesEl.appendChild(dateDivider);
      }

      const messageEl = document.createElement('div');
      messageEl.className = `message-item ${msg.sender}`;

      const avatarText = msg.sender === 'admin' ? 'A' : currentUser.avatar;
      const avatarColor = msg.sender === 'admin' ? '#3498db' : getAvatarColor(currentUser.id);

      messageEl.innerHTML = `
                <div class="message-avatar" style="background: ${avatarColor};">${avatarText}</div>
                <div class="message-content">
                    <p class="message-text">${msg.text}</p>
                    <div class="message-time">
                        <span>${msg.time}</span>
                        ${msg.sender === 'admin' ? '<span class="message-status"><i class="fas fa-check-double"></i></span>' : ''}
                    </div>
                </div>
            `;

      chatMessagesEl.appendChild(messageEl);
    });

    // Scroll to bottom
    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;

    // Show typing indicator if user is online and last message is from admin
    if (currentUser.online && messages.length > 0 && messages[messages.length - 1].sender === 'admin') {
      showTypingIndicator();
    }
  }

  // Send message
  function sendMessage() {
    const text = messageInputEl.value.trim();
    if (!text || !currentUser) return;

    // Add message to demo data
    const newMessage = {
      id: Date.now(),
      sender: 'admin',
      text: text,
      time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }),
      date: 'Hôm nay'
    };

    if (!demoMessages[currentUser.id]) {
      demoMessages[currentUser.id] = [];
    }
    demoMessages[currentUser.id].push(newMessage);

    // Clear input
    messageInputEl.value = '';
    autoResizeTextarea(messageInputEl);

    // Reload messages
    loadMessages(currentUser.id);

    // Update user's last message
    currentUser.lastMessage = text.length > 30 ? text.substring(0, 30) + '...' : text;
    currentUser.lastMessageTime = newMessage.time;
    currentUser.unread = 0;

    // Simulate user reply after 2 seconds if online
    if (currentUser.online) {
      setTimeout(() => {
        simulateUserReply(text);
      }, 2000);
    }

    renderUsersList();
  }

  // Simulate user reply
  function simulateUserReply(adminMessage) {
    const replies = [
      "Cảm ơn bạn đã phản hồi",
      "Tôi hiểu rồi, cảm ơn",
      "Được, tôi sẽ làm theo hướng dẫn",
      "Tôi cần thêm thông tin",
      "Khi nào thì có kết quả?",
      "Tuyệt vời, cảm ơn bạn"
    ];

    const randomReply = replies[Math.floor(Math.random() * replies.length)];

    const userReply = {
      id: Date.now(),
      sender: 'user',
      text: randomReply,
      time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }),
      date: 'Hôm nay'
    };

    demoMessages[currentUser.id].push(userReply);
    loadMessages(currentUser.id);

    // Update user's last message
    currentUser.lastMessage = userReply.text;
    currentUser.lastMessageTime = userReply.time;
    currentUser.unread = 1;

    renderUsersList();
    updateStats();
  }

  // Show typing indicator
  function showTypingIndicator() {
    const typingEl = document.createElement('div');
    typingEl.className = 'typing-indicator';
    typingEl.innerHTML = `
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
            <span>Đang soạn tin nhắn...</span>
        `;

    chatMessagesEl.appendChild(typingEl);
    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;

    // Remove after 3 seconds
    setTimeout(() => {
      if (typingEl.parentNode) {
        typingEl.remove();
      }
    }, 3000);
  }

  // Update statistics
  function updateStats() {
    const onlineUsers = demoUsers.filter(u => u.online).length;
    const unreadMessages = demoUsers.reduce((sum, user) => sum + user.unread, 0);
    const activeChats = Object.keys(demoMessages).length;

    onlineUsersCountEl.textContent = onlineUsers;
    unreadMessagesCountEl.textContent = unreadMessages;
    totalUsersCountEl.textContent = demoUsers.length;
    activeChatsCountEl.textContent = activeChats;
  }

  // Update online status (simulate)
  function updateOnlineStatus() {
    // Randomly toggle some users' online status
    demoUsers.forEach(user => {
      if (Math.random() > 0.7) {
        user.online = !user.online;
        user.lastSeen = user.online ? 'Vừa xong' : `${Math.floor(Math.random() * 60) + 1} phút trước`;
      }
    });

    if (currentUser) {
      document.getElementById('currentUserStatus').textContent = currentUser.online ? 'Online' : 'Offline';
      document.getElementById('currentUserLastSeen').textContent = currentUser.online ? '' : ` • ${currentUser.lastSeen}`;
    }

    renderUsersList();
    updateStats();
  }

  // Helper functions
  function getAvatarColor(userId) {
    const colors = ['#3498db', '#2ecc71', '#e74c3c', '#9b59b6', '#1abc9c', '#f39c12', '#34495e', '#16a085'];
    return colors[userId % colors.length];
  }

  function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
  }

  // Modal functions
  function showUserInfo() {
    if (!currentUser) return;

    const userInfoContent = document.getElementById('userInfoContent');
    userInfoContent.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: ${getAvatarColor(currentUser.id)}; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; margin: 0 auto 15px;">
                    ${currentUser.avatar}
                </div>
                <h4 style="margin: 0;">${currentUser.username}</h4>
                <p style="color: #7f8c8d; margin: 5px 0;">${currentUser.email}</p>
                <div class="status-badge ${currentUser.online ? 'status-delivered' : 'status-pending'}" style="display: inline-block;">
                    ${currentUser.online ? 'Online' : 'Offline'}
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <h5 style="margin-top: 0; color: #2c3e50;">Thông tin liên hệ</h5>
                <p><strong>Số điện thoại:</strong> ${currentUser.phone}</p>
                <p><strong>Tham gia từ:</strong> ${currentUser.joinDate}</p>
                <p><strong>Số đơn hàng:</strong> ${currentUser.orderCount} đơn</p>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                <h5 style="margin-top: 0; color: #2c3e50;">Thống kê chat</h5>
                <p><strong>Tin nhắn chưa đọc:</strong> ${currentUser.unread}</p>
                <p><strong>Tin nhắn cuối:</strong> ${currentUser.lastMessageTime}</p>
                <p><strong>Trạng thái:</strong> ${currentUser.online ? 'Đang hoạt động' : 'Không hoạt động'}</p>
            </div>
        `;

    document.getElementById('userInfoModal').style.display = 'flex';
  }

  function closeUserInfoModal() {
    document.getElementById('userInfoModal').style.display = 'none';
  }

  function viewUserDetails() {
    alert(`Chuyển đến trang chi tiết người dùng: ${currentUser.username}`);
    closeUserInfoModal();
  }

  function toggleAttachments() {
    alert('Tính năng đính kèm file đang được phát triển');
  }

  function clearChat() {
    if (confirm('Bạn có chắc muốn xoá toàn bộ đoạn chat với người dùng này?')) {
      if (currentUser && demoMessages[currentUser.id]) {
        delete demoMessages[currentUser.id];
        loadMessages(currentUser.id);
        currentUser.lastMessage = 'Chưa có tin nhắn';
        currentUser.lastMessageTime = '';
        renderUsersList();
      }
    }
  }

  function attachFile() {
    alert('Chức năng đính kèm file đang được phát triển');
  }

  // Initialize on load
  document.addEventListener('DOMContentLoaded', init);
</script>
