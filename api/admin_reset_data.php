<?php
require_once __DIR__ . '/util.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

// Clear bookings and holds
write_json(BOOKINGS_FILE, []);
write_json(HOLDS_FILE, []);

echo json_encode(['message' => 'Đã reset toàn bộ ghế đã đặt và giữ chỗ']);

