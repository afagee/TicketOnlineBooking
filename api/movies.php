<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$movies = read_json(MOVIES_FILE, []);
echo json_encode($movies, JSON_UNESCAPED_UNICODE);

