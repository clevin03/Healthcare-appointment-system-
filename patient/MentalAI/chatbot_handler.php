<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

function respondJson($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$conn = null;

try {
    session_start();

    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/../../config/OpenAIHandler.php';
    require_once __DIR__ . '/../../config/db_connection.php';

    if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'patient') {
        respondJson([
            'success' => false,
            'error' => 'Unauthorized access. Please log in.'
        ], 401);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!is_array($input) || !isset($input['message'])) {
        respondJson([
            'success' => false,
            'error' => 'No message provided'
        ], 400);
    }

    $message = trim((string) $input['message']);
    if ($message === '' && !isset($input['image'])) {
        respondJson([
            'success' => false,
            'error' => 'Message cannot be empty'
        ], 400);
    }

    $patientId = (int) $_SESSION['user_id'];
    $conversationHistory = is_array($input['history'] ?? null) ? $input['history'] : [];
    $conversationId = is_string($input['conversation_id'] ?? null) ? trim((string) $input['conversation_id']) : (string) ($_SESSION['dify_conversation_id'] ?? DIFY_CONVERSATION_ID);
    $imageData = is_string($input['image'] ?? null) ? trim((string) $input['image']) : null;

    $difyUser = 'patient-' . $patientId;

    // Auto-create ai_provider_config table if it doesn't exist
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // Load AI provider config from DB; fall back to .env constants
    $dbProviders = [];
    $result = $conn->query("SELECT * FROM ai_provider_config WHERE provider_key IN ('ollama', 'gpt-4o-mini', 'openai-compatible', 'dify')");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dbProviders[$row['provider_key']] = $row;
        }
    }

    $hasDbConfig = !empty($dbProviders);

    $allHandlers = [];
    $priorityOrder = ['ollama', 'gpt-4o-mini', 'openai-compatible', 'dify'];

    // Determine active provider from DB
    $activeProvider = '';
    if ($hasDbConfig) {
        foreach ($priorityOrder as $key) {
            if (!empty($dbProviders[$key]['is_active'])) {
                $activeProvider = $key;
                break;
            }
        }
    }

    if ($hasDbConfig) {
        foreach ($priorityOrder as $key) {
            $cfg = $dbProviders[$key] ?? null;
            if (!$cfg) continue;

            $apiUrl = trim((string) $cfg['api_url']);
            $apiKey = trim((string) $cfg['api_key']);
            $model = trim((string) $cfg['model']);

            if (empty($apiUrl)) continue;
            if (in_array($key, ['gpt-4o-mini', 'openai-compatible', 'dify']) && (empty($apiKey) || $apiKey === 'sk-your-key-here' || $apiKey === 'sk-your-api-key-here')) continue;

            if ($key === 'ollama') {
                $allHandlers['ollama'] = new OpenAIHandler('', $model ?: OLLAMA_MODEL, $apiUrl, OPENAI_TIMEOUT, 'ollama', '', '', 'ollama');
            } elseif ($key === 'gpt-4o-mini') {
                $baseUrl = $apiUrl;
                if (strpos($baseUrl, 'chat/completions') === false) {
                    $baseUrl = rtrim($baseUrl, '/') . '/chat/completions';
                }
                $allHandlers['gpt-4o-mini'] = new OpenAIHandler($apiKey, $model ?: OPENAI_MODEL, $baseUrl, OPENAI_TIMEOUT, 'openai', '', '', 'gpt-4o-mini');
            } elseif ($key === 'openai-compatible') {
                $baseUrl = $apiUrl;
                if (strpos($baseUrl, 'chat/completions') === false) {
                    $baseUrl = rtrim($baseUrl, '/') . '/chat/completions';
                }
                $allHandlers['openai-compatible'] = new OpenAIHandler($apiKey, $model ?: OPENAI_COMPATIBLE_MODEL, $baseUrl, 60, 'openai', '', '', 'openai-compatible');
            } elseif ($key === 'dify') {
                $allHandlers['dify'] = new OpenAIHandler($apiKey, 'dify', $apiUrl, OPENAI_TIMEOUT, 'dify', $conversationId, $difyUser, 'dify');
            }
        }
    }

    // Fallback to .env constants if no DB config
    if (empty($allHandlers)) {
        $allHandlers['ollama'] = new OpenAIHandler('', OLLAMA_MODEL, OLLAMA_API_URL, OPENAI_TIMEOUT, 'ollama', '', '', 'ollama');
        $allHandlers['gpt-4o-mini'] = new OpenAIHandler(OPENAI_API_KEY, OPENAI_MODEL, OPENAI_API_URL, OPENAI_TIMEOUT, 'openai', '', '', 'gpt-4o-mini');
        if (!empty(OPENAI_COMPATIBLE_API_KEY) && OPENAI_COMPATIBLE_API_KEY !== 'sk-your-key-here') {
            $baseUrl = OPENAI_COMPATIBLE_BASE_URL;
            if (strpos($baseUrl, 'chat/completions') === false) {
                $baseUrl = rtrim($baseUrl, '/') . '/chat/completions';
            }
            $allHandlers['openai-compatible'] = new OpenAIHandler(OPENAI_COMPATIBLE_API_KEY, OPENAI_COMPATIBLE_MODEL, $baseUrl, 60, 'openai', '', '', 'openai-compatible');
        }
        $allHandlers['dify'] = new OpenAIHandler(DIFY_API_KEY, 'dify', DIFY_API_URL, OPENAI_TIMEOUT, 'dify', $conversationId, $difyUser, 'dify');
    }

    // Build priority: active provider first, then others
    if ($activeProvider && isset($allHandlers[$activeProvider])) {
        $priorityOrder = array_unique(
            array_merge([$activeProvider], $priorityOrder)
        );
    }

    $handlers = [];
    foreach ($priorityOrder as $key) {
        if (isset($allHandlers[$key])) {
            $handlers[] = $allHandlers[$key];
        }
    }

    $result = AgentOrchestrator::handle($message, $conversationHistory, $patientId, $conn, $handlers, $imageData);

    if (!empty($result['conversation_id'])) {
        $_SESSION['dify_conversation_id'] = (string) $result['conversation_id'];
    }

    respondJson($result, 200);
} catch (Throwable $e) {
    error_log('[MentalAI API] ' . $e->getMessage());
    respondJson([
        'success' => false,
        'error' => 'Server error while processing chatbot request.',
        'debug' => defined('OPENAI_DEBUG') && OPENAI_DEBUG ? $e->getMessage() : null
    ], 500);
} finally {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}

?>
