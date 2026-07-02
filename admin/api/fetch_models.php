<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$apiUrl = isset($_GET['api_url']) ? trim($_GET['api_url']) : '';
$apiKey = isset($_GET['api_key']) ? trim($_GET['api_key']) : '';

if (!$apiUrl || !$apiKey) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing api_url or api_key']);
    exit;
}

$modelsUrl = preg_replace('/\/chat\/completions$/i', '', rtrim($apiUrl, '/')) . '/models';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $modelsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(502);
    echo json_encode(['error' => 'cURL Error: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $response ?: json_encode(['error' => "HTTP $httpCode from upstream"]);
    exit;
}

echo $response;
