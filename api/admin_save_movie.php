<?php
require_once __DIR__ . '/util.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['message' => 'Payload không hợp lệ']);
    exit;
}

$title = trim($input['title'] ?? '');
$duration = (int)($input['duration'] ?? 0);
$price = (int)($input['price'] ?? 0);
$poster = trim($input['poster'] ?? '');
$description = trim($input['description'] ?? '');
$showtimesRaw = $input['showtimes'] ?? [];

if ($title === '' || $duration <= 0 || $price < 0 || $poster === '' || $description === '' || empty($showtimesRaw)) {
    http_response_code(422);
    echo json_encode(['message' => 'Thiếu thông tin bắt buộc']);
    exit;
}

$movies = read_json(MOVIES_FILE, []);
$id = $input['id'] ?: 'mv-' . substr(uniqid(), -6);

$showtimes = [];
foreach ($showtimesRaw as $idx => $time) {
    $showtimes[] = [
        'id' => $id . '-' . ($idx + 1),
        'time' => $time,
    ];
}

$found = false;
foreach ($movies as &$movie) {
    if ($movie['id'] === $id) {
        $movie = [
            'id' => $id,
            'title' => $title,
            'duration' => $duration,
            'poster' => $poster,
            'description' => $description,
            'showtimes' => $showtimes,
        ];
        $found = true;
        break;
    }
}
unset($movie);

if (!$found) {
    $movies[] = [
        'id' => $id,
        'title' => $title,
        'duration' => $duration,
        'price' => $price,
        'price' => $price,
        'poster' => $poster,
        'description' => $description,
        'showtimes' => $showtimes,
    ];
}

write_json(MOVIES_FILE, $movies);

echo json_encode(['message' => 'Đã lưu phim', 'id' => $id], JSON_UNESCAPED_UNICODE);

