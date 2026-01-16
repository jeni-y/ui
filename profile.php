<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CloudApp - Profile</title>
  <link rel="stylesheet" href="assets/css/profile.css" />
</head>
<body>
  <nav>
    <div class="nav-brand">CloudApp</div>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="user_docs.php">PaaS</a>
      <a href="iaas.php">IaaS</a>
      <a href="#">VPN</a>
    </div>
  </nav>

  <main class="profile-container">
    <h1>User Profile</h1>
    <div class="profile-info">
      <div class="field"><span>Username:</span> username</div>
      <div class="field"><span>Email:</span> user@example.com</div>
      <div class="field"><span>Role:</span> Developer</div>
      <div class="field"><span>Joined:</span> January 2026</div>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>