<?php
declare(strict_types=1);

/* =========================
   Secure session start
========================= */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
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
   Core includes (example)
========================= */
// require_once __DIR__ . '/config/db.php';
// require_once __DIR__ . '/autoloader.php';