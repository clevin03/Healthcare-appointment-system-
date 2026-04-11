<?php
if (!function_exists('loadProjectEnv')) {
	function loadProjectEnv($envPath) {
		if (!is_readable($envPath)) {
			return;
		}

		$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) {
			return;
		}

		foreach ($lines as $line) {
			$trimmed = trim($line);
			if ($trimmed === '' || strpos($trimmed, '#') === 0) {
				continue;
			}

			if (strpos($trimmed, '=') === false) {
				continue;
			}

			list($name, $value) = explode('=', $trimmed, 2);
			$name = trim($name);
			$value = trim($value);

			if ($name === '') {
				continue;
			}

			$len = strlen($value);
			if ($len >= 2) {
				$first = $value[0];
				$last = $value[$len - 1];
				if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
					$value = substr($value, 1, -1);
				}
			}

			if (getenv($name) === false) {
				putenv($name . '=' . $value);
				$_ENV[$name] = $value;
				$_SERVER[$name] = $value;
			}
		}
	}
}

$projectRoot = dirname(__DIR__);
loadProjectEnv($projectRoot . DIRECTORY_SEPARATOR . '.env');

if (!function_exists('envValue')) {
	function envValue($name, $default = null) {
		$value = getenv($name);
		return $value === false ? $default : $value;
	}
}

if (!function_exists('envBool')) {
	function envBool($name, $default = false) {
		$value = getenv($name);
		if ($value === false) {
			return $default;
		}

		$normalized = strtolower(trim($value));
		return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
	}
}

define('OPENAI_API_KEY', envValue('OPENAI_API_KEY', ''));
define('OPENAI_MODEL', envValue('OPENAI_MODEL', 'gpt-4o-mini'));
define('OPENAI_API_URL', envValue('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'));
define('OPENAI_TIMEOUT', (int) envValue('OPENAI_TIMEOUT', '30'));
define('OPENAI_DEBUG', envBool('OPENAI_DEBUG', false));

define('SYSTEM_PROMPT', 'You are a helpful healthcare assistant for a clinic appointment system. Help patients find doctors, manage appointments, and share general wellness guidance. Do not diagnose; advise consulting a qualified doctor for medical concerns.');

define('USE_OPENAI', true);
define('USE_DATABASE_CONTEXT', true); 
define('SAVE_CONVERSATION_HISTORY', true);

?>
