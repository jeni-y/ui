<?php
require_once __DIR__ . '/backend/bootstrap.php';

if (!isset($_SESSION['username'])) {
    $user = [
    "username" => 'username',
    "email"    => "user@example.com",
    "role"     => "Developer",
    "joined"   => "January 2026"
];
}else{

$user = [
    "username" => $_SESSION['username'],
    "email"    => "user@example.com",
    "role"     => "Developer",
    "joined"   => "January 2026"
];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - CloudApp</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>


<section class="profile">
  <h2>User Profile</h2>
  <div class="profile-info">
    <div><span>Username:</span><span><?= htmlspecialchars($user['username']) ?></span></div>
    <div><span>Email:</span><span><?= htmlspecialchars($user['email']) ?></span></div>
    <div><span>Role:</span><span><?= htmlspecialchars($user['role']) ?></span></div>
    <div><span>Joined:</span><span><?= htmlspecialchars($user['joined']) ?></span></div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/scripts.js"></script>
</body>
</html>