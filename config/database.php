<?php

return function (array $cfg): \PDO {
    $dsn = $cfg['db']['dsn'];
    $isVercel = getenv('VERCEL');

    if (strpos($dsn, 'sqlite:') === 0) {
        if ($isVercel) {
            $dsn = 'sqlite:/tmp/database.sqlite';
        }
    }

    $pdo = new PDO(
        $dsn,
        $cfg['db']['user'],
        $cfg['db']['pass'],
        $cfg['db']['options']
    );

    if (strpos($dsn, 'sqlite:') === 0) {
        $pdo->exec("PRAGMA journal_mode = MEMORY");
        
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
        if (!$tables) {
            $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, password_hash TEXT, role TEXT, reputation INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE topics (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT, slug TEXT, body TEXT, status TEXT DEFAULT 'open', pinned_at DATETIME, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, topic_id INTEGER, user_id INTEGER, body TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE tags (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, slug TEXT UNIQUE)");
            $pdo->exec("CREATE TABLE topic_tag (topic_id INTEGER, tag_id INTEGER, PRIMARY KEY (topic_id, tag_id))");
            $pdo->exec("CREATE TABLE votes (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, post_id INTEGER, value INTEGER, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE(user_id, post_id))");

            $passAdmin = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (name, email, password_hash, role, reputation) VALUES ('Admin', 'admin@example.com', '$passAdmin', 'admin', 100)");
            $adminId = $pdo->lastInsertId();
            
            $passUser = password_hash('user123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (name, email, password_hash, role, reputation) VALUES ('John Doe', 'john@example.com', '$passUser', 'user', 10)");
            $userId = $pdo->lastInsertId();

            $pdo->exec("INSERT INTO tags (name, slug) VALUES ('PHP', 'php'), ('Vercel', 'vercel'), ('Showcase', 'showcase')");
            $tagId = $pdo->lastInsertId();
            
            $pdo->exec("INSERT INTO topics (user_id, title, slug, body) VALUES ($userId, 'How to deploy PHP on Vercel?', 'how-to-deploy-php-on-vercel', 'I want to know the best way to deploy a native PHP app with SQLite on Vercel. Any tips?')");
            $topicId = $pdo->lastInsertId();
            
            $pdo->exec("INSERT INTO topic_tag (topic_id, tag_id) VALUES ($topicId, 1), ($topicId, 2)");

            $pdo->exec("INSERT INTO topics (user_id, title, slug, body) VALUES ($adminId, 'Welcome to CoreQA!', 'welcome-to-coreqa', 'This is a professional Q&A forum platform. Use the sidebar to browse categories or the search bar for specific topics.')");
            $welcomeId = $pdo->lastInsertId();
            $pdo->exec("INSERT INTO topic_tag (topic_id, tag_id) VALUES ($welcomeId, 3)");

            $pdo->exec("INSERT INTO posts (topic_id, user_id, body) VALUES ($topicId, $adminId, 'The best way is to use a community runtime like `vercel-php`. It handles everything for you!')");
            $postId = $pdo->lastInsertId();
            
            $pdo->exec("INSERT INTO votes (user_id, post_id, value) VALUES ($userId, $postId, 1)");
            $pdo->exec("UPDATE users SET reputation = reputation + 1 WHERE id = $adminId");
        }
    }

    return $pdo;
};
