<?php
require_once __DIR__ . '/../config/db.php';

$sessionId = session_id();

$stmt = $pdo->prepare(
    "SELECT user_id, fingerprint, expires_at 
     FROM user_sessions 
     WHERE session_id = :sid"
);
$stmt->execute(['sid' => $sessionId]);
$sessionRow = $stmt->fetch();

if (!$sessionRow) {
    session_destroy();
    redirectToLogin();
}

// Verify user matches
if ((int)$sessionRow['user_id'] !== (int)$_SESSION['user_id']) {
    session_destroy();
    redirectToLogin();
}

// Verify fingerprint
if (!hash_equals($sessionRow['fingerprint'], $currentFingerprint)) {
    session_destroy();
    redirectToLogin();
}

// Verify expiry
if (strtotime($sessionRow['expires_at']) < time()) {
    session_destroy();
    redirectToLogin();
}