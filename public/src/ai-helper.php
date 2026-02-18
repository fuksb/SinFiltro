<?php
// =========================================================
// AI Helper — Gemini (primary, free) → Claude (fallback)
// =========================================================
// callAI($prompt, $b64A, $mimeA, $b64B, $mimeB, $maxTokens)
//   → clean JSON string on success | false on total failure
//
// Also exposes:
//   _pruneCache(string $dir, int $ttl)  — lazy expired-file cleanup
// =========================================================

require_once __DIR__ . '/config.php';

// ── Tunables ──────────────────────────────────────────
define('SF_RATE_LIMIT',  30);    // max AI calls per IP per window
define('SF_RATE_WINDOW', 3600);  // window in seconds (1 hour)

// =========================================================
// SSRF Protection - Validate URLs to prevent internal network access
// =========================================================

/**
 * Validate URL to prevent SSRF attacks.
 * Blocks access to private networks, localhost, and cloud metadata endpoints.
 * Returns true if URL is safe to fetch, false otherwise.
 */
function _isUrlSafe(string $url): bool {
    $parsed = @parse_url($url);
    
    // Must have scheme and host
    if (empty($parsed['scheme']) || empty($parsed['host'])) {
        return false;
    }
    
    // Only allow http and https
    if (!in_array($parsed['scheme'], ['http', 'https'], true)) {
        return false;
    }
    
    $host = strtolower($parsed['host']);
    
    // Block localhost variants
    if (in_array($host, ['localhost', 'localhost.localdomain', '0.0.0.0', '::1', '127.0.0.1'], true)) {
        return false;
    }
    
    // Block private IP ranges
    // 10.0.0.0/8
    if (preg_match('/^10\.\d+\.\d+\.\d+$/', $host)) {
        return false;
    }
    // 172.16.0.0/12
    if (preg_match('/^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/', $host)) {
        return false;
    }
    // 192.168.0.0/16
    if (preg_match('/^192\.168\.\d+\.\d+$/', $host)) {
        return false;
    }
    // 169.254.169.254 (cloud metadata)
    if ($host === '169.254.169.254') {
        return false;
    }
    // Block .internal, .local, .corp domains
    if (preg_match('/\.(internal|corp|local|lan)$/i', $host)) {
        return false;
    }
    // Block metadata.google.internal (GCP)
    if ($host === 'metadata.google.internal') {
        return false;
    }
    
    return true;
}

// =========================================================
// PUBLIC API
// =========================================================

/**
 * Try Gemini first; fall back to Claude automatically.
 * Enforces per-IP rate limit before calling any AI.
 * Increments usage counter on success.
 * Returns clean JSON string or false if both models fail.
 */
function callAI(
    string  $prompt,
    ?string $b64A      = null,
    ?string $mimeA     = null,
    ?string $b64B      = null,
    ?string $mimeB     = null,
    int     $maxTokens = 2500
): string|false {
    _enforceRateLimit();

    $text = _callGemini($prompt, $b64A, $mimeA, $b64B, $mimeB, $maxTokens);
    if ($text !== false) {
        _incrementCounter();
        return $text;
    }

    // Gemini failed → try Claude fallback
    $text = _callClaude($prompt, $b64A, $mimeA, $b64B, $mimeB, $maxTokens);
    if ($text !== false) {
        _incrementCounter();
    }
    return $text;
}

/**
 * Lazy cache pruner. Call after writing a cache file.
 * Runs with 2% probability to avoid overhead on every request.
 */
function _pruneCache(string $dir, int $ttl): void {
    if (!is_dir($dir) || rand(1, 50) !== 1) return;
    $now = time();
    foreach (glob($dir . '/*.json') ?: [] as $file) {
        if ($now - filemtime($file) > $ttl) @unlink($file);
    }
}

// =========================================================
// PRIVATE: Rate Limiting
// =========================================================

function _enforceRateLimit(): void {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $dir = __DIR__ . '/../data/ratelimit/';
    if (!is_dir($dir)) @mkdir($dir, 0750, true);

    // Cleanup stale ratelimit files ~1% of the time (filemtime as proxy, no file reads)
    if (rand(1, 100) === 1) {
        $cutoff = time() - SF_RATE_WINDOW;
        foreach (glob($dir . '*.json') ?: [] as $f) {
            if (@filemtime($f) < $cutoff) @unlink($f);
        }
    }

    $file = $dir . md5($ip) . '.json';
    $fp   = @fopen($file, 'c+');
    if (!$fp) return; // can't lock → allow (fail open)

    flock($fp, LOCK_EX);
    $raw   = stream_get_contents($fp);
    $data  = $raw ? (json_decode($raw, true) ?? []) : [];
    $now   = time();
    $reset = $data['reset'] ?? ($now + SF_RATE_WINDOW);
    $count = $data['count'] ?? 0;

    if ($now > $reset) {
        $count = 0;
        $reset = $now + SF_RATE_WINDOW;
    }

    if ($count >= SF_RATE_LIMIT) {
        flock($fp, LOCK_UN);
        fclose($fp);
        http_response_code(429);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'error'       => 'Demasiadas consultas. Espera un momento antes de volver a intentarlo.',
            'retry_after' => max(0, $reset - $now),
        ]);
        exit;
    }

    $count++;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode(['count' => $count, 'reset' => $reset]));
    flock($fp, LOCK_UN);
    fclose($fp);
}

