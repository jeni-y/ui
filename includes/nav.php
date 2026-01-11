
<header>
  <h1>CloudApp</h1>
  <nav>

    <a href="paas.php">PaaS</a>
    <a href="iaas.php">IaaS</a>
    <a href="#">VPN</a>
    <a href="login.php">Login</a>

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
        <a href="login.php">Login</a>
      </div>
    </div>
  </nav>
</header>