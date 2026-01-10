<?php
session_start();

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Demo validation (replace with DB logic)
        if ($email === 'admin@example.com' && $password === '123456') {
            $_SESSION['user'] = $email;
            $message = "Login successful!";
            $type = "success";
            header("Location: login_index.php");
        } else {
            $message = "Invalid email or password";
            $type = "error";
        }
    }

    if ($_POST['action'] === 'signup') {
        $username = trim($_POST['username']);

        if(!empty($username)){
            $_SESSION['username'] = $username;   
        }

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm = trim($_POST['confirm_password']);

        if ($password !== $confirm) {
            $message = "Passwords do not match";
            $type = "error";
        } else {
            // Demo success (replace with DB insert)
            $message = "Account created successfully.";
            $type = "success";
            header("Location: login_index.php");
            exit;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login / Sign Up</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
body{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#667eea,#764ba2);
}
.auth-card{
    width:380px;
    background:#fff;
    padding:35px;
    border-radius:12px;
    box-shadow:0 20px 40px rgba(0,0,0,0.2);
}
h2{text-align:center;margin-bottom:10px;color:#333;}
.subtitle{text-align:center;font-size:14px;color:#777;margin-bottom:25px;}

.message{
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    text-align:center;
    font-size:14px;
}
.error{background:#ffe6e6;color:#b00020;}
.success{background:#e6fffa;color:#00695c;}

.form-group{margin-bottom:16px;}
label{display:block;margin-bottom:6px;font-size:14px;color:#555;}
input{
    width:100%;
    padding:11px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:15px;
}
input:focus{border-color:#667eea;outline:none;}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:6px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

.toggle{
    text-align:center;
    margin-top:18px;
    font-size:14px;
}
.toggle a{
    color:#667eea;
    text-decoration:none;
    cursor:pointer;
}
.toggle a:hover{text-decoration:underline;}

.hidden{display:none;}
</style>
</head>

<body>

<div class="auth-card">

    <h2 id="title">Login</h2>
    <p class="subtitle" id="subtitle">Please login to continue</p>

    <?php if (!empty($message)): ?>
        <div class="message <?= $type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form method="POST" id="loginForm">
        <input type="hidden" name="action" value="login">

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <!-- SIGN UP FORM -->
    <form method="POST" id="signupForm" class="hidden">
        <input type="hidden" name="action" value="signup">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit">Sign Up</button>
    </form>

    <div class="toggle">
        <span id="toggleText">
            New user? <a onclick="showSignup()">Create an account</a>
        </span>
    </div>

</div>

<script>
function showSignup(){
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('signupForm').classList.remove('hidden');
    document.getElementById('title').innerText = 'Sign Up';
    document.getElementById('subtitle').innerText = 'Create a new account';
    document.getElementById('toggleText').innerHTML =
        'Already have an account? <a onclick="showLogin()">Login</a>';
}

function showLogin(){
    document.getElementById('signupForm').classList.add('hidden');
    document.getElementById('loginForm').classList.remove('hidden');
    document.getElementById('title').innerText = 'Login';
    document.getElementById('subtitle').innerText = 'Please login to continue';
    document.getElementById('toggleText').innerHTML =
        'New user? <a onclick="showSignup()">Create an account</a>';
}
</script>

</body>
</html>