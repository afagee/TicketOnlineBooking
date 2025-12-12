<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\View;
use App\Services\AuthService;

class AuthController
{
    private View $view;
    private AuthService $auth;

    public function __construct(View $view, AuthService $auth)
    {
        $this->view = $view;
        $this->auth = $auth;
    }

    public function loginPage(): void
    {
        $user = $this->auth->currentUser();
        if ($user && ($user['role'] ?? '') === 'admin') {
            header('Location: /admin.php');
            return;
        }
        $this->view->render('login', ['user' => $user]);
    }
}

