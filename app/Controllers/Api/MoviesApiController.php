<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\MovieModel;

class MoviesApiController
{
    private MovieModel $movies;

    public function __construct(MovieModel $movies)
    {
        $this->movies = $movies;
    }

    public function index(): array
    {
        return $this->movies->all();
    }
}

