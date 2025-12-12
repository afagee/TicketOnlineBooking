<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function render(string $view, array $data = []): void
    {
        $path = $this->basePath . '/' . ltrim($view, '/') . '.php';
        if (!file_exists($path)) {
            http_response_code(500);
            echo "View not found: {$path}";
            return;
        }
        extract($data, EXTR_SKIP);
        include $path;
    }
}

