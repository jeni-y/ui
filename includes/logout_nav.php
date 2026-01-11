
<header>
  <h1>CloudApp</h1>
  <nav>
    <a href="user_docs.php">PaaS</a>
    <a href="iaas.php">IaaS</a>
    <a href="#">VPN</a>
    <a href="index.php">Log Out</a>

    <!-- Profile -->
    <div class="profile-wrapper">
      <button class="profile-btn" onclick="toggleProfile()">
        <?= strtoupper($username[0]) ?>
      </button>
      <div class="profile-menu" id="profileMenu">
        <a href="profile.php">View Profile</a>
        <a href="#">Manage Account</a>
        <div>
          Dark Mode
          <div class="toggle" id="darkToggle" onclick="toggleDarkMode(event)">
            <span></span>
          </div>
        </div>
        <hr>
        <a href="#">Resources</a>
        <hr>
        <a href="index.php">Log Out</a>
      </div>
    </div>
  </nav>
</header>