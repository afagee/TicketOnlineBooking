<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu username hoặc password']);
    exit;
}

$users = load_users();
$found = null;
foreach ($users as $u) {
    if (($u['username'] ?? '') === $username) {
        $found = $u;
        break;
    }
}

if (!$found || !password_verify($password, $found['password'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Sai tài khoản hoặc mật khẩu']);
    exit;
}

$_SESSION['user'] = ['username' => $found['username'], 'role' => $found['role']];

echo json_encode(['message' => 'Đăng nhập thành công', 'user' => $_SESSION['user']]);

