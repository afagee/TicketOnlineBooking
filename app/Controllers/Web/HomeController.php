<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\View;

class HomeController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function index(): void
    {
        $this->view->render('home');
    }
}

