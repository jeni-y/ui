<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        // 🔒 Validate env first (VERY IMPORTANT)
        foreach (['SMTP_HOST','SMTP_USER','SMTP_PASS','SMTP_PORT','SMTP_FROM'] as $key) {
            if (empty($_ENV[$key])) {
                throw new RuntimeException("Missing env variable: $key");
            }
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];   // apikey
            $mail->Password   = $_ENV['SMTP_PASS'];   // SG.xxxxx
            $mail->Port       = (int) $_ENV['SMTP_PORT'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            // Headers
            $mail->setFrom($_ENV['SMTP_FROM'], 'CloudApp');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
        } catch (Exception $e) {
            error_log('MAIL ERROR: ' . $mail->ErrorInfo);
            throw new RuntimeException('Email send failed');
        }
    }
}