<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['authenticated']) || empty($_SESSION['user_id']) || empty($_SESSION['session_fingerprint'])) {
    redirectToLogin();
}

$currentFingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));

if (!hash_equals($_SESSION['session_fingerprint'], $currentFingerprint)) {
    destroySession();
    redirectToLogin();
}

/* Validate session in DB */
$sessionId = session_id();
$stmt = $pdo->prepare('SELECT * FROM user_sessions WHERE session_id = :sid');
$stmt->execute(['sid' => $sessionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    if ((int)$row['user_id'] !== (int)$_SESSION['user_id'] ||
        !hash_equals($row['fingerprint'], $currentFingerprint) ||
        strtotime($row['expires_at']) < time()) {
        destroySession();
        redirectToLogin();
    }

    /* Sliding expiration */
    $pdo->prepare('UPDATE user_sessions SET expires_at = :exp WHERE session_id = :sid')
        ->execute(['exp' => date('Y-m-d H:i:s', time() + 1800), 'sid' => $sessionId]);
}

function redirectToLogin(): never {
    header('Location: /login.php');
    exit;
}

function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
