<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../error_log.php';

/* ---------------- Session Check ---------------- */
if (empty($_SESSION['otp_user'])) {
    $_SESSION['auth_error'] = 'Session expired. Please login again.';
    header('Location: /login.php');
    exit;
}

$userId = (int) $_SESSION['otp_user'];

/* ---------------- Rate Limiting (Session-based) ---------------- */
$_SESSION['otp_attempts'] ??= 0;
if ($_SESSION['otp_attempts'] >= 5) {
    $_SESSION['auth_error'] = 'Too many failed attempts. Please login again.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Handle OTP Submission ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['auth_error'] = 'Invalid request.';
    header('Location: /login.php');
    exit;
}

$otpInput = trim($_POST['otp'] ?? '');

if ($otpInput === '' || !ctype_digit($otpInput)) {
    $_SESSION['auth_error'] = 'Invalid OTP format.';
    exit;
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
    $_SESSION['auth_error'] = 'OTP not found.';
    exit;
}

/* ---------------- Expiry Check ---------------- */
if (new DateTimeImmutable() > new DateTimeImmutable($otpRow['expires_at'])) {
    cleanupOtpSession();
    $_SESSION['auth_error'] = 'OTP expired. Please login again.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Verify OTP ---------------- 
if (!password_verify($otpInput, $otpRow['otp_hash'])) {
    $_SESSION['otp_attempts']++;
    $_SESSION['auth_error'] = 'Invalid OTP.';
    exit;
}
*/
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
    $_SESSION['auth_error'] = 'Verification failed. Try again.';
    exit;
}

/* ---------------- FINALIZE AUTHENTICATED SESSION ---------------- */

// Fetch email (or username) from DB to store in session
$stmt = $pdo->prepare(
    'SELECT email FROM users WHERE id = :id'
);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Regenerate session safely
session_regenerate_id(true);

// Store user info in session
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $user['email']; // ⭐ this fixes login_index.php display
$_SESSION['authenticated'] = true;

/* ---------------- SESSION FINGERPRINT ---------------- */
$_SESSION['session_fingerprint'] = hash(
    'sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') .
    ($_SERVER['REMOTE_ADDR'] ?? '')
);

$_SESSION['last_activity'] = time();

/* ================= CREATE REMEMBER-ME TOKEN ================= */
$rememberToken = bin2hex(random_bytes(32));
$rememberHash  = hash('sha256', $rememberToken);

$fingerprint = $_SESSION['session_fingerprint'];

$pdo->prepare(
    'INSERT INTO remember_tokens (user_id, token_hash, fingerprint, expires_at)
     VALUES (:uid, :hash, :fp, :exp)'
)->execute([
    'uid'  => $userId,
    'hash' => $rememberHash,
    'fp'   => $fingerprint,
    'exp'  => date('Y-m-d H:i:s', time() + 30*24*60*60)
]);

setcookie('remember_token', $rememberToken, [
    'expires'  => time() + 30*24*60*60,
    'path'     => '/',
    'secure'   => false, // true when HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

/* ---------------- Cleanup OTP Session ---------------- */
cleanupOtpSession();

/* ---------------- Redirect ---------------- */
header('Location: /login_index.php');
exit;


/* ---------------- Helper ---------------- */
function cleanupOtpSession(): void {
    unset(
        $_SESSION['otp_user'],
        $_SESSION['otp_attempts'],
        $_SESSION['otp_stage']
    );
}
