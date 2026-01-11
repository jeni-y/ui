<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../security/otp.php';
require_once __DIR__ . '/../mail/Mailer.php';
require_once __DIR__ . '/../bootstrap.php';

/* ---------------- Input Validation ---------------- */
$username = trim($_POST['username'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    exit('All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Invalid email address.');
}

if (strlen($password) < 8) {
    exit('Password must be at least 8 characters.');
}

/* ---------------- Check Existing User ---------------- */
$check = $pdo->prepare(
    'SELECT id FROM users WHERE email = :email OR username = :username'
);
$check->execute([
    'email'    => $email,
    'username' => $username
]);

if ($check->fetch()) {
    exit('Email or username already exists.');
}

/* ---------------- Begin Transaction ---------------- */
$pdo->beginTransaction();

try {
    /* ---------------- Hash Password ---------------- */
    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

    /* ---------------- Insert User (Postgres) ---------------- */
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password, email_verified)
         VALUES (:username, :email, :password, false)
         RETURNING id'
    );

    $stmt->execute([
        'username' => $username,
        'email'    => $email,
        'password' => $passwordHash
    ]);

    $userId = (int) $stmt->fetchColumn();

    /* ---------------- Generate OTP ---------------- */
    $otp = generateOtp();

    $otpStmt = $pdo->prepare(
        'INSERT INTO email_otps (user_id, otp_hash, expires_at)
         VALUES (:user_id, :otp_hash, :expires_at)'
    );

    $otpStmt->execute([
        'user_id'   => $userId,
        'otp_hash'  => password_hash($otp, PASSWORD_DEFAULT),
        'expires_at'=> otpExpiry() // must be Y-m-d H:i:s
    ]);

    /* ---------------- Commit Transaction ---------------- */
    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack();
    exit('Signup failed. Please try again.');
}

/* ---------------- Send Verification Email ---------------- */
Mailer::send(
    $email,
    'Verify your email',
    "Your OTP is: {$otp}\n\nValid for 5 minutes."
);

/* ---------------- Store OTP Session ---------------- */
$_SESSION['otp_user'] = $userId;
$_SESSION['otp_stage'] = 'email_verification';

/* ---------------- Redirect ---------------- */
header('Location: ../../verify_otp.php');
exit;