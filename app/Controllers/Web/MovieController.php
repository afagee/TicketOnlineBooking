<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\View;

class MovieController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function show(string $movieId): void
    {
        $this->view->render('movie', ['movieId' => $movieId]);
    }
}

