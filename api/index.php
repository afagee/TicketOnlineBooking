<?php
declare(strict_types=1);

require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route = $_GET['route'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function bad_request(string $message): void
{
    json_out(['message' => $message], 400);
}

switch ($method . ' ' . $route) {
    case 'GET movies':
        json_out(movies_api()->index());

    case 'GET auth/me':
        json_out(auth_api()->me());

    case 'POST auth/login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        if ($username === '' || $password === '') {
            bad_request('Thiếu username hoặc password');
        }
        $user = auth_api()->login($username, $password);
        if (!$user) {
            json_out(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }
        json_out(['message' => 'Đăng nhập thành công', 'user' => $user]);

    case 'POST auth/logout':
        auth_api()->logout();
        json_out(['message' => 'Đã đăng xuất']);

    case 'POST auth/register':
        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        if ($username === '' || $password === '' || $email === '') {
            bad_request('Thiếu username, email hoặc password');
        }
        if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $email)) {
            json_out(['message' => 'Email phải là gmail hợp lệ'], 422);
        }
        try {
            $user = auth_api()->register($username, $email, $password);
            json_out(['message' => 'Đăng ký thành công', 'user' => $user]);
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 400;
            json_out(['message' => $e->getMessage()], $code);
        }

    case 'GET seat-map':
        $showId = $_GET['showId'] ?? '';
        if ($showId === '') {
            bad_request('Thiếu showId');
        }
        json_out(booking_api()->seatMap($showId, current_session_id()));

    case 'POST hold':
        require_login();
        $showId = $input['showId'] ?? '';
        $seats = $input['seats'] ?? [];
        if ($showId === '' || empty($seats) || !is_array($seats)) {
            bad_request('Thiếu showId hoặc danh sách ghế');
        }
        try {
            $result = booking_api()->hold($showId, $seats, current_session_id());
            json_out($result);
        } catch (InvalidArgumentException $e) {
            json_out(['message' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 409;
            json_out(['message' => $e->getMessage()], $code);
        }

    case 'POST hold/release':
        require_login();
        $showId = $input['showId'] ?? '';
        if ($showId === '') {
            bad_request('Thiếu showId');
        }
        $removed = booking_api()->release($showId, current_session_id());
        json_out(['message' => $removed ? 'Đã hủy giữ ghế' : 'Không có ghế nào đang giữ']);

    case 'POST booking/confirm':
        require_login();
        $showId = $input['showId'] ?? '';
        if ($showId === '') {
            bad_request('Thiếu showId');
        }
        try {
            $result = booking_api()->confirm($showId, current_session_id(), current_user()['username'] ?? null);
            json_out($result);
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 409;
            json_out(['message' => $e->getMessage()], $code);
        }

    case 'GET my-bookings':
        require_login();
        $user = current_user();
        $username = $user['username'] ?? null;
        $session = current_session_id();
        json_out(booking_api()->myBookings($username, $session));

    case 'POST my-bookings/cancel':
        require_login();
        $showId = $input['showId'] ?? '';
        $seats = $input['seats'] ?? [];
        if ($showId === '' || empty($seats) || !is_array($seats)) {
            bad_request('Thiếu showId hoặc seats');
        }
        $user = current_user();
        $username = $user['username'] ?? null;
        $session = current_session_id();
        $changed = booking_api()->cancelMine($showId, $seats, $username, $session);
        json_out(['message' => $changed ? 'Đã hủy vé' : 'Không tìm thấy vé để hủy']);

    case 'POST admin/movie/save':
        require_admin();
        try {
            $result = admin_api()->saveMovie($input);
            json_out($result);
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 400;
            json_out(['message' => $e->getMessage()], $code);
        }

    case 'POST admin/movie/delete':
        require_admin();
        $id = trim($input['id'] ?? '');
        if ($id === '') {
            bad_request('Thiếu id');
        }
        $ok = admin_api()->deleteMovie($id);
        json_out(['message' => $ok ? 'Đã xóa phim' : 'Không xóa được phim']);

    case 'POST admin/reset':
        require_admin();
        booking_api()->resetAll();
        json_out(['message' => 'Đã reset toàn bộ ghế đã đặt và giữ chỗ']);

    case 'POST admin/booking/cancel':
        require_admin();
        $showId = $input['showId'] ?? '';
        $seats = $input['seats'] ?? [];
        $username = $input['username'] ?? null;
        $session = $input['session'] ?? null;
        if ($showId === '' || empty($seats)) {
            bad_request('Thiếu showId hoặc seats');
        }
        $changed = booking_api()->adminCancel($showId, $seats, $username, $session);
        json_out(['message' => $changed ? 'Đã hủy vé' : 'Không tìm thấy vé để hủy']);

    default:
        json_out(['message' => 'Không tìm thấy route'], 404);
}

