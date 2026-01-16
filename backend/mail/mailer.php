<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static array $config;

    private static function initConfig(): void {
        if (isset(self::$config)) return; // already loaded
        foreach (['SMTP_HOST','SMTP_USER','SMTP_PASS','SMTP_PORT','SMTP_FROM'] as $key) {
            if (empty($_ENV[$key])) {
                throw new Exception("Missing env variable: $key");
            }
        }
        self::$config = [
            'host' => $_ENV['SMTP_HOST'],
            'user' => $_ENV['SMTP_USER'],
            'pass' => $_ENV['SMTP_PASS'],
            'port' => (int) $_ENV['SMTP_PORT'],
            'from' => $_ENV['SMTP_FROM'],
        ];
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        try {
            self::initConfig();

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = self::$config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$config['user'];
            $mail->Password   = self::$config['pass'];
            $mail->Port       = self::$config['port'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPKeepAlive = false; // no persistent connection

            $mail->setFrom(self::$config['from'], 'CloudApp');
            $mail->addAddress($to);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("MAIL ERROR: " . $e->getMessage());
            return false;
        }
    }
}
