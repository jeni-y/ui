<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Send an email using PHPMailer.
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @return bool True if sent successfully, false otherwise
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        // 🔒 Validate env first
        foreach (['SMTP_HOST','SMTP_USER','SMTP_PASS','SMTP_PORT','SMTP_FROM'] as $key) {
            if (empty($_ENV[$key])) {
                error_log("Missing env variable: $key");
                return false;
            }
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];   // usually "apikey" for SendGrid
            $mail->Password   = $_ENV['SMTP_PASS'];   // SG.xxxxx API key
            $mail->Port       = (int) $_ENV['SMTP_PORT'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            // Headers
            $mail->setFrom($_ENV['SMTP_FROM'], 'CloudApp');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            if ($mail->send()) {
                error_log("MAIL SUCCESS: sent to $to");
                return true;
            } else {
                error_log("MAIL ERROR: " . $mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("MAIL EXCEPTION: " . $e->getMessage());
            return false;
        }
    }
}