<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../bootstrap.php';

/* ---------------- Session Check ---------------- */
if (empty($_SESSION['otp_user'])) {
    exit('Session expired. Please login again.');
}

$userId = (int) $_SESSION['otp_user'];

/* ---------------- Rate Limiting (Session-based) ---------------- */
$_SESSION['otp_attempts'] ??= 0;
if ($_SESSION['otp_attempts'] >= 5) {
    exit('Too many failed attempts. Please login again.');
}

/* ---------------- Handle OTP Submission ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request.');
}

$otpInput = trim($_POST['otp'] ?? '');

if ($otpInput === '' || !ctype_digit($otpInput)) {
    exit('Invalid OTP format.');
}

/* ---------------- Fetch Latest OTP ---------------- */
$stmt = $pdo->prepare(
    'SELECT id, otp_hash, expires_at
     FROM email_otps
     WHERE user_id = :user_id
     ORDER BY id DESC
     LIMIT 1'
);
$stmt->execute(['user_id' => $userId]);
$otpRow = $stmt->fetch();

if (!$otpRow) {
    exit('OTP not found.');
}

/* ---------------- Expiry Check ---------------- */
if (new DateTimeImmutable() > new DateTimeImmutable($otpRow['expires_at'])) {
    cleanupOtpSession();
    exit('OTP expired. Please login again.');
}

/* ---------------- Verify OTP ---------------- */
if (!password_verify($otpInput, $otpRow['otp_hash'])) {
    $_SESSION['otp_attempts']++;
    exit('Invalid OTP.');
}

/* ---------------- OTP VALID → AUTHENTICATE USER ---------------- */
$pdo->beginTransaction();

try {
    // Mark email as verified
    $pdo->prepare(
        'UPDATE users SET email_verified = TRUE WHERE id = :id'
    )->execute(['id' => $userId]);

    // Delete all OTPs for user
    $pdo->prepare(
        'DELETE FROM email_otps WHERE user_id = :id'
    )->execute(['id' => $userId]);

    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack();
    exit('Verification failed. Try again.');
}

/* ---------------- Secure Login Session ---------------- */
session_regenerate_id(true);

$_SESSION['user_id'] = $userId;
$_SESSION['authenticated'] = true;

/* ---------------- Cleanup OTP Session ---------------- */
cleanupOtpSession();

/* ---------------- Redirect ---------------- */
header('Location: /dashboard.php');
exit;


/* ---------------- Helper ---------------- */
function cleanupOtpSession(): void {
    unset(
        $_SESSION['otp_user'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_stage']
    );
}