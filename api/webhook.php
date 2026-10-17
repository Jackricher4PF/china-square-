<?php
require_once '../config/db.php';

$input = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$signature || $signature !== hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($input, true);

if ($event['event'] === 'charge.success') {
    $reference = $event['data']['reference'];
    $stmt = $pdo->prepare('UPDATE orders SET status = "Paid" WHERE reference = ?');
    $stmt->execute([$reference]);
}

http_response_code(200);