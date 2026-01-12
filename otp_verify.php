<?php
declare(strict_types=1);
require_once __DIR__ . 'backend/bootstrap.php';

/* If user has no OTP session, redirect */
if (empty($_SESSION['otp_user'])) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<style>
    body {
        margin: 0;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .otp-card {
        background: #020617;
        padding: 2rem;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 20px 40px rgba(0,0,0,.6);
    }

    h2 {
        margin-bottom: .5rem;
        text-align: center;
    }

    p {
        text-align: center;
        color: #cbd5f5;
        font-size: .9rem;
        margin-bottom: 1.5rem;
    }

    input {
        width: 100%;
        padding: .9rem;
        font-size: 1.2rem;
        letter-spacing: .4rem;
        text-align: center;
        border-radius: 8px;
        border: none;
        outline: none;
        margin-bottom: 1rem;
    }

    button {
        width: 100%;
        padding: .9rem;
        border: none;
        border-radius: 8px;
        background: #2563eb;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        transition: background .2s ease;
    }

    button:hover {
        background: #1d4ed8;
    }

    .hint {
        margin-top: 1rem;
        font-size: .8rem;
        color: #94a3b8;
        text-align: center;
    }
</style>
</head>

<body>

<div class="otp-card">
    <h2>Verify OTP</h2>
    <p>Enter the 6-digit OTP sent to your email</p>

    <form method="POST" action="/backend/auth/verify_otp.php" autocomplete="off">
        <input
            type="text"
            name="otp"
            maxlength="6"
            pattern="[0-9]{6}"
            inputmode="numeric"
            placeholder="••••••"
            required
        >

        <button type="submit">Verify</button>
    </form>

    <div class="hint">
        OTP valid for 5 minutes
    </div>
</div>

</body>
</html>