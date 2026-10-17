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
define('PAYSTACK_SECRET_KEY', 'sk_test_your_paystack_secret_key');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_your_paystack_public_key');