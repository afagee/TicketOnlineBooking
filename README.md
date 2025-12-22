# ProjectWeb - Cinema Booking (PHP)

Ứng dụng đặt vé xem phim đơn giản viết bằng PHP (thuần), lưu trữ dữ liệu dưới dạng JSON, không cần DB. Giao diện tĩnh (HTML/CSS/JS) gọi API PHP.

## Cách chạy

### Windows
Double-click vào `start.bat`

### Linux / macOS
```bash
./start.sh
```

### Hoặc chạy trực tiếp
```bash
php -S 0.0.0.0:8080
```

Sau đó truy cập: http://localhost:8080

Truy cập LAN: `http://<ip-máy>:8080` (ví dụ 192.168.x.x:8080)

## Tài khoản mặc định
- **Admin:** `admin / admin`

## Cấu trúc thư mục
- `index.php`, `login.php`, `movie.php`, `seats.php`, `admin.php`, `logout.php`: entry web pages (view layer) gọi JS tương ứng.
- `start.bat`, `start.sh`: script khởi động server (Windows/Linux).
- `api/`
  - `index.php`: API router duy nhất, nhận `?route=` và gọi controllers/services.
  - `util.php`: bootstrap + container khởi tạo model/service cho API.
- `app/`
  - `Core/`: `JsonStore` (đọc/ghi JSON), `View` (render PHP view).
  - `Models/`: `MovieModel` (có tính năng lịch chiếu động), `UserModel`, `BookingModel`, `SeatHoldModel`.
  - `Services/`: `AuthService`, `BookingService` (giữ ghế, đặt vé, lưu phim).
  - `Controllers/`
    - `Web/`: Home, Auth, Movie, Seats, Admin (render view).
    - `Api/`: MoviesApi, AuthApi, BookingApi, AdminApi (logic API).
  - `Views/`: `home.php`, `login.php`, `movie.php`, `seats.php`, `admin.php`.
- `assets/`: frontend tĩnh
  - `styles.css`: giao diện (theme tím dusk).
  - `script.js`: trang chủ (danh sách phim, vé đã đặt).
  - `movie.js`: trang chi tiết phim + trailer YouTube + chọn suất.
  - `seats.js`: trang chọn ghế, giữ ghế, xác nhận.
  - `auth.js`: đăng nhập/đăng ký.
  - `admin.js`: quản lý phim, reset dữ liệu.
- `data/`: JSON lưu trữ
  - `movies.json`: phim, suất chiếu, poster, trailer, mô tả.
  - `users.json`: tài khoản (mặc định admin/admin).
  - `bookings.json`: vé đã đặt.
  - `seat_holds.json`: ghế đang giữ theo session.

## API (qua `api/index.php?route=...`)
- Public: `movies`, `auth/me`, `auth/login` (POST), `auth/register` (POST)
- Booking (cần đăng nhập): `seat-map`, `hold` (POST), `hold/release` (POST), `booking/confirm` (POST), `my-bookings`, `my-bookings/cancel` (POST)
- Admin: `admin/movie/save` (POST), `admin/movie/delete` (POST), `admin/reset` (POST), `admin/booking/cancel` (POST)

## Ghi chú
- Dữ liệu lưu file JSON; không cần DB.
- Session PHP dùng để xác định user và giữ ghế theo phiên.

## Cách hoạt động (flow)
1) **Trang chủ** (`index.php` + `assets/script.js`):
   - Gọi `movies` để render danh sách phim.
   - Gọi `auth/me` và `my-bookings` để hiển thị vé của user đang đăng nhập; cho phép hủy vé (`my-bookings/cancel`).
2) **Xem phim & chọn suất** (`movie.php` + `assets/movie.js`):
   - Gọi `movies` để lấy chi tiết phim theo `id`, hiển thị trailer (YouTube embed) và suất chiếu.
   - Chọn suất => điều hướng sang `seats.php?showId=...`.
3) **Chọn ghế & đặt vé** (`seats.php` + `assets/seats.js`):
   - Gọi `seat-map` để lấy trạng thái ghế (trống/đang giữ/đã đặt) + TTL giữ chỗ.
   - Giữ ghế: `hold` (POST {showId, seats}) kiểm tra trùng ghế đã đặt hoặc đang giữ bởi session khác.
   - Hủy giữ: `hold/release` (POST {showId}).
   - Xác nhận: `booking/confirm` (POST {showId}) kiểm tra xung đột và ghi vào `bookings.json`, xoá hold của phiên.
4) **Đăng nhập / Đăng ký** (`login.php` + `assets/auth.js`):
   - `auth/login` lưu user vào session; `auth/register` thêm user mới và auto đăng nhập; `auth/logout` xóa session.
5) **Admin** (`admin.php` + `assets/admin.js`):
   - Quản lý phim: `admin/movie/save`, `admin/movie/delete`.
   - Reset dữ liệu ghế/đặt: `admin/reset`.
   - (Tùy chọn) Hủy vé người dùng: `admin/booking/cancel`.
6) **Models/Services**:
   - `BookingService`: giữ ghế (theo session), dọn hold hết hạn, xác nhận đặt, tính giá, hủy vé, reset.
   - `AuthService`: quản lý user, đảm bảo admin mặc định, kiểm tra quyền.
7) **Lưu trữ**:
   - `movies.json`: phim/suất, poster, trailer (lịch chiếu được điều chỉnh động).
   - `seat_holds.json`: các ghế đang được giữ, có `session`, `expiresAt`.
   - `bookings.json`: vé đã đặt (showId, seats, user|session, giá, thời gian).
   - `users.json`: user đã đăng ký (password hash).

## Yêu cầu hệ thống
- PHP >= 7.4
- Không cần cài đặt thêm extension hay database
