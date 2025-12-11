<?php
require_once __DIR__ . '/util.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$showId = $input['showId'] ?? '';
$seats = $input['seats'] ?? [];

if ($showId === '' || empty($seats) || !is_array($seats)) {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu showId hoặc seats']);
    exit;
}

$user = current_user();
$username = $user['username'] ?? null;
$session = current_session_id();

$bookings = read_json(BOOKINGS_FILE, []);
$changed = false;
foreach ($bookings as $idx => $b) {
    if (($b['showId'] ?? '') !== $showId) {
        continue;
    }
    $isOwner = false;
    if ($username && ($b['user'] ?? null) === $username) {
        $isOwner = true;
    } elseif (!$username && ($b['session'] ?? null) === $session) {
        $isOwner = true;
    }
    if (!$isOwner) {
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
    // also clear holds for these seats
    $holds = read_json(HOLDS_FILE, []);
    $holds = array_values(array_filter($holds, function ($h) use ($showId, $seats) {
        if (($h['showId'] ?? '') !== $showId) return true;
        $newSeats = array_diff($h['seats'] ?? [], $seats);
        $h['seats'] = $newSeats;
        return !empty($newSeats);
    }));
    write_json(HOLDS_FILE, $holds);
}

echo json_encode(['message' => $changed ? 'Đã hủy vé' : 'Không tìm thấy vé để hủy']);

