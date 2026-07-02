<?php

class RiskEngine {
	public static function assess($message) {
		$normalized = strtolower(trim((string) $message));

		$highRiskKeywords = [
			'suicide', 'kill myself', 'end my life', 'want to die', 'self harm',
			'hurt myself', 'no reason to live', 'i am done', 'take my life'
		];

		$moderateRiskKeywords = [
			'depressed', 'hopeless', 'panic', 'anxiety', 'can\'t sleep',
			'overwhelmed', 'stressed out', 'lonely', 'mental breakdown',
			'worthless', 'burnout'
		];

		$mentalHealthKeywords = [
			'mental health', 'therapy', 'counseling', 'psychiatrist', 'psychologist',
			'stress', 'depression', 'anxiety', 'mood', 'trauma'
		];

		foreach ($highRiskKeywords as $keyword) {
			if (strpos($normalized, $keyword) !== false) {
				return [
					'level' => 'high',
					'category' => 'crisis',
					'matched_keyword' => $keyword
				];
			}
		}

		foreach ($moderateRiskKeywords as $keyword) {
			if (strpos($normalized, $keyword) !== false) {
				return [
					'level' => 'moderate',
					'category' => 'distress',
					'matched_keyword' => $keyword
				];
			}
		}

		foreach ($mentalHealthKeywords as $keyword) {
			if (strpos($normalized, $keyword) !== false) {
				return [
					'level' => 'low',
					'category' => 'mental_health',
					'matched_keyword' => $keyword
				];
			}
		}

		return [
			'level' => 'none',
			'category' => 'general',
			'matched_keyword' => null
		];
	}
}
