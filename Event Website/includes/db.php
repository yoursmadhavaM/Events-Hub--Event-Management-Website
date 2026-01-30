<?php
if (!defined('EVENTHUB_CONFIG')) {
    define('EVENTHUB_CONFIG', require __DIR__ . '/../config/database.php');
}

$cfg = EVENTHUB_CONFIG['db'];
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $cfg['host'],
    $cfg['name'],
    $cfg['charset']
);
$opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $opts);
} catch (PDOException $e) {
    die('Database connection failed. Run database/schema.sql and check config/database.php.');
}
