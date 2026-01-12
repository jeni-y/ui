<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/db.php'; // $pdo connection

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
    destroySession();
    redirectToLogin();
}

/* =========================
   Validate session against DB
========================= */
$sessionId = session_id();
$stmt = $pdo->prepare(
    "SELECT user_id, fingerprint, expires_at 
     FROM user_sessions 
     WHERE session_id = :sid"
);
$stmt->execute(['sid' => $sessionId]);
$row = $stmt->fetch();

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
   Idle timeout (30 min)
========================= */
$MAX_IDLE_TIME = 1800;
if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $MAX_IDLE_TIME
) {
    destroySession();
    redirectToLogin();
}
$_SESSION['last_activity'] = time();

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
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}