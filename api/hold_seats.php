<?php
require_once __DIR__ . '/util.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$showId = $input['showId'] ?? '';
$seats = $input['seats'] ?? [];

if ($showId === '' || empty($seats) || !is_array($seats)) {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu showId hoặc danh sách ghế']);
    exit;
}

$validSeats = seat_template();
foreach ($seats as $seat) {
    if (!in_array($seat, $validSeats, true)) {
        http_response_code(422);
        echo json_encode(['message' => "Ghế $seat không hợp lệ"]);
        exit;
    }
}

$now = time();
$holds = read_json(HOLDS_FILE, []);
$holds = cleanup_expired_holds($holds, $now);
$bookings = read_json(BOOKINGS_FILE, []);

// Check booked
foreach ($bookings as $b) {
    if (($b['showId'] ?? null) === $showId) {
        foreach ($b['seats'] as $s) {
            if (in_array($s, $seats, true)) {
                http_response_code(409);
                echo json_encode(['message' => "Ghế $s đã được đặt"]);
                exit;
            }
        }
    }
}

// Check holds
foreach ($holds as $hold) {
    if (($hold['showId'] ?? '') !== $showId) {
        continue;
    }
    if ($hold['session'] !== current_session_id()) {
        foreach ($hold['seats'] as $s) {
            if (in_array($s, $seats, true)) {
                http_response_code(409);
                echo json_encode(['message' => "Ghế $s đang được giữ bởi phiên khác"]);
                exit;
            }
        }
    }
}

// Remove old hold for this session & show
$holds = array_values(array_filter($holds, function ($h) use ($showId) {
    return !($h['showId'] === $showId && $h['session'] === current_session_id());
}));

$holds[] = [
    'session' => current_session_id(),
    'showId' => $showId,
    'seats' => array_values(array_unique($seats)),
    'expiresAt' => $now + HOLD_TTL_SECONDS,
];

write_json(HOLDS_FILE, $holds);

echo json_encode([
    'message' => 'Đã giữ ghế trong 5 phút',
    'expiresAt' => $now + HOLD_TTL_SECONDS,
], JSON_UNESCAPED_UNICODE);

