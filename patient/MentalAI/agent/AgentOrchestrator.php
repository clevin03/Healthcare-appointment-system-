<?php

class AgentOrchestrator {
	public static function handle($message, $conversationHistory, $patientId, $conn, $handlers, $imageData = null) {
		$message = trim((string) $message);
		$message = function_exists('mb_substr') ? mb_substr($message, 0, 2000) : substr($message, 0, 2000);

		$riskAssessment = RiskEngine::assess($message);
		$actions = [];
		$response = '';
		$source = 'fallback';
		$debug = null;

		$dbContext = DoctorDirectory::buildDatabaseContext($conn, $patientId);

		$handlers = is_array($handlers) ? $handlers : [$handlers];
		$usedProvider = null;
		$lastError = null;

		foreach ($handlers as $aiHandler) {
			if (!MENTAL_AI_USE_OPENAI || !$aiHandler->isConfigured()) {
				continue;
			}

			$basePrompt = defined('MENTAL_AI_SYSTEM_PROMPT') ? MENTAL_AI_SYSTEM_PROMPT : SYSTEM_PROMPT;
			$enhancedSystemPrompt = SafetyPolicy::buildSafetyAwarePrompt($basePrompt, $dbContext, $riskAssessment);
			
			if ($imageData) {
			    $enhancedSystemPrompt .= "\n[IMAGE CONTEXT]\n- CRITICAL RULE: You MUST first check if the uploaded image contains a medicine (e.g. tablet, capsule, pill, syrup, injection, blister pack) or a medical prescription.\n- If the image does NOT contain a medicine or a prescription (for example, if it is a general object, person, animal, scenery, or any non-medical image), you MUST refuse to answer. State politely in the detected language (English or Sinhala) that you can only analyze images of medicines or prescriptions.\n- If it is a medicine or prescription, identify the medicine details, usage, side effects, and precautions.";
			}
			
			$aiResponse = $aiHandler->chat($message, $conversationHistory, $enhancedSystemPrompt, $imageData);

			if ($aiResponse['success']) {
				// Let the AI provide the primary response. Only override for HIGH risk crises.
				$aiResponseMsg = (string) $aiResponse['message'];
				$source = $aiResponse['provider'] ?? 'openai';
				$usedProvider = $aiHandler->getProvider();
				$actions = ResponseEngine::getContextualActions($message, $patientId, $conn);
				
				// Let ResponseEngine try to pattern match for some hardcoded scenarios (like finding doctors)
				$patternActions = [];
				$patternResponse = ResponseEngine::handlePatternMatching($message, $patientId, $conn, $patternActions, $conversationHistory);
				
				if (!empty($patternResponse)) {
				    $response = $patternResponse;
				    $actions = $patternActions;
				    $source = 'pattern_matcher';
				} else {
				    $response = $aiResponseMsg;
				}

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
				break;
			} else {
				$lastError = $aiResponse['error'] ?? 'Unknown error';
				error_log('[MentalAI ' . strtoupper((string) ($aiHandler->getProvider() ?? 'llm')) . '] ' . $lastError);
			}
		}

		if ($usedProvider === null) {
			if (defined('OPENAI_DEBUG') && OPENAI_DEBUG && $lastError) {
				$debug = $lastError;
			}
			$response = 'AI reply ekak ganna ba. Providers tinaima (Ollama, OpenAI, Dify) weda nahata. Karunakara .env file eke settings check karanna.';
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
