<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\View;
use App\Controllers\Web\MovieController;

$view = new View(__DIR__ . '/app/Views');
$controller = new MovieController($view);
$controller->show($_GET['id'] ?? '');
