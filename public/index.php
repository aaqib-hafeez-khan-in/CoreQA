<?php

declare(strict_types=1);

/**
 * Global helpers
 */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

/**
 * PSR-4 style manual autoloader with Linux casing support
 */
spl_autoload_register(function ($c) {
    $file = str_replace('\\', '/', $c);
    if (strpos($file, 'App/') === 0) {
        $file = 'app' . substr($file, 3);
    }
    $path = __DIR__ . '/../' . $file . '.php';
    if (is_file($path)) {
        require $path;
    }
});

/**
 * Bootstrap the application
 */
$config = require __DIR__ . '/../config/config.php';
session_name($config['session_name']);
session_start();

$container = new App\Core\Container($config);
$router    = new App\Core\Router($container);

($routes = require __DIR__ . '/../config/routes.php')($router);

$path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
