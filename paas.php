<?php
require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/../../backend/middleware/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Deploy App</title>
  <link rel="stylesheet" href="/assets/css/paas.css">
</head>
<body>
  <div class="cloud cloud1"></div>
  <div class="cloud cloud2"></div>
  <div class="cloud cloud3"></div>

  <div class="wrapper">
    <h2 class="title">Deploy Your Application</h2>
    <div class="form-card">
      <form id="deployForm" class="form-grid" autocomplete="off">

        <!-- App Name -->
        <div class="form-group">
          <label for="client_name">App Name</label>
          <input type="text" id="client_name" name="client_name" required
                 pattern="[a-z0-9\-]+" title="Lowercase letters, numbers, dash only">
        </div>

        <!-- Framework -->
        <div class="form-group">
          <label for="framework">Framework</label>
          <select id="framework" name="framework" required>
            <option value="php">PHP</option>
            <option value="node">Node.js</option>
            <option value="python">Python</option>
            <option value="go">Go</option>
            <option value="java">Java</option>
          </select>
        </div>

        <!-- App Domain -->
        <div class="form-group">
          <label for="app_domain">App Domain</label>
          <input type="text" id="app_domain" name="app_domain" placeholder="example.com" required>
        </div>

        <!-- GitHub Repository -->
        <div class="form-group">
          <label for="repo_url">GitHub Repository</label>
          <input type="text" id="repo_url" name="repo_url" placeholder="owner/repo" required>
        </div>

        <!-- Branch -->
        <div class="form-group">
          <label for="repo_branch">Branch</label>
          <input type="text" id="repo_branch" name="repo_branch" value="main">
        </div>

        <!-- Private Repo Yes/No -->
        <div class="form-group small-toggle">
          <label>Private Repository?</label>
          <div class="toggle-group">
            <input type="radio" id="private_no" name="is_private_repo" value="no" checked>
            <label for="private_no">No</label>

            <input type="radio" id="private_yes" name="is_private_repo" value="yes">
            <label for="private_yes">Yes</label>
          </div>
        </div>

        <!-- GitHub Token (hidden until Yes is selected) -->
        <div class="form-group" id="token_field" style="display:none;">
          <label for="client_github_token">GitHub Personal Access Token</label>
          <input type="password" id="client_github_token" name="client_github_token"
                 placeholder="ghp_xxx..." autocomplete="off">
        </div>

        <!-- Database Options (hidden until Yes is selected) -->
        <div class="form-group" id="database_options" style="display:none;">
          <label for="db_type">Database Type</label>
          <select id="db_type" name="db_type">
            <option value="postgres">PostgreSQL</option>
          </select>
        </div>

        <!-- Deploy Button -->
        <button type="submit" class="btn">Deploy</button>
      </form>

      <div id="result" class="result"></div>
    </div>
  </div>

  <!-- ✅ External JS file -->
  <script src="/assets/js/paas.js"></script>
</body>
</html>