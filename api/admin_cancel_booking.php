<?php
require_once __DIR__ . '/util.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$showId = $input['showId'] ?? '';
$seats = $input['seats'] ?? [];
$username = $input['username'] ?? null;
$session = $input['session'] ?? null;

if ($showId === '' || empty($seats)) {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu showId hoặc seats']);
    exit;
}

$bookings = read_json(BOOKINGS_FILE, []);
$changed = false;
foreach ($bookings as $idx => $b) {
    if (($b['showId'] ?? '') !== $showId) {
        continue;
    }
    if ($username && ($b['user'] ?? null) !== $username) {
        continue;
    }
    if (!$username && $session && ($b['session'] ?? null) !== $session) {
        continue;
    }
    $remaining = array_values(array_diff($b['seats'], $seats));
    if (count($remaining) === count($b['seats'])) {
        continue;
    }
    if (empty($remaining)) {
        unset($bookings[$idx]);
    } else {
        $bookings[$idx]['seats'] = $remaining;
        $bookings[$idx]['totalPrice'] = ($b['pricePerSeat'] ?? 0) * count($remaining);
    }
    $changed = true;
}

if ($changed) {
    $bookings = array_values($bookings);
    write_json(BOOKINGS_FILE, $bookings);
}

echo json_encode(['message' => $changed ? 'Đã hủy vé' : 'Không tìm thấy vé để hủy']);

