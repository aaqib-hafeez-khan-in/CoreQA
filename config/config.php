<?php

declare(strict_types=1);

$env = getenv('APP_ENV') ?: 'local';
$baseUrl = rtrim(getenv('APP_BASE_URL') ?: '/', '/');
$csrfKey = getenv('CSRF_KEY') ?: '';

if ($env === 'production' && strlen($csrfKey) < 32) {
    throw new RuntimeException('CSRF_KEY must be set to a random value of at least 32 characters in production.');
}

return [
    'env' => $env,
    'base_url' => $baseUrl === '' ? '/' : $baseUrl . '/',
    'session_name' => getenv('SESSION_NAME') ?: 'coreqa_sid',
    'csrf_key' => $csrfKey ?: hash('sha256', __FILE__ . php_uname()),
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
        'user' => getenv('DB_USER') ?: '',
        'pass' => getenv('DB_PASS') ?: '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
