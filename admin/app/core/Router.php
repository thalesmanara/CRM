<?php

declare(strict_types=1);

namespace Revita\Crm\Core;

final class Router
{
    /**
     * @param array<string, array<string, array{0:class-string,1:string}>> $routes
     */
    public function __construct(
        private readonly Request $request,
        private readonly array $routes
    ) {
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->request->path();
        $map = $this->routes[$method] ?? [];
        if (!isset($map[$path])) {
            http_response_code(404);
            echo 'Página não encontrada.';
            exit;
        }
        [$class, $action] = $map[$path];
        $controller = new $class();
        $controller->{$action}($this->request);
    }
}
