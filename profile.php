<?php

require_once __DIR__. '/backend/bootstrap.php';
if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Example: fetch user details from database
// (replace with your actual DB connection and query)
$user = [
    "username" => $_SESSION['username'],
    "email"    => "user@example.com",
    "role"     => "Developer",
    "joined"   => "January 2025"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - CloudApp</title>
<style>
/* Global */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI', Tahoma, sans-serif;}
body{background:#f9f9f9;color:#333;line-height:1.6;}
a{text-decoration:none;color:#0072ff;}
a:hover{text-decoration:underline;}

/* Header */
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 50px;
    background:#fff;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
header h1{font-size:24px;font-weight:700;}
nav a{margin-left:20px;font-weight:500;color:#333;}
nav a:hover{color:#0072ff;}

/* Profile Section */
.profile{
    max-width:800px;
    margin:50px auto;
    background:#fff;
    padding:40px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
.profile h2{
    font-size:28px;
    margin-bottom:20px;
    color:#0072ff;
    text-align:center;
}
.profile-info{
    display:flex;
    flex-direction:column;
    gap:15px;
}
.profile-info div{
    display:flex;
    justify-content:space-between;
    padding:10px 15px;
    background:#f1f1f1;
    border-radius:8px;
}
.profile-info div span:first-child{
    font-weight:600;
    color:#333;
}
.profile-info div span:last-child{
    color:#555;
}

/* Footer */
footer{
    text-align:center;
    padding:30px;
    background:#f1f1f1;
    color:#333;
    font-size:14px;
}
</style>
</head>
<body>

<header>
    <h1>CloudApp</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="paas.php">PaaS</a>
        <a href="iaas.php">IaaS</a>
        <a href="#">VPN</a>
        <a href="index.php">Logout</a>
    </nav>
</header>

<section class="profile">
    <h2>User Profile</h2>
    <div class="profile-info">
        <div><span>Username:</span><span><?php echo $user['username']; ?></span></div>
        <div><span>Email:</span><span><?php echo $user['email']; ?></span></div>
        <div><span>Role:</span><span><?php echo $user['role']; ?></span></div>
        <div><span>Joined:</span><span><?php echo $user['joined']; ?></span></div>
    </div>
</section>

<footer>
    &copy; 2025 CloudApp. All rights reserved.
</footer>

</body>
</html>