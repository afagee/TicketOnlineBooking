<?php
require_once __DIR__ . '/util.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$showId = $input['showId'] ?? '';

if ($showId === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu showId']);
    exit;
}

$now = time();
$holds = read_json(HOLDS_FILE, []);
$holds = cleanup_expired_holds($holds, $now);
$myHold = null;
foreach ($holds as $h) {
    if (($h['showId'] ?? '') === $showId && ($h['session'] ?? '') === current_session_id()) {
        $myHold = $h;
        break;
    }
}

if (!$myHold || empty($myHold['seats'])) {
    http_response_code(409);
    echo json_encode(['message' => 'Bạn chưa giữ ghế nào cho suất này']);
    exit;
}

$bookings = read_json(BOOKINGS_FILE, []);

// Re-check conflicts
foreach ($bookings as $b) {
    if (($b['showId'] ?? null) === $showId) {
        foreach ($b['seats'] as $seat) {
            if (in_array($seat, $myHold['seats'], true)) {
                http_response_code(409);
                echo json_encode(['message' => "Ghế $seat vừa được đặt bởi người khác"]);
                exit;
            }
        }
    }
}

// Determine price per seat from movie config
$movies = read_json(MOVIES_FILE, []);
$pricePerSeat = 0;
foreach ($movies as $mv) {
    foreach ($mv['showtimes'] as $st) {
        if ($st['id'] === $showId) {
            $pricePerSeat = (int)($mv['price'] ?? 0);
            break 2;
        }
    }
}

$totalPrice = $pricePerSeat * count($myHold['seats']);

$booking = [
    'showId' => $showId,
    'seats' => $myHold['seats'],
    'session' => current_session_id(),
    'bookedAt' => date('c', $now),
    'pricePerSeat' => $pricePerSeat,
    'totalPrice' => $totalPrice,
    'paid' => true,
    'user' => current_user()['username'] ?? null,
];
$bookings[] = $booking;
write_json(BOOKINGS_FILE, $bookings);

// remove hold
$holds = array_values(array_filter($holds, function ($h) use ($showId) {
    return !($h['showId'] === $showId && $h['session'] === current_session_id());
}));
write_json(HOLDS_FILE, $holds);

echo json_encode([
    'message' => 'Đặt vé thành công',
    'booking' => $booking,
    'totalPrice' => $totalPrice,
], JSON_UNESCAPED_UNICODE);

