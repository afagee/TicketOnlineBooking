<?php
declare(strict_types=1);

require_once __DIR__ . '/api/util.php';

use App\Core\View;
use App\Controllers\Web\SeatsController;

$view = new View(__DIR__ . '/app/Views');
$controller = new SeatsController($view, auth_service());
$controller->show($_GET['showId'] ?? '');
