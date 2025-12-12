<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthService;

class AuthApiController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function me(): array
    {
        return ['user' => $this->auth->currentUser()];
    }

    public function login(string $username, string $password): ?array
    {
        return $this->auth->login($username, $password);
    }

    public function logout(): void
    {
        $this->auth->logout();
    }

    public function register(string $username, string $email, string $password): array
    {
        return $this->auth->register($username, $email, $password);
    }
}

