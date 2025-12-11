<?php
require_once __DIR__ . '/util.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Thiếu username, email hoặc password']);
    exit;
}

if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $email)) {
    http_response_code(422);
    echo json_encode(['message' => 'Email phải là gmail hợp lệ']);
    exit;
}

$users = load_users();
foreach ($users as $u) {
    if (($u['username'] ?? '') === $username) {
        http_response_code(409);
        echo json_encode(['message' => 'Tài khoản đã tồn tại']);
        exit;
    }
    if (isset($u['email']) && $u['email'] === $email) {
        http_response_code(409);
        echo json_encode(['message' => 'Email đã được dùng']);
        exit;
    }
}

$users[] = [
    'username' => $username,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'user',
];
save_users($users);

$_SESSION['user'] = ['username' => $username, 'role' => 'user'];

echo json_encode(['message' => 'Đăng ký thành công', 'user' => $_SESSION['user']]);

