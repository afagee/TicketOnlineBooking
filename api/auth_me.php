<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
echo json_encode(['user' => $user]);

