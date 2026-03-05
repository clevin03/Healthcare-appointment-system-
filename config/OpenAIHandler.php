<?php
// OpenAI API

class OpenAIHandler {
    private $apiKey;
    private $model;
    private $apiUrl;
    private $timeout;
    
    public function __construct($apiKey, $model = 'gpt-3.5-turbo', $apiUrl = 'https://api.openai.com/v1/chat/completions', $timeout = 30) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->apiUrl = $apiUrl;
        $this->timeout = $timeout;
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
            
            $requestData = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500,
                'top_p' => 0.9
            ];
            
            $response = $this->makeRequest($requestData);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $response['message'],
                    'usage' => $response['usage'] ?? null,
                    'tokens_used' => $response['tokens_used'] ?? 0
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Failed to get response from OpenAI'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'OpenAI API Error: ' . $e->getMessage()
            ];
        }
    }
    
    private function makeRequest($data) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            
            return [
                'success' => false,
                'error' => "OpenAI API Error ($httpCode): " . $errorMessage
            ];
        }
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
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
    
    public function isConfigured() {
        return !empty($this->apiKey) && strpos($this->apiKey, 'sk-') === 0 && $this->apiKey !== 'sk-your-api-key-here';
    }
}

?>
