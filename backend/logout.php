<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';


/* 🔥 REMOVE REMEMBER TOKEN (DB + COOKIE) */
if (!empty($_COOKIE['remember_token'])) {

    $tokenHash = hash('sha256', $_COOKIE['remember_token']);

    $pdo->prepare(
        'DELETE FROM remember_tokens WHERE token_hash = :hash'
    )->execute(['hash' => $tokenHash]);

    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

/* 🔥 DESTROY SESSION */
$_SESSION = [];
session_destroy();

/* 🔁 REDIRECT */
header('Location: /login.php');
exit;
