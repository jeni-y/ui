<?php

require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__. '/backend/middleware/auth_check.php';

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
<link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>

<header>
    <h1>CloudApp</h1>
    <nav>
        <a href="login_index.php">Home</a>
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