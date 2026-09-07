<?php
// Temporary Railway DB fix script - DELETE AFTER USE
$secret = $_GET['key'] ?? '';
if ($secret !== 'fix-aim-2026') {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../config/openai_config.php';

$results = [];

// Create table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `ai_provider_config` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `provider_key` varchar(50) NOT NULL,
        `label` varchar(100) NOT NULL,
        `api_url` varchar(500) DEFAULT '',
        `api_key` varchar(500) DEFAULT '',
        `model` varchar(100) DEFAULT '',
        `is_active` tinyint(1) DEFAULT 0,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `provider_key` (`provider_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

// Set openai-compatible with AIM model as active
$providers = [
    'ollama'             => ['label' => 'Ollama (Local)',                  'api_url' => 'http://127.0.0.1:11434/api/chat',                   'api_key' => '',                                          'model' => 'qwen2.5:1.5b', 'active' => 0],
    'gpt-4o-mini'        => ['label' => 'OpenAI (gpt-4o-mini)',            'api_url' => 'https://api.openai.com/v1/chat/completions',         'api_key' => OPENAI_API_KEY,                              'model' => 'gpt-4o-mini',  'active' => 0],
    'openai-compatible'  => ['label' => 'OpenAI Compatible (AImahagedara)','api_url' => 'https://aimahagedara.online/v1',                    'api_key' => 'aim-4b47084c670b3354-4l2shf-e5f08c2e',      'model' => 'AIM',          'active' => 1],
    'dify'               => ['label' => 'Dify',                            'api_url' => 'https://api.dify.ai/v1/chat-messages',              'api_key' => DIFY_API_KEY,                                'model' => '',             'active' => 0],
];

foreach ($providers as $key => $cfg) {
    $stmt = $conn->prepare("INSERT INTO ai_provider_config (provider_key, label, api_url, api_key, model, is_active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE label=VALUES(label), api_url=VALUES(api_url), api_key=VALUES(api_key), model=VALUES(model), is_active=VALUES(is_active)");
    $stmt->bind_param('sssssi', $key, $cfg['label'], $cfg['api_url'], $cfg['api_key'], $cfg['model'], $cfg['active']);
    $stmt->execute();
    $results[] = $key . ': ' . ($stmt->affected_rows >= 0 ? 'OK' : 'FAIL - ' . $stmt->error);
    $stmt->close();
}

// Show current state
$r = $conn->query("SELECT provider_key, model, is_active FROM ai_provider_config");
$current = [];
while ($row = $r->fetch_assoc()) {
    $current[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'done',
    'updates' => $results,
    'current' => $current
], JSON_PRETTY_PRINT);
