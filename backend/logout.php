<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user_id'])) {
    $pdo->prepare(
        'DELETE FROM remember_tokens WHERE user_id = :uid'
    )->execute(['uid' => $_SESSION['user_id']]);
}

setcookie('remember_token', '', time() - 3600, '/');

$_SESSION = [];
session_destroy();

header('Location: /login_index.php');
exit;