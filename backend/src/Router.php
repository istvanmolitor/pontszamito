<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<string, array{0:class-string<Controller>,1:string}> */
    private array $getRoutes = [];

    /** @var array<string, array{0:class-string<Controller>,1:string}> */
    private array $postRoutes = [];

    /** @param array{0:class-string<Controller>,1:string} $handler */
    public function get(string $path, array $handler): void
    {
        $this->getRoutes[$this->normalizePath($path)] = $handler;
    }

    /** @param array{0:class-string<Controller>,1:string} $handler */
    public function post(string $path, array $handler): void
    {
        $this->postRoutes[$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): ?ApiResource
    {
        $normalizedPath = $this->normalizePath($path);

        $routes = match ($method) {
            'GET' => $this->getRoutes,
            'POST' => $this->postRoutes,
            default => null,
        };

        if ($routes === null || !isset($routes[$normalizedPath])) {
            return null;
        }

        [$controllerClass, $controllerMethod] = $routes[$normalizedPath];

        if (!is_subclass_of($controllerClass, Controller::class)) {
            throw new \RuntimeException(sprintf(
                '%s must extend %s',
                $controllerClass,
                Controller::class
            ));
        }

        $controller = new $controllerClass();
        $response = $controller->{$controllerMethod}();

        if (!$response instanceof ApiResource) {
            throw new \RuntimeException(sprintf(
                '%s::%s must return %s',
                $controllerClass,
                $controllerMethod,
                ApiResource::class
            ));
        }

        return $response;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/' . trim($path, '/');
    }
}
