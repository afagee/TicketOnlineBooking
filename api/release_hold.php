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

$holds = read_json(HOLDS_FILE, []);
$before = count($holds);
$holds = array_values(array_filter($holds, function ($h) use ($showId) {
    return !($h['showId'] === $showId && $h['session'] === current_session_id());
}));
write_json(HOLDS_FILE, $holds);

$removed = $before - count($holds);
echo json_encode(['message' => $removed ? 'Đã hủy giữ ghế' : 'Không có ghế nào đang giữ']);

