<?php
require_once __DIR__ . '/api/util.php';
load_users(); // đảm bảo có admin mặc định
$user = current_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
  header('Location: /login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản trị phim</title>
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">Cinema Booking</div>
    <nav>
      <a href="/index.php">Về trang đặt vé</a>
      <a href="/logout.php">Đăng xuất</a>
    </nav>
  </header>

  <main class="container">
    <section class="hero">
      <div>
        <p class="eyebrow">Quản lý nội dung</p>
        <h1>Thêm phim và suất chiếu</h1>
      </div>
      <div class="actions">
        <button id="reset-data-btn" class="ghost">Reset ghế đã đặt</button>
      </div>
    </section>

    <section class="form-card">
      <h2>Thêm / cập nhật phim</h2>
      <form id="movie-form">
        <input type="hidden" id="movie-id">
        <label>Tiêu đề
          <input type="text" id="title" required>
        </label>
        <label>Thời lượng (phút)
          <input type="number" id="duration" min="1" required>
        </label>
        <label>Giá vé (đồng)
          <input type="number" id="price" min="0" step="1000" required>
        </label>
        <label>Poster (URL)
          <input type="url" id="poster" required>
        </label>
        <label>Mô tả
          <textarea id="description" rows="3" required></textarea>
        </label>
        <div class="form-row">
          <div>
            <label>Ngày chiếu
              <input type="date" id="show-date">
            </label>
          </div>
          <div>
            <label>Giờ chiếu
              <input type="time" id="show-time">
            </label>
          </div>
          <div class="actions">
            <button type="button" id="add-showtime" class="primary">Thêm suất</button>
          </div>
        </div>
        <div>
          <p class="eyebrow">Suất chiếu đã nhập</p>
          <ul id="showtime-list" class="list-plain"></ul>
        </div>
        <div class="actions">
          <button type="submit" class="primary">Lưu phim</button>
          <button type="button" id="reset-btn" class="ghost">Làm mới</button>
        </div>
      </form>
    </section>

    <section>
      <h2>Danh sách phim</h2>
      <div id="movie-list" class="card-grid"></div>
    </section>
  </main>

  <script src="/assets/admin.js"></script>
</body>
</html>

