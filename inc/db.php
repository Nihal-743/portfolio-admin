<?php
// database connection (sqlite - single file, no mysql setup needed)

define('ROOT', dirname(__DIR__));
define('DB_FILE', ROOT . '/data/site.db');

function db()
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $firstRun = !file_exists(DB_FILE);

    if (!is_dir(ROOT . '/data')) {
        mkdir(ROOT . '/data', 0755, true);
    }

    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL');

    if ($firstRun) {
        create_tables($pdo);
        require __DIR__ . '/seed.php';
        seed_content($pdo);
    }

    return $pdo;
}

function create_tables($pdo)
{
    $pdo->exec("
        CREATE TABLE settings (
            key   TEXT PRIMARY KEY,
            value TEXT
        );

        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT
        );

        CREATE TABLE projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            url TEXT,
            category TEXT,
            tag TEXT,
            scratch INTEGER DEFAULT 0,
            image TEXT,
            sort INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1
        );

        CREATE TABLE jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            period TEXT,
            title TEXT,
            company TEXT,
            points TEXT,
            is_current INTEGER DEFAULT 0,
            sort INTEGER DEFAULT 0
        );

        CREATE TABLE skill_groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            label TEXT,
            items TEXT,
            sort INTEGER DEFAULT 0
        );

        CREATE TABLE steps (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            text TEXT,
            sort INTEGER DEFAULT 0
        );

        CREATE TABLE education (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            period TEXT,
            title TEXT,
            place TEXT,
            pill TEXT,
            sort INTEGER DEFAULT 0
        );

        CREATE TABLE enquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            contact TEXT,
            company TEXT,
            message TEXT,
            ip TEXT,
            is_read INTEGER DEFAULT 0,
            created_at TEXT
        );

        CREATE TABLE page_views (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visitor TEXT,
            device TEXT,
            referrer TEXT,
            created_at TEXT
        );

        CREATE TABLE cv_views (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visitor TEXT,
            device TEXT,
            referrer TEXT,
            ip TEXT,
            created_at TEXT
        );

        CREATE INDEX idx_views_date ON page_views(created_at);
        CREATE INDEX idx_cv_date ON cv_views(created_at);
    ");
}
