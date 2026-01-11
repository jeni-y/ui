<?php
declare(strict_types=1);
require_once __DIR__. '/../bootstrap.php';

class Mailer {
    public static function send(string $to, string $subject, string $body): void {
        $headers = "From: CloudApp <".$_ENV['SMTP_USER'].">\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8";

        if (!mail($to, $subject, $body, $headers)) {
            throw new RuntimeException("Email send failed");
        }
    }
}