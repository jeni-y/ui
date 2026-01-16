<!-- LOGOUT NAV LOADED -->
<header>
  <h1>CloudApp</h1>
  <nav>
    <a href="paas.php">PaaS</a>
    <a href="iaas.php">IaaS</a>
    <a href="#">VPN</a>
    <a href="/backend/logout.php">Log Out</a>

    <!-- Profile -->
    <div class="profile-wrapper">
    <button class="profile-btn" id="profileBtn">
    <?= strtoupper($username[0]) ?>
    </button>

    <div class="profile-menu" id="profileMenu">
    <a href="profile.php">View Profile</a>
    <a href="#">Manage Account</a>

    <div class="dark-row">
      Dark Mode
      <div class="toggle" id="darkToggle">
        <span></span>
      </div>
    </div>

    <hr>
    <a href="#">Resources</a>
    <hr>
    <a href="/backend/logout.php">Log Out</a>
     </div>
  </div>
</nav>
</header>