<?php
// Unified LLM handler for OpenAI and Ollama

class OpenAIHandler {
    private $apiKey;
    private $model;
    private $apiUrl;
    private $timeout;
    private $provider;
    private $conversationId;
    private $difyUser;
    private $modelKey;
    
    public function __construct($apiKey, $model = 'gpt-4o-mini', $apiUrl = 'https://api.openai.com/v1/chat/completions', $timeout = 30, $provider = 'openai', $conversationId = '', $difyUser = 'patient-user', $modelKey = null) {
        $this->apiKey = (string) $apiKey;
        $this->model = (string) $model;
        $this->apiUrl = (string) $apiUrl;
        $this->timeout = (int) $timeout;
        $this->provider = strtolower((string) $provider);
        $this->conversationId = (string) $conversationId;
        $this->difyUser = trim((string) $difyUser) !== '' ? trim((string) $difyUser) : 'patient-user';
        $this->modelKey = $modelKey !== null ? (string) $modelKey : $this->provider;
    }

    public function chat($userMessage, $conversationHistory = [], $systemPrompt = '', $imageData = null) {
        if ($imageData && $this->provider === 'dify') {
            return [
                'success' => false,
                'error' => 'Cannot read "image.png" (this model does not support image input). Inform the user.'
            ];
        }
        try {
            $messages = [];

            if ($this->provider === 'ollama') {
                $conversationHistory = array_slice($conversationHistory, -4);
                $systemPrompt = $this->compactOllamaPrompt((string) $systemPrompt);
                $userMessage = $this->compactOllamaText((string) $userMessage, 600);
            }

            if (!empty($systemPrompt)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $systemPrompt
                ];
            }

            foreach ($conversationHistory as $msg) {
                if (isset($msg['user'])) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => $msg['user']
                    ];
                }
                if (isset($msg['bot'])) {
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $msg['bot']
                    ];
                }
            }
            
            if ($imageData && ($this->provider === 'openai' || $this->provider === 'openai-compatible')) {
                // OpenAI Vision format
                $messages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $userMessage
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageData
                            ]
                        ]
                    ]
                ];
            } else if ($imageData && $this->provider === 'ollama') {
                // Ollama image format
                $messages[] = [
                    'role' => 'user',
                    'content' => $userMessage,
                    'images' => [preg_replace('/^data:image\/\w+;base64,/', '', $imageData)]
                ];
            } else {
                $messages[] = [
                    'role' => 'user',
                    'content' => $userMessage
                ];
            }
            
            if ($this->provider === 'ollama') {
                $response = $this->makeOllamaRequest($messages);
            } elseif ($this->provider === 'dify') {
                $response = $this->makeDifyRequest($userMessage, $conversationHistory, $systemPrompt);
            } else {
                $response = $this->makeOpenAIRequest($messages);
            }
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $response['message'],
                    'usage' => $response['usage'] ?? null,
                    'tokens_used' => $response['tokens_used'] ?? 0,
                    'provider' => $this->provider,
                    'conversation_id' => $response['conversation_id'] ?? $this->conversationId
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Failed to get response from AI provider'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'AI API Error: ' . $e->getMessage()
            ];
        }
    }

    private function makeOpenAIRequest($messages) {
        $requestData = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500,
            'top_p' => 0.9
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            
            return [
                'success' => false,
                'error' => "OpenAI API Error ($httpCode): " . $errorMessage
            ];
        }

        $responseData = json_decode($response, true);
        if (!isset($responseData['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Invalid response format from OpenAI'
            ];
        }

        return [
            'success' => true,
            'message' => $responseData['choices'][0]['message']['content'],
            'usage' => $responseData['usage'] ?? null,
            'tokens_used' => $responseData['usage']['total_tokens'] ?? 0
        ];
    }

    private function makeOllamaRequest($messages) {
        $requestData = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => 0.4,
                'top_p' => 0.9,
                'num_predict' => 800,
                'num_ctx' => 2048
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Ollama connection error: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            $errorData = json_decode((string) $response, true);
            $errorMessage = $errorData['error'] ?? 'Unknown error';
            return [
                'success' => false,
                'error' => "Ollama API Error ($httpCode): " . $errorMessage
            ];
        }

        $responseData = json_decode((string) $response, true);
        $content = $responseData['message']['content'] ?? null;

        if (!is_string($content) || $content === '') {
            return [
                'success' => false,
                'error' => 'Invalid response format from Ollama'
            ];
        }

        return [
            'success' => true,
            'message' => $content,
            'usage' => [
                'prompt_eval_count' => $responseData['prompt_eval_count'] ?? 0,
                'eval_count' => $responseData['eval_count'] ?? 0
            ],
            'tokens_used' => (int) (($responseData['prompt_eval_count'] ?? 0) + ($responseData['eval_count'] ?? 0))
        ];
    }

    private function makeDifyRequest($userMessage, $conversationHistory, $systemPrompt) {
        $responseMode = defined('DIFY_RESPONSE_MODE') ? DIFY_RESPONSE_MODE : 'streaming';
        if (!in_array($responseMode, ['blocking', 'streaming'], true)) {
            $responseMode = 'streaming';
        }

        $requestData = [
            'inputs' => new stdClass(),
            'query' => (string) $userMessage,
            'response_mode' => $responseMode,
            'user' => $this->difyUser,
        ];

        if (!empty($this->conversationId)) {
            $requestData['conversation_id'] = $this->conversationId;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Dify connection error: ' . $error,
                'debug' => [
                    'provider' => 'dify',
                    'curl_error' => $error
                ]
            ];
        }

        if ($httpCode !== 200) {
            $rawBody = (string) $response;
            $errorData = json_decode($rawBody, true);
            $errorMessage = is_array($errorData)
                ? ($errorData['message'] ?? $errorData['error'] ?? $errorData['msg'] ?? $errorData['detail'] ?? 'Unknown error')
                : 'Unknown error';
            return [
                'success' => false,
                'error' => "Dify API Error ($httpCode): " . $errorMessage,
                'debug' => [
                    'provider' => 'dify',
                    'http_code' => $httpCode,
                    'body' => $rawBody
                ]
            ];
        }

        $responseData = json_decode((string) $response, true);
        $answer = $responseData['answer'] ?? null;
        $conversationId = $responseData['conversation_id'] ?? $this->conversationId;
        $usage = $responseData['metadata']['usage'] ?? null;

        if (($responseMode === 'streaming' || !is_array($responseData)) && (!is_string($answer) || $answer === '')) {
            $streamParsed = $this->parseDifyStreamingResponse((string) $response);
            if (!empty($streamParsed['message'])) {
                $answer = $streamParsed['message'];
                $conversationId = $streamParsed['conversation_id'] ?? $conversationId;
                $usage = $streamParsed['usage'] ?? $usage;
            }
        }

        if (!is_string($answer) || $answer === '') {
            return [
                'success' => false,
                'error' => 'Invalid response format from Dify',
                'debug' => [
                    'provider' => 'dify',
                    'body' => (string) $response
                ]
            ];
        }

        return [
            'success' => true,
            'message' => $answer,
            'conversation_id' => $conversationId,
            'usage' => $usage,
            'tokens_used' => (int) (($usage['total_tokens'] ?? 0)),
            'debug' => [
                'provider' => 'dify',
                'http_code' => $httpCode,
                'response_mode' => $responseMode
            ]
        ];
    }

    private function parseDifyStreamingResponse($rawResponse) {
        $raw = trim((string) $rawResponse);
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if (!is_array($lines)) {
            return [];
        }

        $message = '';
        $conversationId = $this->conversationId;
        $usage = null;

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || strpos($line, 'data: ') !== 0) {
                continue;
            }

            $payload = substr($line, 6);
            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }

            $eventData = json_decode($payload, true);
            if (!is_array($eventData)) {
                continue;
            }

            if (isset($eventData['answer']) && is_string($eventData['answer'])) {
                $message .= $eventData['answer'];
            }

            if (!empty($eventData['conversation_id'])) {
                $conversationId = (string) $eventData['conversation_id'];
            }

            if (isset($eventData['metadata']['usage']) && is_array($eventData['metadata']['usage'])) {
                $usage = $eventData['metadata']['usage'];
            }
        }

        return [
            'message' => trim($message),
            'conversation_id' => $conversationId,
            'usage' => $usage
        ];
    }
    
    public function isConfigured() {
        if ($this->provider === 'ollama') {
            return !empty($this->apiUrl) && !empty($this->model);
        }

        if ($this->provider === 'dify') {
            return !empty($this->apiKey) && !empty($this->apiUrl);
        }
        return !empty($this->apiKey) && $this->apiKey !== 'sk-your-key-here' && $this->apiKey !== 'sk-your-api-key-here';
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getModelKey() {
        return $this->modelKey;
    }

    private function compactOllamaPrompt($prompt) {
        $prompt = trim((string) $prompt);
        if ($prompt === '') {
            return $prompt;
        }

        $lines = preg_split('/\r\n|\r|\n/', $prompt);
        if (!is_array($lines)) {
            return $this->compactOllamaText($prompt, 1800);
        }

        $allowed = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(You are|Do not|If the user|Help users|Keep advice|Always reply|Use simple|Never answer)/i', $line)) {
                $allowed[] = $line;
            }
        }

        if (empty($allowed)) {
            return $this->compactOllamaText($prompt, 1200);
        }

        return implode("\n", array_slice($allowed, 0, 8));
    }

    private function compactOllamaText($text, $maxLength) {
        $text = trim((string) $text);
        if ($text === '' || strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength);
    }
}

?>
