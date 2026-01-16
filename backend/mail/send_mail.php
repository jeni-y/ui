<?php
declare(strict_types=1);
require_once __DIR__ . '/mailer.php';

if ($argc < 4) {
    exit("Usage: php send_mail.php recipient subject body\n");
}

$to = $argv[1];
$subject = $argv[2];
$body = $argv[3];

Mailer::send($to, $subject, $body);
