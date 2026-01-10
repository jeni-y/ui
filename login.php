<?php
session_start();

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // DEMO LOGIN (replace with DB)
        if ($email === 'admin@example.com' && $password === '123456') {
            #$_SESSION['user'] = $email;
            $username = explode('@', $email)[0];
            $_SESSION['username'] = $username;
            header("Location: login_index.php");
            exit;
        } else {
            $message = "Invalid email or password";
            $type = "error";
        }
    }

    if ($_POST['action'] === 'signup') {
        $email = trim($_POST['email']);

        if (!empty($email)) {
            // DEMO SIGNUP (replace with DB insert)
            $message = "Account created successfully. Please log in.";
            $type = "success";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Authentication</title>

<style>
*{box-sizing:border-box;font-family:Arial,Helvetica,sans-serif}

body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    background:#fafafa;
}

.card{
    width:460px;
    background:#fff;
    padding:40px;
    margin-top:40px;
    border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,.1);
}

h1{margin-bottom:6px}
.subtitle{color:#555;margin-bottom:28px}

label{font-size:14px;margin-bottom:6px;display:block}
input{
    width:100%;
    padding:13px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:15px;
    margin-bottom:16px;
}

input:focus{outline:none;border-color:#9ef01a}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:6px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
}

.primary{
    background:#9ef01a;
}

.primary:hover{background:#8bdc12}

.google{
    background:#fff;
    border:1px solid #ccc;
    margin-bottom:20px;
}

.or{
    display:flex;
    align-items:center;
    margin:24px 0;
    color:#777;
}

.or::before,.or::after{
    content:"";
    flex:1;
    height:1px;
    background:#ddd;
}

.or span{margin:0 12px}

.link{
    color:#0066ff;
    cursor:pointer;
    text-decoration:none;
}

.footer{
    margin-top:16px;
    font-size:14px;
}

.message{
    padding:10px;
    border-radius:6px;
    margin-bottom:18px;
    text-align:center;
}

.error{background:#ffe6e6;color:#b00020}
.success{background:#e6fffa;color:#00695c}

.hidden{display:none}
</style>
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

    <form method="POST">
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
        Don’t have an account?
        <span class="link" onclick="showSignup()">Sign up</span>
    </div>
</div>

<!-- SIGN UP -->
<div id="signupBox" class="hidden">
    <h1>Sign Up</h1>
    <p class="subtitle">Join over 6 million others learning cyber security.</p>

    <button class="google" onclick="googleAuth()">Continue with Google</button>

    <div class="or"><span>or</span></div>

    <form method="POST">
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