// =========================================================
// PRIVATE: Counter (atomic, flock-safe)
// =========================================================

function _incrementCounter(): void {
    $file = __DIR__ . '/../data/counter.txt';
    $fp   = @fopen($file, 'c+');
    if (!$fp) return;
    flock($fp, LOCK_EX);
    $count = (int) stream_get_contents($fp) + 1;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) $count);
    flock($fp, LOCK_UN);
    fclose($fp);
}

// =========================================================
// PRIVATE: Error Logging
// =========================================================

function _logAIError(string $model, int $httpCode, string $message): void {
    $tool = basename($_SERVER['SCRIPT_NAME'] ?? 'unknown', '-api.php');
    $ip   = substr($_SERVER['REMOTE_ADDR'] ?? '-', 0, 45);
    $ts   = date('Y-m-d H:i:s');
    // Sanitize message to prevent log injection
    $sanitizedMessage = preg_replace('/[\r\n\t]/', ' ', $message);
    $sanitizedMessage = substr($sanitizedMessage, 0, 500); // Limit length
    $line = "$ts\t$model\t$tool\tHTTP-$httpCode\t$ip\t$sanitizedMessage\n";
    @file_put_contents(__DIR__ . '/../data/errors.log', $line, FILE_APPEND | LOCK_EX);
}

// =========================================================
// PRIVATE: Gemini
// =========================================================

function _callGemini(
    string  $prompt,
    ?string $b64A,
    ?string $mimeA,
    ?string $b64B,
    ?string $mimeB,
    int     $maxTokens
): string|false {
    $parts = [];
    if ($b64A && $mimeA) $parts[] = ['inline_data' => ['mime_type' => $mimeA, 'data' => $b64A]];
    if ($b64B && $mimeB) $parts[] = ['inline_data' => ['mime_type' => $mimeB, 'data' => $b64B]];
    $parts[] = ['text' => $prompt];

    $payload = json_encode([
        'contents'         => [['parts' => $parts]],
        'generationConfig' => [
            'temperature'        => 0.3,
            'maxOutputTokens'    => $maxTokens,
            'response_mime_type' => 'application/json',
        ],
    ]);

    $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 55,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        _logAIError('gemini', 0, "cURL: $curlErr");
        return false;
    }
    if ($httpCode !== 200 || !$response) {
        $msg = json_decode($response, true)['error']['message'] ?? "HTTP $httpCode";
        _logAIError('gemini', $httpCode, $msg);
        return false;
    }

    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    return $text !== '' ? $text : false;
}

// =========================================================
// PRIVATE: Claude (fallback)
// =========================================================

function _callClaude(
    string  $prompt,
    ?string $b64A,
    ?string $mimeA,
    ?string $b64B,
    ?string $mimeB,
    int     $maxTokens
): string|false {
    $blocks = [];
    if ($b64A && $mimeA) {
        $type     = str_starts_with($mimeA, 'image/') ? 'image' : 'document';
        $blocks[] = ['type' => $type, 'source' => ['type' => 'base64', 'media_type' => $mimeA, 'data' => $b64A]];
    }
    if ($b64B && $mimeB) {
        $type     = str_starts_with($mimeB, 'image/') ? 'image' : 'document';
        $blocks[] = ['type' => $type, 'source' => ['type' => 'base64', 'media_type' => $mimeB, 'data' => $b64B]];
    }
    $blocks[]       = ['type' => 'text', 'text' => $prompt];
    $messageContent = count($blocks) > 1 ? $blocks : $prompt;

    $hasPdf  = ($mimeA === 'application/pdf' || $mimeB === 'application/pdf');
    $headers = array_values(array_filter([
        'Content-Type: application/json',
        'x-api-key: ' . CLAUDE_API_KEY,
        'anthropic-version: 2023-06-01',
        $hasPdf ? 'anthropic-beta: pdfs-2024-09-25' : null,
    ]));

    $payload = json_encode([
        'model'       => CLAUDE_MODEL,
        'max_tokens'  => $maxTokens,
        'temperature' => 0.3,
        'messages'    => [['role' => 'user', 'content' => $messageContent]],
    ]);

    $ch = curl_init(CLAUDE_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        _logAIError('claude', 0, "cURL: $curlErr");
        return false;
    }
    if ($httpCode !== 200 || !$response) {
        $msg = json_decode($response, true)['error']['message'] ?? "HTTP $httpCode";
        _logAIError('claude', $httpCode, $msg);
        return false;
    }

    $data = json_decode($response, true);
    $text = $data['content'][0]['text'] ?? '';
    if ($text === '') return false;

    // Strip markdown fences Claude sometimes adds
    $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
    $text = preg_replace('/\s*```$/', '', $text);
    return trim($text);
}
