<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../security/otp.php';
require_once __DIR__ . '/../mail/mailer.php';
require_once __DIR__ . '/../bootstrap.php';

// Get POST data
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['auth_error'] = 'Email and password are required.';
    exit;
}

// Fetch user securely
$stmt = $pdo->prepare(
    'SELECT id, password, email_verified FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['auth_error'] = 'Invalid credentials';
    exit;
}

if (!$user['email_verified']) {
    $_SESSION['auth_error'] = 'Email not verified. Please check your inbox.';
    exit;
}

/* =========================
   Generate OTP
========================= */
$otp = generateOtp();

// Store OTP in database
$insert = $pdo->prepare(
    'INSERT INTO email_otps (user_id, otp_hash, expires_at) VALUES (:user_id, :otp_hash, :expires_at)'
);
$insert->execute([
    'user_id'    => $user['id'],
    'otp_hash'   => password_hash($otp, PASSWORD_DEFAULT),
    'expires_at' => otpExpiry()
]);

// Send OTP via email
if (!Mailer::send($email, 'Login OTP', "Your login OTP: $otp")) {
    $_SESSION['auth_error'] = 'Failed to send OTP email. Try again later.';
    exit;
}

/* =========================
   Prepare session for OTP verification
========================= */
$_SESSION['otp_user'] = $user['id'];
$_SESSION['otp_expires'] = time() + 300; // 5 minutes expiry

// Generate fingerprint now
$fingerprint = hash(
    'sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? '')
);

// Reserve session row in DB (will be validated after OTP)
$sessionId = session_id();
$stmt = $pdo->prepare(
    "INSERT INTO user_sessions (user_id, session_id, fingerprint, expires_at)
     VALUES (:uid, :sid, :fp, :exp)
     ON CONFLICT (session_id) DO UPDATE
     SET fingerprint = EXCLUDED.fingerprint,
         expires_at = EXCLUDED.expires_at"
);
$stmt->execute([
    'uid' => $user['id'],
    'sid' => $sessionId,
    'fp'  => $fingerprint,
    'exp' => date('Y-m-d H:i:s', time() + 1800) // 30 min expiry
]);

// Redirect to OTP verification page
header('Location: /otp_verify.php');
exit;