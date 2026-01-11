<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'secure'   => true,   // HTTPS only
        'httponly' => true,   // JS can't access
        'samesite' => 'Strict'
    ]);
    session_start();
}

/* 🔐 Security Headers */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'");