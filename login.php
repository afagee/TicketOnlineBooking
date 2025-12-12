<?php
declare(strict_types=1);

require_once __DIR__ . '/api/util.php';

use App\Core\View;
use App\Controllers\Web\AuthController;

$view = new View(__DIR__ . '/app/Views');
$controller = new AuthController($view, auth_service());
$controller->loginPage();

