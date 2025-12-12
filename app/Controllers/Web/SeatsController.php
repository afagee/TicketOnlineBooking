<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\View;
use App\Services\AuthService;

class SeatsController
{
    private View $view;
    private AuthService $auth;

    public function __construct(View $view, AuthService $auth)
    {
        $this->view = $view;
        $this->auth = $auth;
    }

    public function show(string $showId): void
    {
        if (!$this->auth->currentUser()) {
            header('Location: /login.php');
            return;
        }
        $this->view->render('seats', ['showId' => $showId]);
    }
}

