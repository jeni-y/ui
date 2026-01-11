<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../security/otp.php';
require_once __DIR__ . '/../mail/Mailer.php';
require_once __DIR__ . '/../bootstrap.php';

// Get POST data
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    exit('Email and password are required.');
}

// Fetch user securely
$stmt = $pdo->prepare(
    'SELECT id, password, email_verified FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    exit('Invalid credentials');
}

if (!$user['email_verified']) {
    exit('Email not verified. Please check your inbox.');
}

// Generate OTP
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
    exit('Failed to send OTP email. Try again later.');
}

// Store user ID in session
$_SESSION['otp_user'] = $user['id'];
$_SESSION['otp_expires'] = time() + 300; // Store expiration timestamp

// Redirect to OTP verification page
header('Location: ../../verify_otp.php');
exit;