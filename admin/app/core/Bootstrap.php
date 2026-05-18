<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

use Revita\Crm\Controllers\InstallController;

final class Bootstrap
{
    public static function run(): void
    {
        Session::start();
        \Revita\Crm\Helpers\Url::rememberBasePathInSession();

        $request = new Request();
        $installed = Config::isInstalled();

        // Antes de instalar: não usa o Router (evita 404 por path/rewrite em subpastas).
        if (!$installed) {
            $install = new InstallController();
            if ($request->method() === 'POST' && $request->post('db_host') !== null) {
                $install->submit($request);
            } else {
                $install->showForm($request);
            }
            return;
        }

        $path = $request->path();

        if ($path === '/install' || str_starts_with($path, '/install/')) {
            http_response_code(403);
            echo 'Instalação já concluída. Remova o acesso ao instalador.';
            exit;
        }

        if (str_starts_with($path, '/api')) {
            $routes = require REVITA_CRM_ROOT . '/routes/api.php';
        } else {
            $routes = require REVITA_CRM_ROOT . '/routes/web.php';
        }

        $router = new Router($request, $routes);
        $router->dispatch($path);
    }
}
