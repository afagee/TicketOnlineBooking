<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    private UserModel $users;

    public const DEFAULT_ADMIN = [
        'username' => 'admin',
        'password' => 'admin',
        'role' => 'admin',
    ];

    public function __construct(UserModel $users)
    {
        $this->users = $users;
        $this->ensureDefaultAdmin();
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function requireLoginJson(): void
    {
        if (!$this->currentUser()) {
            http_response_code(401);
            echo json_encode(['message' => 'Cần đăng nhập']);
            exit;
        }
    }

    public function requireAdminJson(): void
    {
        $user = $this->currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Chỉ admin được phép thao tác']);
            exit;
        }
    }

    public function login(string $username, string $password): ?array
    {
        $users = $this->users->all();
        foreach ($users as $u) {
            if (($u['username'] ?? '') === $username && password_verify($password, $u['password'])) {
                $_SESSION['user'] = ['username' => $u['username'], 'role' => $u['role'] ?? 'user'];
                return $_SESSION['user'];
            }
        }
        return null;
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
    }

    public function register(string $username, string $email, string $password): array
    {
        $users = $this->users->all();
        foreach ($users as $u) {
            if (($u['username'] ?? '') === $username) {
                throw new \RuntimeException('Tài khoản đã tồn tại', 409);
            }
            if (($u['email'] ?? null) === $email) {
                throw new \RuntimeException('Email đã được dùng', 409);
            }
        }

        $users[] = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
        ];
        $this->users->saveAll($users);
        $_SESSION['user'] = ['username' => $username, 'role' => 'user'];
        return $_SESSION['user'];
    }

    private function ensureDefaultAdmin(): void
    {
        $users = $this->users->all();
        $hasAdmin = false;
        foreach ($users as $u) {
            if (($u['username'] ?? '') === self::DEFAULT_ADMIN['username']) {
                $hasAdmin = true;
                break;
            }
        }
        if (!$hasAdmin) {
            $users[] = [
                'username' => self::DEFAULT_ADMIN['username'],
                'password' => password_hash(self::DEFAULT_ADMIN['password'], PASSWORD_DEFAULT),
                'role' => self::DEFAULT_ADMIN['role'],
            ];
            $this->users->saveAll($users);
        }
    }
}

