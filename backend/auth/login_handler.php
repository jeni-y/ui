<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../security/otp.php';
require_once __DIR__ . '/../mail/mailer.php';

if ($_ENV['APP_ENV'] ?? 'dev' === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
/* =========================
   1. Validate Input
========================= */
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['auth_error'] = 'Email and password are required.';
    header('Location: /login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['auth_error'] = 'Invalid email address.';
    header('Location: /login.php');
    exit;
}

/* =========================
   2. Fetch User
========================= */
$stmt = $pdo->prepare(
    'SELECT id, password, email_verified FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['auth_error'] = 'Invalid email or password.';
    header('Location: /login.php');
    exit;
}

if (!$user['email_verified']) {
    $_SESSION['auth_error'] = 'Please verify your email first.';
    header('Location: /login.php');
    exit;
}
session_regenerate_id(true);

/* =========================
   3. Generate OTP
========================= */
$otp = generateOtp();

$otpStmt = $pdo->prepare(
    'INSERT INTO email_otps (user_id, otp_hash, expires_at)
     VALUES (:uid, :hash, :exp)'
);
$otpStmt->execute([
    'uid'  => $user['id'],
    'hash' => password_hash($otp, PASSWORD_DEFAULT),
    'exp'  => otpExpiry()
]);

/* =========================
   4. Send OTP Email
========================= */
$mailSent = Mailer::send(
    $email,
    'Your Login OTP',
    "Your OTP is: {$otp}\n\nValid for 5 minutes."
);

if (!$mailSent) {
    $_SESSION['auth_error'] = 'Failed to send OTP email.';
    header('Location: /login.php');
    exit;
}

/* =========================
   5. Prepare OTP Session
========================= */


$_SESSION['otp_user']    = $user['id'];
$_SESSION['otp_stage']   = 'login';
$_SESSION['otp_expires'] = time() + 300;

/* =========================
   6. Redirect
========================= */
header('Location: /otp_verify.php');
exit;