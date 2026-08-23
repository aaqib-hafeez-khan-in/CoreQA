<?php


$dbPath = __DIR__ . '/../storage/database.sqlite';
if (file_exists($dbPath)) { unlink($dbPath); }

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating tables...\n";
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        reputation INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        slug TEXT NOT NULL,
        body TEXT NOT NULL,
        status TEXT DEFAULT 'open',
        pinned_at DATETIME,
        accepted_post_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE
    )");
    $db->exec("CREATE TABLE topic_tag (
        topic_id INTEGER NOT NULL,
        tag_id INTEGER NOT NULL,
        PRIMARY KEY (topic_id, tag_id)
    )");
    $db->exec("CREATE TABLE votes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        post_id INTEGER NOT NULL,
        value INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, post_id)
    )");

    echo "Inserting mock data...\n";
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (name, email, password_hash, role, reputation) VALUES ('Admin', 'admin@example.com', '$pass', 'admin', 100)");
    $adminId = $db->lastInsertId();
    $passUser = password_hash('user123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (name, email, password_hash, role, reputation) VALUES ('John Doe', 'john@example.com', '$passUser', 'user', 10)");
    $userId = $db->lastInsertId();
    $db->exec("INSERT INTO tags (name, slug) VALUES ('PHP', 'php')");
    $db->exec("INSERT INTO tags (name, slug) VALUES ('Vercel', 'vercel')");
    $db->exec("INSERT INTO tags (name, slug) VALUES ('Showcase', 'showcase')");
    $db->exec("INSERT INTO topics (user_id, title, slug, body, status, created_at) VALUES 
        ($adminId, 'Welcome to CoreQA!', 'welcome-to-coreqa', 'This is a showcase of the CoreQA platform running on Vercel with SQLite.', 'open', datetime('now', '-1 day'))");
    $t1 = $db->lastInsertId();

    $db->exec("INSERT INTO topics (user_id, title, slug, body, status, created_at) VALUES 
        ($userId, 'How to deploy PHP on Vercel?', 'how-to-deploy-php-on-vercel', 'I am trying to deploy this project but I need help with the configuration.', 'open', datetime('now'))");
    $t2 = $db->lastInsertId();
    $db->exec("INSERT INTO topic_tag (topic_id, tag_id) VALUES ($t1, 1), ($t1, 3), ($t2, 1), ($t2, 2)");
    $db->exec("INSERT INTO posts (topic_id, user_id, body, created_at) VALUES ($t1, $adminId, 'We hope you like the design!', datetime('now', '-23 hours'))");
    $p1 = $db->lastInsertId();

    $db->exec("INSERT INTO posts (topic_id, user_id, body, created_at) VALUES ($t2, $adminId, 'Check the vercel.json file in the root!', datetime('now', '-1 hour'))");
    $p2 = $db->lastInsertId();
    $db->exec("INSERT INTO votes (user_id, post_id, value) VALUES ($userId, $p1, 1)");
    $db->exec("INSERT INTO votes (user_id, post_id, value) VALUES ($adminId, $p2, 1)");

    echo "Done! SQLite database generated at $dbPath\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

