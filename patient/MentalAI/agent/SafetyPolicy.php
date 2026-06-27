<?php

class SafetyPolicy {
	public static function buildSafetyAwarePrompt($basePrompt, $dbContext, $riskAssessment, $styleHint = '') {
		$safetyPrompt = "[ROLE RULES]\n"
			. "- Act as a knowledgeable healthcare assistant. Provide detailed information about medicines, their uses, side effects, and general medical advice when asked.\n"
			. "- If the user asks about a disease or condition, explain it clearly.\n"
			. "- For severe emergencies (suicide, severe bleeding, unconsciousness), advise seeking immediate emergency medical help.\n"
			. "- Keep advice informative and easy to understand.\n"
			. "- Do NOT include any disclaimers like 'I cannot prescribe medicine' or 'I cannot diagnose' in your response unless explicitly asked.\n"
			. "- REFUSAL RULE: You MUST decline to answer any question that is not related to healthcare, medicine, doctors, or wellness. If a user asks about software (like GitHub), programming, general knowledge, or other non-health topics, politely say that you are a healthcare assistant and can only answer health-related questions. Match the language of your refusal to the language of the user's prompt (English or Sinhala).\n";

		$stylePrompt = "[LANGUAGE & STYLE RULE]\n"
			. "- AUTOMATIC LANGUAGE ADAPTATION: Detect the user's language.\n"
			. "- If the user types in English (e.g. 'I have a headache'), reply entirely in English.\n"
			. "- If the user types in Sinhala script or in Singlish/Transliterated Sinhala (e.g. 'mage oluwa kakkumai', 'mata una', 'beheth ganna one'), you MUST reply entirely in proper Sinhala language using Sinhala script.\n"
			. "- Use short, clear, easy-to-understand sentences.\n"
			. "- Keep the tone warm, natural, and conversational, like a real human.\n";

		$riskContext = "[RISK CONTEXT]\n"
			. "Detected risk level: " . $riskAssessment['level'] . "\n"
			. "Detected category: " . $riskAssessment['category'] . "\n";

		return $basePrompt . "\n\n" . $safetyPrompt . ($stylePrompt !== '' ? "\n" . $stylePrompt : '') . "\n" . $riskContext . "\n" . $dbContext;
	}

	public static function buildHighRiskResponse() {
		return "I am really sorry that you are going through this. Your safety matters most right now.\n\n"
			. "Please contact emergency help in your area immediately and reach out to a trusted family member or friend right now.\n"
			. "If you can, do not stay alone.\n\n"
			. "I can also help you connect with a psychiatrist through this system immediately.";
	}

	public static function getHighRiskActions() {
		return [
			['label' => 'Emergency Help', 'action' => 'Show emergency contacts'],
			['label' => 'Call Trusted Person', 'action' => 'I want to call a trusted person'],
			['label' => 'Urgent Psychiatrist Booking', 'action' => 'Book urgent psychiatrist appointment']
		];
	}

	public static function getModerateRiskActions() {
		return [
			['label' => 'Talk to Mental Health Doctor', 'action' => 'Find psychiatrist'],
			['label' => 'Book Counseling Visit', 'action' => 'Book counseling appointment'],
			['label' => 'Self-Care Plan', 'action' => 'Show self-care steps']
		];
	}

	public static function buildModerateRiskFooter() {
		return "If your feelings get worse or you feel unsafe, contact emergency help immediately and reach out to someone you trust.";
	}
}
