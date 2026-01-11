<?php
declare(strict_types=1);
require_once __DIR__. '/../bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}