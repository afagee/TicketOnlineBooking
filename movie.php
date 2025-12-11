<?php
session_start();
$movieId = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chọn suất chiếu</title>
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
        <p class="eyebrow">Bước 2</p>
        <h1>Chọn suất chiếu</h1>
        <p>Chọn khung giờ để chuyển sang chọn ghế.</p>
      </div>
    </section>

    <section id="movie-detail" class="card movie-detail-card"></section>
  </main>

  <script>
    window.__MOVIE_ID__ = "<?php echo htmlspecialchars($movieId, ENT_QUOTES); ?>";
  </script>
  <script src="/assets/movie.js"></script>
</body>
</html>


