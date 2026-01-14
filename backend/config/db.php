<?php
declare(strict_types=1);

// Path outside project folder
$credPath = __DIR__. '/../../../db_credentials.json';

// Load and decode JSON
$creds = json_decode(file_get_contents($credPath), true);

if (!$creds) {
    throw new RuntimeException("Failed to load DB credentials");
}

// Build DSN for PostgreSQL
$dsn = sprintf(
    "pgsql:host=%s;port=%d;dbname=%s",
    $creds['host'],
    $creds['port'],
    $creds['dbname']
);

// Create PDO instance
$pdo = new PDO(
    $dsn,
    $creds['user'],
    $creds['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);