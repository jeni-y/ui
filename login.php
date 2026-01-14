<?php
/*
$message = '';
$type = '';

if (!empty($_SESSION['auth_error'])) {
    $message = $_SESSION['auth_error'];
    $type = 'error';
    unset($_SESSION['auth_error']); // clear after showing
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Authentication</title>
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="card">

<?php if (!empty($message)): ?>
    <div class="message <?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- LOGIN -->
<div id="loginBox">
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
        <span class="link" onclick="showSignup()">Sign in</span>
    </div>
</div>

<!-- SIGN UP -->
<div id="signupBox" class="hidden">
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
        <span class="link" onclick="showLogin()">Login</span>
    </div>
</div>

</div>

<script>
function showSignup(){
    document.getElementById('loginBox').classList.add('hidden');
    document.getElementById('signupBox').classList.remove('hidden');
}
function showLogin(){
    document.getElementById('signupBox').classList.add('hidden');
    document.getElementById('loginBox').classList.remove('hidden');
}
function googleAuth(){
    window.location.href = "https://accounts.google.com/";
}
</script>
</body>
</html>