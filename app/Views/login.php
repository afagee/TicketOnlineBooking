<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập / Đăng ký</title>
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
      <h1>Đăng nhập hoặc đăng ký</h1>
      <p>Vui lòng đăng nhập để đặt vé online.</p>
    </section>

    <section class="form-card">
      <div class="tab-row">
        <button class="tab-btn active" data-tab="login">Đăng nhập</button>
        <button class="tab-btn" data-tab="register">Đăng ký</button>
      </div>
      <form id="login-form" class="auth-form">
        <label>Tên đăng nhập
          <input type="text" id="login-username" required>
        </label>
        <label>Mật khẩu
          <input type="password" id="login-password" required>
        </label>
        <button type="submit" class="primary">Đăng nhập</button>
      </form>

      <form id="register-form" class="auth-form hidden">
        <label>Tên đăng nhập
          <input type="text" id="register-username" required>
        </label>
        <label>Email (Gmail)
          <input type="email" id="register-email" required>
        </label>
        <label>Mật khẩu
          <input type="password" id="register-password" required>
        </label>
        <button type="submit" class="primary">Đăng ký</button>
      </form>

      <p id="auth-status"></p>
    </section>
  </main>

  <script src="/assets/auth.js"></script>
</body>
</html>

