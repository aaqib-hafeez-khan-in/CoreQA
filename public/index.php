<?php

declare(strict_types=1);

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function redirect(string $to): never { header('Location: ' . $to, true, 302); exit; }

spl_autoload_register(function (string $class): void {
    if (strpos($class, 'App\\') !== 0) return;
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) require $path;
});

$config = require __DIR__ . '/../config/config.php';
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($config['env'] === 'production');
session_name($config['session_name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if ($config['env'] === 'production' && $secure) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

$container = new App\Core\Container($config);
$router = new App\Core\Router($container);
(require __DIR__ . '/../config/routes.php')($router);
$path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
