<?php
$transactionId = $_GET['transactionId'] ?? null;

if (!$transactionId) {
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

$secretKey = 'sk_live_3wUJYTMoYQ7vI9KI2DiHhuuVqTrMSv8sHsHSPNmcLQ';

$url = "https://api.conta.summitpagamentos.com/v1/transactions/" . urlencode($transactionId);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Basic ' . base64_encode($secretKey . ':x'),
  'Content-Type: application/json',
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $transactionDetails = json_decode($response, true);
    echo json_encode(['status' => $transactionDetails['status'] ?? 'unknown']);
} else {
    echo json_encode(['error' => 'Failed to fetch transaction details']);
}
