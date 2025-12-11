<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$showId = $_GET['showId'] ?? '';
if ($showId === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu showId']);
    exit;
}

$now = time();
$holds = read_json(HOLDS_FILE, []);
$holds = cleanup_expired_holds($holds, $now);
write_json(HOLDS_FILE, $holds);

$bookings = read_json(BOOKINGS_FILE, []);
$bookedSeats = [];
foreach ($bookings as $b) {
    if (($b['showId'] ?? null) === $showId) {
        foreach ($b['seats'] as $s) {
            $bookedSeats[$s] = true;
        }
    }
}

$holdsForShow = array_filter($holds, fn($h) => ($h['showId'] ?? '') === $showId);
$seatStatus = [];
foreach (seat_template() as $seat) {
    if (isset($bookedSeats[$seat])) {
        $seatStatus[] = ['code' => $seat, 'status' => 'booked'];
        continue;
    }
    $status = 'available';
    foreach ($holdsForShow as $hold) {
        if (in_array($seat, $hold['seats'], true)) {
            $status = $hold['session'] === current_session_id() ? 'held-you' : 'held';
            break;
        }
    }
    $seatStatus[] = ['code' => $seat, 'status' => $status];
}

echo json_encode([
    'seats' => $seatStatus,
    'holdTtlSeconds' => HOLD_TTL_SECONDS,
    'message' => 'Ghế giữ tạm trong 5 phút',
], JSON_UNESCAPED_UNICODE);

