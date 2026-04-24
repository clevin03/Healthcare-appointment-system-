<?php

class AgentOrchestrator {
	public static function handle($message, $conversationHistory, $patientId, $conn, $aiHandler) {
		$message = trim((string) $message);
		$message = function_exists('mb_substr') ? mb_substr($message, 0, 2000) : substr($message, 0, 2000);

		$riskAssessment = RiskEngine::assess($message);
		$actions = [];
		$response = '';
		$source = 'fallback';
		$debug = null;

		$dbContext = DoctorDirectory::buildDatabaseContext($conn, $patientId);

		if (MENTAL_AI_USE_OPENAI && $aiHandler->isConfigured()) {
			$basePrompt = defined('MENTAL_AI_SYSTEM_PROMPT') ? MENTAL_AI_SYSTEM_PROMPT : SYSTEM_PROMPT;
			$enhancedSystemPrompt = SafetyPolicy::buildSafetyAwarePrompt($basePrompt, $dbContext, $riskAssessment);
			$aiResponse = $aiHandler->chat($message, $conversationHistory, $enhancedSystemPrompt);

			if ($aiResponse['success']) {
				$response = (string) $aiResponse['message'];
				$source = $aiResponse['provider'] ?? 'openai';
				$actions = ResponseEngine::getContextualActions($message, $patientId, $conn);

				if ($riskAssessment['level'] === 'high') {
					$actions = SafetyPolicy::getHighRiskActions();
					$response = SafetyPolicy::buildHighRiskResponse();
					$source = 'safety_protocol';
				} elseif ($riskAssessment['level'] === 'moderate') {
					$actions = ResponseEngine::mergeActions($actions, SafetyPolicy::getModerateRiskActions());
				}

				ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
				if (MENTAL_AI_SAVE_HISTORY) {
					ConversationLogger::saveConversation($conn, $patientId, $message, $response);
				}
				$conversationId = $aiResponse['conversation_id'] ?? null;
			} else {
				$apiError = $aiResponse['error'] ?? 'Unknown OpenAI error';
				error_log('[MentalAI ' . strtoupper((string) ($aiHandler->getProvider() ?? 'llm')) . '] ' . $apiError);
				if (defined('OPENAI_DEBUG') && OPENAI_DEBUG) {
					$debug = $apiError;
				}

				if ($aiHandler->getProvider() === 'dify') {
					$response = 'Dify reply ekak ganna ba. Please check if the Dify app is published and the API key is correct.';
				} elseif ($aiHandler->getProvider() === 'ollama') {
					$response = 'Ollama reply ekak ganna ba. Please check if Ollama server eka run wenawada, model eka load vela thiyenawada.';
				} else {
					$response = 'AI provider reply ekak ganna ba. Please check the API configuration.';
				}

				ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
			}
		} else {
			if ($aiHandler->getProvider() === 'dify') {
				$response = 'Dify API not configured. Please set DIFY_API_KEY and confirm the app is published.';
			} elseif ($aiHandler->getProvider() === 'ollama') {
				$response = 'Ollama API not configured. Please set LLM_PROVIDER=ollama and check the local server.';
			} else {
				$response = 'AI provider not configured. Please check your .env file and API settings.';
			}
			ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
		}

		if ($riskAssessment['level'] === 'high') {
			$actions = SafetyPolicy::getHighRiskActions();
			if ($source !== 'safety_protocol') {
				$response = SafetyPolicy::buildHighRiskResponse();
				$source = 'safety_protocol';
			}
		} elseif ($riskAssessment['level'] === 'moderate') {
			$actions = ResponseEngine::mergeActions($actions, SafetyPolicy::getModerateRiskActions());
		}

		return self::buildResult($response, $actions, $source, $riskAssessment, $debug, $conversationId ?? null);
	}

	private static function buildResult($response, $actions, $source, $riskAssessment, $debug, $conversationId = null) {
		return [
			'success' => true,
			'response' => $response,
			'actions' => $actions,
			'source' => $source,
			'conversation_id' => $conversationId,
			'risk' => [
				'level' => $riskAssessment['level'],
				'category' => $riskAssessment['category']
			],
			'debug' => $debug
		];
	}

}
