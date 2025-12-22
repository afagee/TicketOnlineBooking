<?php
/** @var string $showId */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chọn ghế - Cinema Booking</title>
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <a href="/">Cinema Booking</a>
    </div>
    <nav>
      <a href="javascript:history.back()" class="ghost">Quay lại</a>
    </nav>
  </header>

  <main class="container">
    <section class="card movie-detail-card">
      <div class="booking-header">
        <div class="movie-title">
          <p class="eyebrow">Đang đặt vé</p>
          <h2 id="selected-movie">Đang tải...</h2>
          <p id="selected-time" class="muted">...</p>
          <span class="price-tag" id="selected-price">...</span>
        </div>
      </div>

      <div class="legend">
        <span><span class="seat available" style="width:18px;height:18px;"></span> Trống</span>
        <span><span class="seat held-you" style="width:18px;height:18px;"></span> Đang chọn</span>
        <span><span class="seat held" style="width:18px;height:18px;"></span> Người khác giữ</span>
        <span><span class="seat booked" style="width:18px;height:18px;"></span> Đã đặt</span>
      </div>

      <div class="screen" style="margin-top:10px; margin-bottom:20px;">
        MÀN HÌNH
      </div>

      <div id="seats" class="seat-grid"></div>

      <div class="booking" style="margin-top:24px;">
        <div class="booking-header">
          <div>
            <div id="summary">Chọn ghế để tính tiền.</div>
            <div id="status" class="muted" style="margin-top:4px;"></div>
          </div>
          <div class="actions">
            <button class="ghost" id="release-btn">Hủy giữ ghế</button>
            <button class="primary" id="confirm-btn">Xác nhận đặt vé</button>
          </div>
        </div>
        <p class="muted" style="margin-top:8px; font-size:12px;">Ghế sẽ được giữ trong 5 phút.</p>
      </div>
    </section>
  </main>

  <script>
    window.__SHOW_ID__ = <?= json_encode($showId) ?>;
  </script>
  <script src="/assets/seats.js"></script>
</body>
</html>
