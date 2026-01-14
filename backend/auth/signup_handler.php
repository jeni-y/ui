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
    header('Location: /signup.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['auth_error'] = 'Invalid email address.';
    header('Location: /signup.php');
    exit;
}

/* =========================
   2. Derive Username (from Gmail)
========================= */
$username = explode('@', $email)[0]; // example: ajay123@gmail.com → ajay123

if (strlen($username) > 50) {
    $_SESSION['auth_error'] = 'Derived username is too long.';
    header('Location: /signup.php');
    exit;
}

/* =========================
   3. Check Existing User
========================= */
$stmt = $pdo->prepare(
    'SELECT id FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    $_SESSION['auth_error'] = 'Email already registered. Please log in.';
    header('Location: /login.php');
    exit;
}

/* =========================
   4. Hash Password
========================= */
$hash = password_hash($password, PASSWORD_DEFAULT);

/* =========================
   5. Create User
========================= */
$stmt = $pdo->prepare(
    'INSERT INTO users (username, email, password, email_verified)
     VALUES (:username, :email, :password, false)'
);

$stmt->execute([
    'username' => $username,
    'email'    => $email,
    'password' => $hash
]);

$userId = (int)$pdo->lastInsertId();

/* =========================
   6. Generate OTP
========================= */
$otp = generateOtp();

$otpStmt = $pdo->prepare(
    'INSERT INTO email_otps (user_id, otp_hash, expires_at)
     VALUES (:uid, :hash, :exp)'
);

$otpStmt->execute([
    'uid'  => $userId,
    'hash' => password_hash($otp, PASSWORD_DEFAULT),
    'exp'  => otpExpiry()
]);

/* =========================
   7. Send Verification Email
========================= */
$mailSent = Mailer::send(
    $email,
    'Verify Your Email',
    "Your verification OTP is: {$otp}\n\nValid for 5 minutes."
);

if (!$mailSent) {
    $_SESSION['auth_error'] = 'Failed to send verification email.';
    header('Location: /signup.php');
    exit;
}

/* =========================
   8. Prepare OTP Session
========================= */
session_regenerate_id(true);

$_SESSION['otp_user']    = $userId;
$_SESSION['otp_stage']   = 'signup';
$_SESSION['otp_expires'] = time() + 300;

/* =========================
   9. Redirect
========================= */
header('Location: /otp_verify.php');
exit;