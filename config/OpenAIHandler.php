<?php
// Unified LLM handler for OpenAI and Ollama

class OpenAIHandler {
    private $apiKey;
    private $model;
    private $apiUrl;
    private $timeout;
    private $provider;
    
    public function __construct($apiKey, $model = 'gpt-4o-mini', $apiUrl = 'https://api.openai.com/v1/chat/completions', $timeout = 30, $provider = 'openai') {
        $this->apiKey = (string) $apiKey;
        $this->model = (string) $model;
        $this->apiUrl = (string) $apiUrl;
        $this->timeout = (int) $timeout;
        $this->provider = strtolower((string) $provider);
    }

    public function chat($userMessage, $conversationHistory = [], $systemPrompt = '') {
        try {
            $messages = [];

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
            
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage
            ];
            
            $response = $this->provider === 'ollama'
                ? $this->makeOllamaRequest($messages)
                : $this->makeOpenAIRequest($messages);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $response['message'],
                    'usage' => $response['usage'] ?? null,
                    'tokens_used' => $response['tokens_used'] ?? 0,
                    'provider' => $this->provider
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
            'max_tokens' => 500,
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
            'stream' => false
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
    
    public function isConfigured() {
        if ($this->provider === 'ollama') {
            return !empty($this->apiUrl) && !empty($this->model);
        }

        return !empty($this->apiKey) && strpos($this->apiKey, 'sk-') === 0 && $this->apiKey !== 'sk-your-api-key-here';
    }

    public function getProvider() {
        return $this->provider;
    }
}

?>
