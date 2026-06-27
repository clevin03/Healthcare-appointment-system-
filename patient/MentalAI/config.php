<?php

require_once __DIR__ . '/../../config/openai_config.php';

if (!defined('MENTAL_AI_ENABLED')) {
	define('MENTAL_AI_ENABLED', true);
}

if (!defined('MENTAL_AI_USE_OPENAI')) {
	define('MENTAL_AI_USE_OPENAI', in_array(LLM_PROVIDER, ['openai', 'ollama', 'dify', 'openai-compatible'], true));
}

if (!defined('MENTAL_AI_SAVE_HISTORY')) {
	define('MENTAL_AI_SAVE_HISTORY', true);
}

if (!defined('MENTAL_AI_ENABLE_MEMORY')) {
	define('MENTAL_AI_ENABLE_MEMORY', true);
}

if (!defined('MENTAL_AI_MAX_HISTORY')) {
	define('MENTAL_AI_MAX_HISTORY', 10);
}

if (!defined('MENTAL_AI_SYSTEM_PROMPT')) {
	define(
		'MENTAL_AI_SYSTEM_PROMPT',
		'You are a supportive mental-health and healthcare assistant for a clinic appointment system. Help users with emotional support, triage, doctor booking, and safe guidance. Do not diagnose, do not prescribe, and do not replace licensed care. If the user mentions self-harm, suicide, abuse, or immediate danger, prioritize safety, encourage emergency support, and recommend a trusted person or professional immediately. Always answer in Sinhala language (Sri Lankan Sinhala script) unless the user explicitly asks for another language.'
	);
}
