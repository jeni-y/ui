<?php
declare(strict_types=1);
require_once __DIR__. '/../bootstrap.php';
$envFile = __DIR__. '/../../../.env';

if (!is_readable($envFile)) {
    throw new RuntimeException("ENV file not readable: $envFile");
}

foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    $_ENV[$key] = $value;
    putenv("$key=$value");

}