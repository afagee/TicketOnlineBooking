<?php
session_start();

const MOVIES_FILE = __DIR__ . '/../data/movies.json';
const BOOKINGS_FILE = __DIR__ . '/../data/bookings.json';
const HOLDS_FILE = __DIR__ . '/../data/seat_holds.json';
const USERS_FILE = __DIR__ . '/../data/users.json';
const HOLD_TTL_SECONDS = 300; // 5 minutes
const DEFAULT_ADMIN = ['username' => 'admin', 'password' => 'admin', 'role' => 'admin'];

function read_json(string $file, $default = [])
{
    if (!file_exists($file)) {
        return $default;
    }
    $content = file_get_contents($file);
    if ($content === false || $content === '') {
        return $default;
    }
    $data = json_decode($content, true);
    return $data ?? $default;
}

function write_json(string $file, $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $fp = fopen($file, 'c+');
    if (!$fp) {
        return false;
    }
    // Lock to avoid race conditions
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    ftruncate($fp, 0);
    rewind($fp);
    $result = fwrite($fp, $json) !== false;
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $result;
}

function seat_template(): array
{
    $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    $cols = range(1, 10);
    $seats = [];
    foreach ($rows as $row) {
        foreach ($cols as $col) {
            $seats[] = $row . $col;
        }
    }
    return $seats;
}

function cleanup_expired_holds(array $holds, int $now): array
{
    return array_values(array_filter($holds, function ($hold) use ($now) {
        return isset($hold['expiresAt']) && $hold['expiresAt'] > $now;
    }));
}

function current_session_id(): string
{
    return session_id();
}

function load_users(): array
{
    $users = read_json(USERS_FILE, []);
    $hasAdmin = false;
    foreach ($users as $u) {
        if (($u['username'] ?? '') === DEFAULT_ADMIN['username']) {
            $hasAdmin = true;
            break;
        }
    }
    if (!$hasAdmin) {
        $users[] = [
            'username' => DEFAULT_ADMIN['username'],
            'password' => password_hash(DEFAULT_ADMIN['password'], PASSWORD_DEFAULT),
            'role' => 'admin',
        ];
        write_json(USERS_FILE, $users);
    }
    return $users;
}

function save_users(array $users): void
{
    write_json(USERS_FILE, $users);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        http_response_code(401);
        echo json_encode(['message' => 'Cần đăng nhập']);
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['message' => 'Chỉ admin được phép thao tác']);
        exit;
    }
}

