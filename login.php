<?php
session_start();

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
    }

    if ($_POST['action'] === 'signup') {
        $email = trim($_POST['email']);

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Authentication</title>
</head>
<link rel="stylesheet" href="assets/css/login.css">
<body>

<div class="card">

<?php if (!empty($message)): ?>
    <div class="message <?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- LOGIN -->
<div id="loginBox">
    <h1>Welcome back!</h1>
    <p class="subtitle">Sign into your account</p>

    <form method="POST" action = "backend/auth/signup_handler.php">
        <input type="hidden" name="action" value="login">

        <label>Email address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="primary">Log in</button>
    </form>

    <div class="or"><span>or</span></div>

    <button class="google" onclick="googleAuth()">Continue with Google</button>

    <div class="footer">
        Already have an account Login
        <span class="link" onclick="showSignup()">Login</span>
    </div>
</div>

<!-- SIGN UP -->
<div id="signupBox" class="hidden">
    <h1>Login</h1>
    <p class="subtitle">Join over 6 million others learning cyber security.</p>

    <button class="google" onclick="googleAuth()">Continue with Google</button>

    <div class="or"><span>or</span></div>

    <form method="POST" action = "backend/auth/login_handler.php">
        <input type="hidden" name="action" value="signup">

        <label>Email address</label>
        <input type="email" name="email" placeholder="example@example.com" required>

        <button class="primary">Continue</button>
    </form>

    <div class="footer">
        By signing up, you agree to our
        <a href="#" class="link">Terms and Conditions</a><br><br>
        Already have an account?
        <span class="link" onclick="showLogin()">Log in</span>
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
