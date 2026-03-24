<?php

declare(strict_types=1);

use Revita\Crm\Controllers\AuthController;
use Revita\Crm\Controllers\DashboardController;

return [
    'GET' => [
        '/' => [AuthController::class, 'root'],
        '/login' => [AuthController::class, 'showLogin'],
        '/logout' => [AuthController::class, 'logout'],
        '/dashboard' => [DashboardController::class, 'index'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
    ],
];
