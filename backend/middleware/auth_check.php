<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/* =========================
   Ensure session exists
========================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* =========================
   Authentication check
========================= */
if (
    empty($_SESSION['authenticated']) ||
    empty($_SESSION['user_id']) ||
    empty($_SESSION['session_fingerprint'])
) {
    redirectToLogin();
}

/* =========================
   Session hijack protection
========================= */
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ipPrefix = preg_replace('/\.\d+$/', '', $ip);

$currentFingerprint = hash(
    'sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? '') . $ipPrefix
);

if (!hash_equals($_SESSION['session_fingerprint'], $currentFingerprint)) {
    destroySession();
    redirectToLogin();
}

/* =========================
   Validate session against DB
========================= */
$sessionId = session_id();

$stmt = $pdo->prepare(
    'SELECT user_id, fingerprint, expires_at
     FROM user_sessions
     WHERE session_id = :sid'
);
$stmt->execute(['sid' => $sessionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$row ||
    (int)$row['user_id'] !== (int)$_SESSION['user_id'] ||
    !hash_equals($row['fingerprint'], $currentFingerprint) ||
    strtotime($row['expires_at']) < time()
) {
    destroySession();
    redirectToLogin();
}

/* =========================
   Sliding expiration
========================= */
$pdo->prepare(
    'UPDATE user_sessions
     SET expires_at = :exp
     WHERE session_id = :sid'
)->execute([
    'exp' => date('Y-m-d H:i:s', time() + 1800),
    'sid' => $sessionId
]);

/* =========================
   Helpers
========================= */
function redirectToLogin(): never
{
    header('Location: /login.php');
    exit;
}

function destroySession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}