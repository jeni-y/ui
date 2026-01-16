<?php
require_once __DIR__ . '/backend/bootstrap.php';
/*
$message = '';
$type = '';

if (!empty($_SESSION['auth_error'])) {
    $message = $_SESSION['auth_error'];
    $type = 'error';
    unset($_SESSION['auth_error']); // clear after showing
}*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="card">

<?php 
// Add this before the message display
if (isset($_SESSION['auth_error'])) {
    $message = $_SESSION['auth_error'];
    $type = 'error';
    unset($_SESSION['auth_error']);
}
?>

<?php if (!empty($message)): ?>
    <div class="message <?= $type ?? '' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<h1>Welcome back!</h1>
<p class="subtitle">Sign into your account</p>

<form method="POST" action="/backend/auth/login_handler.php">
    <input type="hidden" name="action" value="Sign in">

    <label>Email address</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button class="primary">Login</button>
</form>

<div class="or"><span>or</span></div>
<button class="google" onclick="googleAuth()">Continue with Google</button>

<div class="footer">
    Don’t have an account?
    <a href="sigin.php" class="link">Sign up</a>
</div>

</div>

<script>
function googleAuth(){
    window.location.href = "https://accounts.google.com/";
}
</script>
</body>
</html>
