<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<string, callable> */
    private array $getRoutes = [];

    /** @var array<string, callable> */
    private array $postRoutes = [];

    public function get(string $path, callable $handler): void
    {
        $this->getRoutes[$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->postRoutes[$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): bool
    {
        $normalizedPath = $this->normalizePath($path);

        $routes = match ($method) {
            'GET' => $this->getRoutes,
            'POST' => $this->postRoutes,
            default => null,
        };

        if ($routes === null || !isset($routes[$normalizedPath])) {
            return false;
        }

        $routes[$normalizedPath]();

        return true;
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
