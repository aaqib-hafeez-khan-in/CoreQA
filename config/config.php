<?php

return [
    'env' => 'local',
    'base_url' => '/',
    'session_name' => 'coreqa_sid',
    'csrf_key' => 'change-me-please',
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
        'user' => getenv('DB_USER') ?: '',
        'pass' => getenv('DB_PASS') ?: '',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
];
