<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/middleware/csrf.php';
/* =========================
   Secure session config
========================= */
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,               // session dies on browser close
        'path'     => '/',
        'secure'   => false,           // true in HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/* =========================
   Security headers
========================= */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");

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
    $row = $stmt->fetch();

    if ($row && strtotime($row['expires_at']) > time()) {

        // 🔁 Rotate token
        $newToken = bin2hex(random_bytes(32));
        $newHash  = hash('sha256', $newToken);

        $pdo->prepare(
            'UPDATE remember_tokens
             SET token_hash = :hash,
                 expires_at = :exp
             WHERE id = :id'
        )->execute([
            'hash' => $newHash,
            'exp'  => date('Y-m-d H:i:s', time() + 30*24*60*60),
            'id'   => $row['id']
        ]);

        setcookie('remember_token', $newToken, [
            'expires'  => time() + 30*24*60*60,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        // 🔐 Create NEW authenticated session
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$row['user_id'];
        $_SESSION['authenticated'] = true;
        $_SESSION['session_fingerprint'] = $fingerprint;
        $_SESSION['last_activity'] = time();

        // Store session in DB (reuse your system)
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