<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

/* =========================
   Secure session settings
========================= */
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,   // HTTPS only
        'httponly' => true,   // JS not accessible
        'samesite' => 'Strict'
    ]);

    session_start();
}

/* =========================
   Authentication check
========================= */
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['authenticated']) ||
    empty($_SESSION['session_fingerprint'])
) {
    redirectToLogin();
}

/* =========================
   Session hijack protection
========================= */
$currentFingerprint = hash(
    'sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') .
    ($_SERVER['REMOTE_ADDR'] ?? '')
);

if (!hash_equals($_SESSION['session_fingerprint'], $currentFingerprint)) {
    session_destroy();
    redirectToLogin();
}

/* =========================
   Idle timeout (30 minutes)
========================= */
$MAX_IDLE_TIME = 1800;

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $MAX_IDLE_TIME
) {
    session_destroy();
    redirectToLogin();
}

$_SESSION['last_activity'] = time();

/* =========================
   Helper
========================= */
function redirectToLogin(): never
{
    header('Location: /login.php');
    exit;
}