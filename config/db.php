<?php
// config/db.php
$host = '127.0.0.1';
$db   = 'china_square';
$user = 'root';
$pass = ''; // Database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Paystack API Keys
// Robust .env loader - won't break site
function loadEnvOnce() {
    if (isset($_ENV['PAYSTACK_SECRET_KEY'])) return;
    
    $possiblePaths = [
        __DIR__ . '/../.env',
        __DIR__ . '/../../.env',
        __DIR__ . '/../.env.example',
    ];
    
    foreach ($possiblePaths as $path) {
        if (!file_exists($path)) continue;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (!str_contains($line, '=')) continue;
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k); $v = trim($v, " \t\n\r\0\x0B\"'");
            if ($k === '') continue;
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
        break; // stop after first file found
    }
}
loadEnvOnce();

// This is what fixes the Warning - use getenv as fallback
$secret = $_ENV['PAYSTACK_SECRET_KEY'] ?? getenv('PAYSTACK_SECRET_KEY') ?? '';
$public = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? getenv('PAYSTACK_PUBLIC_KEY') ?? '';

if ($secret !== '') {
    define('PAYSTACK_SECRET_KEY', $secret);
}
if ($public !== '') {
    define('PAYSTACK_PUBLIC_KEY', $public);
}
