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
    if ($message === '') {
        respondJson([
            'success' => false,
            'error' => 'Message cannot be empty'
        ], 400);
    }

    $patientId = (int) $_SESSION['user_id'];
    $conversationHistory = is_array($input['history'] ?? null) ? $input['history'] : [];
    $conversationId = is_string($input['conversation_id'] ?? null) ? trim((string) $input['conversation_id']) : (string) ($_SESSION['dify_conversation_id'] ?? DIFY_CONVERSATION_ID);

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli('localhost', 'root', '', 'edoctor');
    if ($conn->connect_error) {
        respondJson([
            'success' => false,
            'error' => 'Database connection failed. Please try again in a moment.'
        ], 503);
    }
    $conn->set_charset('utf8mb4');

    if (LLM_PROVIDER === 'ollama') {
        $apiKey = '';
        $model = OLLAMA_MODEL;
        $apiUrl = OLLAMA_API_URL;
    } elseif (LLM_PROVIDER === 'dify') {
        $apiKey = DIFY_API_KEY;
        $model = 'dify';
        $apiUrl = DIFY_API_URL;
    } else {
        $apiKey = OPENAI_API_KEY;
        $model = OPENAI_MODEL;
        $apiUrl = OPENAI_API_URL;
    }

    $difyUser = 'patient-' . $patientId;
    $aiHandler = new OpenAIHandler($apiKey, $model, $apiUrl, OPENAI_TIMEOUT, LLM_PROVIDER, $conversationId, $difyUser);
    $result = AgentOrchestrator::handle($message, $conversationHistory, $patientId, $conn, $aiHandler);

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
