<?php
require_once __DIR__ . '/util.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$id = trim($input['id'] ?? '');

if ($id === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu id']);
    exit;
}

$movies = read_json(MOVIES_FILE, []);
$movies = array_values(array_filter($movies, fn($m) => $m['id'] !== $id));
write_json(MOVIES_FILE, $movies);

echo json_encode(['message' => 'Đã xóa phim']);

