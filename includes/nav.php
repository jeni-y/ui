<?php

$username = $_SESSION['username'] ?? null;
?>

<header>
  <h1>CloudApp</h1>

  <nav>
    <a href="user_docs.php">PaaS</a>
    <a href="iaas.php">IaaS</a>
    <a href="#">VPN</a>
    <?php if (!$username): ?>
      <a href="login.php">login</a>
    <?php else: ?>
      <a href="logout.php">login</a>
      <?php endif; ?>

      <div class="profile-wrapper">
        <button class="profile-btn" type="button">
          <?php if (!$username): ?>
            <?= strtoupper(htmlspecialchars('p')) ?>
          <?php else: ?>
            <?= strtoupper(htmlspecialchars($username[0])) ?>
            <?php endif; ?>
        </button>

        <div class="profile-menu" id="profileMenu">
          <a href="profile.php">View Profile</a>
          <a href="#">Manage Account</a>
          <a href="dashboard.php">Dashboard</a>
          <hr>
          <?php if ($username): ?>
          <a href="logout.php">Log Out</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php">Log In</a>
    <?php endif; ?>
  </nav>
</header>