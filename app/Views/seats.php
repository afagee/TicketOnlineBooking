<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chọn ghế</title>
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">Cinema Booking</div>
    <nav>
      <a href="/index.php">Trang chủ</a>
    </nav>
  </header>

  <main class="container">
    <section class="hero">
      <div>
        <p class="eyebrow">Bước 3</p>
        <h1>Chọn ghế và thanh toán</h1>
        <p>Ghế được giữ tạm 5 phút theo phiên làm việc.</p>
      </div>
    </section>

    <section class="booking">
      <div class="booking-header">
        <div>
          <p class="eyebrow">Suất chiếu</p>
          <h2 id="selected-movie"></h2>
          <p id="selected-time"></p>
          <p id="selected-price" class="muted"></p>
        </div>
        <div class="actions">
          <button id="release-btn" class="ghost">Hủy giữ ghế</button>
          <button id="confirm-btn" class="primary">Xác nhận đặt vé</button>
        </div>
      </div>

      <div class="legend">
        <span><span class="seat available"></span> Trống</span>
        <span><span class="seat held-you"></span> Đang giữ (bạn)</span>
        <span><span class="seat held"></span> Đang giữ</span>
        <span><span class="seat booked"></span> Đã đặt</span>
      </div>

      <div id="seats" class="seat-grid"></div>
      <p id="summary" class="muted"></p>
      <p id="status"></p>
    </section>
  </main>

  <script>
    window.__SHOW_ID__ = "<?php echo htmlspecialchars($showId ?? '', ENT_QUOTES); ?>";
  </script>
  <script src="/assets/seats.js"></script>
</body>
</html>

