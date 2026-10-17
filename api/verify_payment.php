<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to checkout.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$reference = $data['reference'] ?? '';
$totalAmount = $data['totalAmount'] ?? 0;

if (!$reference) {
    echo json_encode(['success' => false, 'message' => 'Missing reference']);
    exit;
}

// Paystack server-to-server transaction verification
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache"
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && isset($result['data']['status']) && $result['data']['status'] === 'success') {
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, reference, total_amount, status) VALUES (?, ?, ?, "Paid")');
    $stmt->execute([$_SESSION['user_id'], $reference, $totalAmount]);

    echo json_encode(['success' => true, 'message' => 'Payment verified successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
}