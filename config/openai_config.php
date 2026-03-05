<?php
// OpenAI Configuration 

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('OPENAI_MODEL', 'gpt-3.5-turbo');
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('OPENAI_TIMEOUT', 30);

define('SYSTEM_PROMPT', 'You are a helpful healthcare assistant for a clinic appointment system. Help patients find doctors, manage appointments, and share general wellness guidance. Do not diagnose; advise consulting a qualified doctor for medical concerns.');

define('USE_OPENAI', true);
define('USE_DATABASE_CONTEXT', true); 
define('SAVE_CONVERSATION_HISTORY', true);

?>
