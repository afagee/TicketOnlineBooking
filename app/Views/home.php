<?php
// Trang chủ - giữ nguyên markup cũ để JS hoạt động
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đặt vé xem phim online</title>
  <link rel="stylesheet" href="/assets/styles.css">
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <a href="/">Cinema Booking</a>
    </div>
    <nav>
      <span id="user-info"></span>
      <a id="login-link" href="/login.php">Đăng nhập</a>
      <a id="logout-link" href="/logout.php" class="hidden">Đăng xuất</a>
    </nav>
  </header>

  <main class="container">
    <!-- Swiper phim nổi bật -->
    <section class="featured-movies">
      <h2>Phim nổi bật</h2>
      <div class="swiper-container phim-hot">
        <div class="swiper-wrapper">
          <!-- Các slide sẽ được thêm bằng JS hoặc viết tay -->
          <!-- Ví dụ tĩnh:
          <div class="swiper-slide">
            <img src="https://placehold.co/300x450" alt="Phim demo">
            <h3>Phim demo</h3>
          </div>
          -->
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
      </div>
    </section>

    <section class="hero">
      <div>
        <p class="eyebrow">Đặt vé online</p>
        <h1>Chọn phim, xem suất chiếu, đặt ghế</h1>
      </div>
    </section>

    <section id="bookings-section" class="card hidden">
      <h2>🎫 Vé đã đặt</h2>
      <div id="bookings-list" class="bookings-grid"></div>
    </section>

    <!-- Modal xem chi tiết vé -->
    <div id="ticket-modal" class="modal hidden">
      <div class="modal-overlay"></div>
      <div class="modal-content ticket-detail">
        <button class="modal-close">&times;</button>
        <div class="ticket-header">
          <span class="ticket-label">VÉ XEM PHIM</span>
          <h2 id="ticket-movie"></h2>
        </div>
        <div class="ticket-body">
          <div class="ticket-row">
            <span class="ticket-icon">📅</span>
            <div>
              <span class="label">Suất chiếu</span>
              <span id="ticket-time" class="value"></span>
            </div>
          </div>
          <div class="ticket-row">
            <span class="ticket-icon">💺</span>
            <div>
              <span class="label">Ghế</span>
              <span id="ticket-seats" class="value seats-display"></span>
            </div>
          </div>
          <div class="ticket-row">
            <span class="ticket-icon">💰</span>
            <div>
              <span class="label">Tổng tiền</span>
              <span id="ticket-price" class="value price"></span>
            </div>
          </div>
          <div class="ticket-row">
            <span class="ticket-icon">🕐</span>
            <div>
              <span class="label">Đặt lúc</span>
              <span id="ticket-booked-at" class="value"></span>
            </div>
          </div>
        </div>
        <div class="ticket-footer">
          <div class="ticket-status">✅ Đã thanh toán</div>
          <button id="ticket-cancel-btn" class="btn-cancel">Hủy vé</button>
        </div>
      </div>
    </div>

    <section id="movies" class="card-grid"></section>
  </main>

  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script src="/assets/script.js"></script>
</body>
</html>

