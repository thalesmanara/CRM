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
        if (isset($map[$path])) {
            [$class, $action] = $map[$path];
            $controller = new $class();
            $controller->{$action}($this->request);
            return;
        }
        $patterns = $this->routes['_patterns'][$method] ?? [];
        foreach ($patterns as $rule) {
            if (!preg_match($rule['pattern'], $path, $m)) {
                continue;
            }
            $params = [];
            foreach ($rule['params'] as $i => $name) {
                $params[$name] = $m[$i + 1] ?? '';
            }
            $this->request->setRouteParams($params);
            [$class, $action] = $rule['handler'];
            $controller = new $class();
            $controller->{$action}($this->request);
            return;
        }
        http_response_code(404);
        echo 'Página não encontrada.';
        exit;
    }
}
