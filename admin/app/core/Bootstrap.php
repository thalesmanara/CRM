<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

use Revita\Crm\Controllers\AuthController;
use Revita\Crm\Controllers\DashboardController;
use Revita\Crm\Controllers\InstallController;

final class Bootstrap
{
    public static function run(): void
    {
        Session::start();

        $request = new Request();
        $path = $request->path();
        $installed = Config::isInstalled();

        if ($installed && ($path === '/install' || str_starts_with($path, '/install'))) {
            http_response_code(403);
            echo 'Instalação já concluída. Remova o acesso ao instalador.';
            exit;
        }

        if (!$installed && $path !== '/install') {
            \Revita\Crm\Helpers\Url::redirect('/install');
        }

        if ($installed) {
            $routes = require REVITA_CRM_ROOT . '/routes/web.php';
        } else {
            $routes = [
                'GET' => [
                    '/install' => [InstallController::class, 'showForm'],
                ],
                'POST' => [
                    '/install' => [InstallController::class, 'submit'],
                ],
            ];
        }

        $router = new Router($request, $routes);
        $router->dispatch();
    }
}
