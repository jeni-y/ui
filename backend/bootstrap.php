<?php
declare(strict_types=1);


/* =====================================================
   BOOTSTRAP — INCLUDE FIRST IN EVERY FILE
   HTTP SAFE VERSION (NO HTTPS REQUIRED)
===================================================== */
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
/* =========================
   SESSION CONFIG
   (MUST BE BEFORE session_start)
========================= */
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,       // browser close
        'path'     => '/',
        'secure'   => false,   // ✅ HTTP ONLY
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

/* =========================
   DATABASE + CSRF
========================= */
require_once __DIR__ . '/config/db.php';


/* =========================
   SESSION TIMEOUT (30 min)
========================= */
if (
    isset($_SESSION['authenticated']) &&
    !empty($_SESSION['last_activity']) &&
    time() - $_SESSION['last_activity'] > 1800
) {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}

$_SESSION['last_activity'] = time();

require_once __DIR__ . '/middleware/csrf.php';
/* =========================
   SECURITY HEADERS (HTTP SAFE)
========================= */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://your-cdn.com; connect-src 'self' https://api.example.com;");

/* =========================
   AUTO LOGIN USING REMEMBER TOKEN
========================= */
if (
    empty($_SESSION['authenticated']) &&
    !empty($_COOKIE['remember_token'])
) {
    $tokenHash = hash('sha256', $_COOKIE['remember_token']);

    $fingerprint = hash(
        'sha256',
        ($_SERVER['HTTP_USER_AGENT'] ?? '') .
        ($_SERVER['REMOTE_ADDR'] ?? '')
    );

    $stmt = $pdo->prepare(
        'SELECT id, user_id, expires_at
         FROM remember_tokens
         WHERE token_hash = :hash
           AND fingerprint = :fp'
    );
    $stmt->execute([
        'hash' => $tokenHash,
        'fp'   => $fingerprint
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && strtotime($row['expires_at']) > time()) {

        /* 🔁 TOKEN ROTATION */
        $newToken = bin2hex(random_bytes(32));
        $newHash  = hash('sha256', $newToken);

        $pdo->prepare(
            'UPDATE remember_tokens
             SET token_hash = :hash,
                 expires_at = :exp
             WHERE id = :id'
        )->execute([
            'hash' => $newHash,
            'exp'  => date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60),
            'id'   => $row['id']
        ]);

        setcookie('remember_token', $newToken, [
            'expires'  => time() + 30 * 24 * 60 * 60,
            'path'     => '/',
            'secure'   => false, // ✅ HTTP SAFE
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        /* 🔐 NEW AUTH SESSION */
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$row['user_id'];
        $_SESSION['authenticated'] = true;
        $_SESSION['session_fingerprint'] = $fingerprint;
        $_SESSION['last_activity'] = time();

        /* OPTIONAL: DB SESSION TRACKING */
        $pdo->prepare(
            'INSERT INTO user_sessions (user_id, session_id, fingerprint, expires_at)
             VALUES (:uid, :sid, :fp, :exp)'
        )->execute([
            'uid' => $row['user_id'],
            'sid' => session_id(),
            'fp'  => $fingerprint,
            'exp' => date('Y-m-d H:i:s', time() + 1800)
        ]);
    }
}