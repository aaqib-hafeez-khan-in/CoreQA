<?php

declare(strict_types=1);

return function (array $cfg): \PDO {
    $dsn = $cfg['db']['dsn'];
    if (strpos($dsn, 'sqlite:') === 0 && getenv('VERCEL')) $dsn = 'sqlite:/tmp/database.sqlite';

    $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['options']);

    if (strpos($dsn, 'sqlite:') !== 0) return $pdo;

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT \'user\', reputation INTEGER NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS topics (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, title TEXT NOT NULL, slug TEXT NOT NULL, body TEXT NOT NULL, status TEXT NOT NULL DEFAULT \'open\', pinned_at DATETIME, accepted_post_id INTEGER, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, topic_id INTEGER NOT NULL, user_id INTEGER NOT NULL, body TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(topic_id) REFERENCES topics(id) ON DELETE CASCADE, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS tags (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, slug TEXT NOT NULL UNIQUE)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS topic_tag (topic_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(topic_id, tag_id), FOREIGN KEY(topic_id) REFERENCES topics(id) ON DELETE CASCADE, FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS votes (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, post_id INTEGER NOT NULL, value INTEGER NOT NULL CHECK(value IN (-1, 1)), created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(user_id, post_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (id INTEGER PRIMARY KEY AUTOINCREMENT, ip BLOB NOT NULL, route TEXT NOT NULL, window_start DATETIME NOT NULL, hits INTEGER NOT NULL DEFAULT 1, UNIQUE(ip, route, window_start))');

    if ($cfg['env'] !== 'production' && !$pdo->query("SELECT 1 FROM users LIMIT 1")->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, reputation) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['Admin', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 100]);
        $adminId = (int)$pdo->lastInsertId();
        $stmt->execute(['John Doe', 'john@example.com', password_hash('user123', PASSWORD_DEFAULT), 'user', 10]);
        $userId = (int)$pdo->lastInsertId();
        $pdo->exec("INSERT INTO tags (name, slug) VALUES ('PHP', 'php'), ('Vercel', 'vercel'), ('Showcase', 'showcase')");
        $pdo->prepare('INSERT INTO topics (user_id, title, slug, body) VALUES (?, ?, ?, ?)')->execute([$userId, 'How to deploy PHP on Vercel?', 'how-to-deploy-php-on-vercel', 'I want to know the best way to deploy a native PHP app with SQLite on Vercel. Any tips?']);
        $topicId = (int)$pdo->lastInsertId();
        $pdo->exec("INSERT INTO topic_tag (topic_id, tag_id) VALUES ($topicId, 1), ($topicId, 2)");
        $pdo->prepare('INSERT INTO topics (user_id, title, slug, body) VALUES (?, ?, ?, ?)')->execute([$adminId, 'Welcome to CoreQA!', 'welcome-to-coreqa', 'This is a professional Q&A forum platform.']);
        $welcomeId = (int)$pdo->lastInsertId();
        $pdo->exec("INSERT INTO topic_tag (topic_id, tag_id) VALUES ($welcomeId, 3)");
        $pdo->prepare('INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)')->execute([$topicId, $adminId, 'The best way is to use a community runtime like `vercel-php`.']);
    }
    return $pdo;
};
