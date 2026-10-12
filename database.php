<?php
declare(strict_types=1);
$config = require __DIR__ . '/config.php';
$db = $config['db'];
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Global Dream database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check config/config.php and import database.sql.');
}
