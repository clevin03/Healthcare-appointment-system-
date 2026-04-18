<?php

class SafetyPolicy {
	public static function buildSafetyAwarePrompt($basePrompt, $dbContext, $riskAssessment, $styleHint = '') {
		$safetyPrompt = "[SAFETY RULES]\n"
			. "- You are not a replacement for a licensed mental health professional.\n"
			. "- Do not provide diagnosis, medication dosage, or emergency guarantees.\n"
			. "- If user expresses self-harm, suicide, abuse, or immediate danger: advise emergency help and trusted person contact.\n"
			. "- Keep advice supportive, brief, and action-oriented.\n";

		$stylePrompt = "[STYLE RULE]\n"
			. "- Always reply in Sinhala (Sri Lankan Sinhala script).\n"
			. "- Use short, clear, easy-to-understand Sinhala sentences.\n"
			. "- Never answer in another language unless the user explicitly asks for it.\n"
			. "- Never use Somali or any unrelated language.\n"
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
