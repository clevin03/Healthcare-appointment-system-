<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: /Healthcare-appointment-system-/auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../config/openai_config.php';

$conn->query("
    CREATE TABLE IF NOT EXISTS `ai_provider_config` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `provider_key` varchar(50) NOT NULL COMMENT 'ollama, gpt-4o-mini, openai-compatible, dify',
        `label` varchar(100) NOT NULL,
        `api_url` varchar(500) DEFAULT '',
        `api_key` varchar(500) DEFAULT '',
        `model` varchar(100) DEFAULT '',
        `is_active` tinyint(1) DEFAULT 0,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `provider_key` (`provider_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

$defaults = [
    'ollama' => [
        'label' => 'Ollama (Local)',
        'api_url' => OLLAMA_API_URL,
        'api_key' => '',
        'model' => OLLAMA_MODEL
    ],
    'gpt-4o-mini' => [
        'label' => 'OpenAI (gpt-4o-mini)',
        'api_url' => OPENAI_API_URL,
        'api_key' => OPENAI_API_KEY,
        'model' => OPENAI_MODEL
    ],
    'openai-compatible' => [
        'label' => 'OpenAI Compatible (Custom)',
        'api_url' => OPENAI_COMPATIBLE_BASE_URL,
        'api_key' => OPENAI_COMPATIBLE_API_KEY,
        'model' => OPENAI_COMPATIBLE_MODEL
    ],
    'dify' => [
        'label' => 'Dify',
        'api_url' => DIFY_API_URL,
        'api_key' => DIFY_API_KEY,
        'model' => ''
    ]
];

$activeProvider = LLM_PROVIDER;

foreach ($defaults as $key => $cfg) {
    $stmt = $conn->prepare("INSERT IGNORE INTO ai_provider_config (provider_key, label, api_url, api_key, model, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $active = ($key === $activeProvider) ? 1 : 0;
    $stmt->bind_param('sssssi', $key, $cfg['label'], $cfg['api_url'], $cfg['api_key'], $cfg['model'], $active);
    $stmt->execute();
    $stmt->close();
}

$total = $conn->query("SELECT COUNT(*) AS c FROM ai_provider_config")->fetch_assoc()['c'];
$msg = urlencode("AI Provider Configuration installed successfully. $total providers configured.");
header("Location: settings.php?success=$msg");
exit;
