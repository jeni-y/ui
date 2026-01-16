<?php
require_once __DIR__ . '/backend/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Signup</title>
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

<h1>Create Account</h1>
<p class="subtitle">Join over 6 million others learning cyber security.</p>

<button class="google" onclick="googleAuth()">Continue with Google</button>

<div class="or"><span>or</span></div>

<form method="POST" action="/backend/auth/signup_handler.php">
    <input type="hidden" name="action" value="Login">

    <label>Email address</label>
    <input type="email" name="email" placeholder="example@example.com" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button class="primary">Continue</button>
</form>

<div class="footer">
    Already have an account?
    <a href="login.php" class="link">Login</a>
</div>

</div>

<script>
function googleAuth(){
    window.location.href = "https://accounts.google.com/";
}
</script>
</body>
</html>
