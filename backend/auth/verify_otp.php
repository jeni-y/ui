<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

/* ---------------- Fail fast: session check ---------------- */
if (empty($_SESSION['otp_user'])) {
    $_SESSION['auth_error'] = 'Session expired. Please login again.';
    header('Location: /login.php');
    exit;
}

$userId = (int) $_SESSION['otp_user'];

/* ---------------- Rate Limiting ---------------- */
$_SESSION['otp_attempts'] ??= 0;
if ($_SESSION['otp_attempts'] >= 5) {
    $_SESSION['auth_error'] = 'Too many failed attempts. Please login again.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Validate OTP input ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['otp'])) {
    $_SESSION['auth_error'] = 'Invalid request.';
    header('Location: /login.php');
    exit;
}

$otpInput = trim($_POST['otp']);
if (!ctype_digit($otpInput)) {
    $_SESSION['otp_attempts']++;
    $_SESSION['auth_error'] = 'Invalid OTP format.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Fetch latest OTP efficiently ---------------- */
$stmt = $pdo->prepare(
    'SELECT id, otp_hash, expires_at
     FROM email_otps
     WHERE user_id = :user_id
     ORDER BY id DESC
     LIMIT 1'
);
$stmt->execute(['user_id' => $userId]);
$otpRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$otpRow) {
    $_SESSION['otp_attempts']++;
    $_SESSION['auth_error'] = 'OTP not found.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Check expiry ---------------- */
$expiresAt = new DateTimeImmutable($otpRow['expires_at']);
if (new DateTimeImmutable() > $expiresAt) {
    // Optionally delete expired OTPs
    $pdo->prepare('DELETE FROM email_otps WHERE user_id = :id')->execute(['id' => $userId]);

    cleanupOtpSession();
    $_SESSION['auth_error'] = 'OTP expired. Please login again.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Verify OTP ---------------- 
if (!password_verify($otpInput, $otpRow['otp_hash'])) {
    $_SESSION['otp_attempts']++;
    $_SESSION['auth_error'] = 'Incorrect OTP.';
    header('Location: /login.php');
    exit;
}
*/
/* ---------------- OTP correct: Authenticate user ---------------- */
try {
    $pdo->beginTransaction();

    // Mark email as verified and cleanup OTPs
    $pdo->prepare('UPDATE users SET email_verified = TRUE WHERE id = :id')
        ->execute(['id' => $userId]);
    $pdo->prepare('DELETE FROM email_otps WHERE user_id = :id')
        ->execute(['id' => $userId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('OTP Verification DB Error: ' . $e->getMessage());
    $_SESSION['auth_error'] = 'Verification failed. Try again.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Secure session ---------------- */
session_regenerate_id(true);

// Fetch email for session display
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $user['email'] ?? '';
$_SESSION['authenticated'] = true;
$_SESSION['session_fingerprint'] = hash(
    'sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? '')
);
$_SESSION['last_activity'] = time();

/* ---------------- Remember-me token ---------------- */
$rememberToken = bin2hex(random_bytes(32));
$rememberHash  = hash('sha256', $rememberToken);
$pdo->prepare(
    'INSERT INTO remember_tokens (user_id, token_hash, fingerprint, expires_at)
     VALUES (:uid, :hash, :fp, :exp)'
)->execute([
    'uid'  => $userId,
    'hash' => $rememberHash,
    'fp'   => $_SESSION['session_fingerprint'],
    'exp'  => date('Y-m-d H:i:s', time() + 30*24*60*60)
]);

setcookie('remember_token', $rememberToken, [
    'expires'  => time() + 30*24*60*60,
    'path'     => '/',
    'secure'   => true,   // ✅ always true in production with HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

/* ---------------- Cleanup OTP session ---------------- */
cleanupOtpSession();

/* ---------------- Redirect ---------------- */
header('Location: /login_index.php');
exit;

/* ---------------- Helper ---------------- */
function cleanupOtpSession(): void {
    unset($_SESSION['otp_user'], $_SESSION['otp_attempts'], $_SESSION['otp_stage']);
}
