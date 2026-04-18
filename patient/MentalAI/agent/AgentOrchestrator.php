<?php

class AgentOrchestrator {
	public static function handle($message, $conversationHistory, $patientId, $conn, $aiHandler) {
		$message = trim((string) $message);
		$message = function_exists('mb_substr') ? mb_substr($message, 0, 2000) : substr($message, 0, 2000);
		$styleHint = 'singlish';

		$riskAssessment = RiskEngine::assess($message);
		$actions = [];
		$response = '';
		$source = 'fallback';
		$debug = null;

		if ($riskAssessment['level'] === 'high') {
			$response = SafetyPolicy::buildHighRiskResponse();
			$actions = SafetyPolicy::getHighRiskActions();
			$source = 'safety_protocol';
			ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, true);

			if (MENTAL_AI_SAVE_HISTORY) {
				ConversationLogger::saveConversation($conn, $patientId, $message, $response);
			}

			return self::buildResult($response, $actions, $source, $riskAssessment, $debug);
		}

		$dbContext = DoctorDirectory::buildDatabaseContext($conn, $patientId);

		if (MENTAL_AI_USE_OPENAI && $aiHandler->isConfigured()) {
			$basePrompt = defined('MENTAL_AI_SYSTEM_PROMPT') ? MENTAL_AI_SYSTEM_PROMPT : SYSTEM_PROMPT;
			$enhancedSystemPrompt = SafetyPolicy::buildSafetyAwarePrompt($basePrompt, $dbContext, $riskAssessment, $styleHint);
			$aiResponse = $aiHandler->chat($message, $conversationHistory, $enhancedSystemPrompt);

			if ($aiResponse['success']) {
				$response = $aiResponse['message'];
				$source = $aiResponse['provider'] ?? 'openai';
				$actions = ResponseEngine::getContextualActions($message, $patientId, $conn);

				if ($riskAssessment['level'] === 'moderate') {
					$actions = ResponseEngine::mergeActions($actions, SafetyPolicy::getModerateRiskActions());
				}

				ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
				if (MENTAL_AI_SAVE_HISTORY) {
					ConversationLogger::saveConversation($conn, $patientId, $message, $response);
				}
			} else {
				$apiError = $aiResponse['error'] ?? 'Unknown OpenAI error';
				error_log('[MentalAI OpenAI] ' . $apiError);
				if (defined('OPENAI_DEBUG') && OPENAI_DEBUG) {
					$debug = $apiError;
				}

				$response = ResponseEngine::handlePatternMatching($message, $patientId, $conn, $actions, $conversationHistory, $styleHint);
				if ($riskAssessment['level'] === 'moderate') {
					$actions = ResponseEngine::mergeActions($actions, SafetyPolicy::getModerateRiskActions());
					$response .= "\n\n" . SafetyPolicy::buildModerateRiskFooter();
				}

				ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
			}
		} else {
			$response = ResponseEngine::handlePatternMatching($message, $patientId, $conn, $actions, $conversationHistory, $styleHint);

			if ($riskAssessment['level'] === 'moderate') {
				$actions = ResponseEngine::mergeActions($actions, SafetyPolicy::getModerateRiskActions());
				$response .= "\n\n" . SafetyPolicy::buildModerateRiskFooter();
			}

			ConversationLogger::logMentalHealthEvent($conn, $patientId, $message, $riskAssessment, false);
		}

		return self::buildResult($response, $actions, $source, $riskAssessment, $debug);
	}

	private static function buildResult($response, $actions, $source, $riskAssessment, $debug) {
		return [
			'success' => true,
			'response' => $response,
			'actions' => $actions,
			'source' => $source,
			'risk' => [
				'level' => $riskAssessment['level'],
				'category' => $riskAssessment['category']
			],
			'debug' => $debug
		];
	}

}
