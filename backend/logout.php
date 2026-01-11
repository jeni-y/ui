<?php
session_start();
session_destroy();
require_once __DIR__. '/../bootstrap.php';
setcookie(session_name(), '', time() - 3600);
header("Location: /login.php");