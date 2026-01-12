<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../security/otp.php';
require_once __DIR__ . '/../mail/mailer.php';
require_once __DIR__ . '/../bootstrap.php';

/* ---------------- Input Validation ---------------- */
$username = trim($_POST['email'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    $_SESSION['auth_error'] = 'Username, email and password are required.';
    header('Location: /login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['auth_error'] = 'Invalid email address.';
    header('Location: /login.php');
    exit;
}

if (strlen($username) > 50) {
    $_SESSION['auth_error'] = 'Username must be 50 characters or fewer.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Check Existing User ---------------- */
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email OR username = :username');
$stmt->execute(['email' => $email, 'username' => $username]);
if ($stmt->fetch()) {
    $_SESSION['auth_error'] = 'Email or username already registered. Please log in.';
    header('Location: /login.php');
    exit;
}

/* ---------------- Hash Password ---------------- */
$hash = password_hash($password, PASSWORD_DEFAULT);

/* ---------------- Create User ---------------- */
try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password, email_verified)
         VALUES (:username, :email, :password, false)'
    );
    $stmt->execute([
        'username' => $username,
        'email'    => $email,
        'password' => $hash
    ]);
    $userId = $pdo->lastInsertId();
} catch (PDOException $e) {
    $_SESSION['auth_error'] = 'Signup failed: ' . $e->getMessage();
    header('Location: /login.php');
    exit;
}

/* ---------------- Generate Verification OTP ---------------- */
$otp = generateOtp();
$otpStmt = $pdo->prepare(
    'INSERT INTO email_otps (user_id, otp_hash, expires_at)
     VALUES (:user_id, :otp_hash, :expires_at)'
);
$otpStmt->execute([
    'user_id'   => $userId,
    'otp_hash'  => password_hash($otp, PASSWORD_DEFAULT),
    'expires_at'=> otpExpiry()
]);

/* ---------------- Send Verification Email ---------------- */
if (!Mailer::send($email, 'Verify Your Account', "Your signup OTP is: {$otp}\n\nValid for 5 minutes.")) {
    // Don’t exit — just set a warning
    $_SESSION['auth_error'] = 'Failed to send verification email. Please check your mail settings.';
}

/* ---------------- Prepare Session ---------------- */
$_SESSION['otp_user']   = $userId;
$_SESSION['otp_stage']  = 'signup_verification';
$_SESSION['otp_expires']= time() + 300; // 5 minutes expiry

/* ---------------- Redirect ---------------- */
header('Location: /otp_verify.php');
exit;