<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    public function __construct(private Container $c) {}
    public function get(string $p, $h): void { $this->map('GET', $p, $h); }
    public function post(string $p, $h): void { $this->map('POST', $p, $h); }
    private function map(string $m, string $p, $h): void
    {
        $re = preg_replace('#\\{(\\w+):([^}]+)\\}#', '(?P<$1>$2)', $p);
        $this->routes[] = [$m, '#^' . $re . '$#', $h];
    }
    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/') ?: '/';
        foreach ($this->routes as [$routeMethod, $re, $handler]) {
            if ($routeMethod !== $method || !preg_match($re, $uri, $matches)) continue;
            $params = [];
            foreach ($matches as $key => $value) if (is_string($key)) $params[] = $value;
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $object = new $class($this->c);
                $object->$action(...$params);
                return;
            }
            $handler(...$params);
            return;
        }
        http_response_code(404);
        echo 'Not Found';
    }
}
