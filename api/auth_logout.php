<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

session_unset();
session_destroy();

echo json_encode(['message' => 'Đã đăng xuất']);

