<?php
declare(strict_types=1);

// Load credentials from JSON or PHP file
$tokenFile = __DIR__ . '/token.json';

if (!file_exists($tokenFile)) {
    throw new RuntimeException('Token file not found.');
}

$tokens = json_decode(file_get_contents($tokenFile), true, 512, JSON_THROW_ON_ERROR);

// Set environment variables
if (isset($tokens['GITHUB_TOKEN'])) {
    putenv('GITHUB_TOKEN=' . $tokens['GITHUB_TOKEN']);
}