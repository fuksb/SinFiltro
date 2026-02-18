<?php
// =========================================================
// CONFIGURACIÓN - API keys cargadas desde .env
// =========================================================

// Cargar .env si las variables no están ya en el entorno
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key); $val = trim($val);
        if (!isset($_ENV[$key]) && !getenv($key)) {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

// Claude (Anthropic) — fallback AI
define('CLAUDE_API_KEY', getenv('CLAUDE_API_KEY') ?: '');
define('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages');
define('CLAUDE_MODEL',   'claude-sonnet-4-5-20250929');

// Google Gemini — primario (gratis: 1.500 req/día, 1M tokens/día)
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL',   'gemini-2.0-flash-lite');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');

// Límites del texto de oferta
define('MAX_LISTING_LENGTH', 5000);
define('MIN_LISTING_LENGTH', 50);
