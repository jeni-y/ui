<?php
declare(strict_types=1);
require_once __DIR__ . '/backend/bootstrap.php';

require_once __DIR__ . '/bootstrap.php';

echo '<pre>';
var_dump($_SESSION);
exit;
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
<link rel="stylesheet" href="assets/css/otp.css">
</head>

<body>

<div class="otp-card">
    <h2>Verify OTP</h2>
    <p>Enter the 6-digit OTP sent to your email</p>

    <!-- OTP input form -->
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

    <!-- Resend OTP form -->
    <form method="POST" action="/backend/auth/resend_otp.php" style="margin-top:1em;">
        <button type="submit">Resend OTP</button>
    </form>
</div>

</body>
</html>