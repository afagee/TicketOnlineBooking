<?php
require_once __DIR__ . '/util.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
$username = $user['username'] ?? null;
$session = current_session_id();

$bookings = read_json(BOOKINGS_FILE, []);
$movies = read_json(MOVIES_FILE, []);

$movieMap = [];
foreach ($movies as $m) {
    foreach ($m['showtimes'] as $st) {
        $movieMap[$st['id']] = ['title' => $m['title'], 'time' => $st['time'], 'movieId' => $m['id']];
    }
}

$result = [];
foreach ($bookings as $b) {
    $isOwner = false;
    if ($username && isset($b['user']) && $b['user'] === $username) {
        $isOwner = true;
    } elseif (!$username && ($b['session'] ?? '') === $session) {
        $isOwner = true;
    }
    if ($isOwner) {
        $info = $movieMap[$b['showId']] ?? ['title' => $b['showId'], 'time' => '', 'movieId' => null];
        $result[] = [
            'movieTitle' => $info['title'],
            'showTime' => $info['time'],
            'movieId' => $info['movieId'],
            'showId' => $b['showId'],
            'seats' => $b['seats'],
            'totalPrice' => $b['totalPrice'] ?? 0,
            'bookedAt' => $b['bookedAt'] ?? '',
        ];
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);

