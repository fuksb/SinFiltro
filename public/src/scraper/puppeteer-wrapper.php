<?php
/**
 * PHP Wrapper for Puppeteer Scraper
 * Calls the Node.js scraper from PHP
 */

function scrapeWithPuppeteer(string $url, int $timeout = 60): string|false {
    // Validate URL
    if (!_isUrlSafe($url)) {
        return false;
    }

    $scraperPath = __DIR__ . '/scraper.js';
    
    // Check if scraper exists
    if (!file_exists($scraperPath)) {
        error_log("Puppeteer scraper not found at: $scraperPath");
        return false;
    }

    // Sanitize URL for command line
    $escapedUrl = escapeshellarg($url);
    
    // Execute scraper with timeout
    $cmd = sprintf(
        'cd %s && node scraper.js %s 2>&1',
        escapeshellarg(__DIR__),
        $escapedUrl
    );

    $descriptors = [
        0 => ['pipe', 'r'],  // stdin
        1 => ['pipe', 'w'],  // stdout
        2 => ['pipe', 'w'],  // stderr
    ];

    $process = proc_open($cmd, $descriptors, $pipes, __DIR__, null, [
        'bypass_shell' => true
    ]);

    if (!is_resource($process)) {
        error_log("Failed to start Puppeteer process");
        return false;
    }

    // Set timeout
    $startTime = time();
    
    // Read stdout with timeout
    $output = '';
    $errorOutput = '';
    
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    
    while (true) {
        $output .= stream_get_contents($pipes[1]);
        $errorOutput .= stream_get_contents($pipes[2]);
        
        $status = proc_get_status($process);
        
        if (!$status['running']) {
            break;
        }
        
        if (time() - $startTime > $timeout) {
            proc_terminate($process);
            proc_close($process);
            error_log("Puppeteer timeout after $timeout seconds");
            return false;
        }
        
        usleep(100000); // 100ms
    }
    
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    
    proc_close($process);
    
    if ($output) {
        $data = json_decode($output, true);
        if ($data && isset($data['success']) && $data['success']) {
            return $data['content'] ?? '';
        }
    }
    
    if ($errorOutput) {
        error_log("Puppeteer error: " . substr($errorOutput, 0, 500));
    }
    
    return false;
}

/**
 * Alternative: Use shell_exec with timeout (simpler but less reliable)
 */
function scrapeWithPuppeteerSimple(string $url, int $timeout = 45): string|false {
    if (!_isUrlSafe($url)) {
        return false;
    }
    
    $scraperPath = __DIR__ . '/scraper.js';
    
    if (!file_exists($scraperPath)) {
        return false;
    }
    
    $escapedUrl = escapeshellarg($url);
    $cmd = sprintf('cd %s && timeout %d node scraper.js %s 2>&1', 
        escapeshellarg(__DIR__),
        $timeout,
        $escapedUrl
    );
    
    $output = shell_exec($cmd);
    
    if ($output) {
        $data = json_decode($output, true);
        if ($data && isset($data['success']) && $data['success']) {
            return $data['content'] ?? '';
        }
    }
    
    return false;
}
