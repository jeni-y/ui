<?php
declare(strict_types=1);
require_once __DIR__. '/backend/bootstrap.php';

// 🔐 Authorization required
require_once __DIR__ . '/backend/middleware/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Deploy on Your PaaS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="assets/css/user_docs.css">
</head>

<body>
<div class="container">

  <h1>🚀 Deploy Applications on Your PaaS</h1>
  <p>
    This guide explains how to deploy applications using
    <strong>GitHub Actions, Docker, and Kubernetes</strong>.
  </p>

  <div class="card">
    <h2>📁 Repository structure</h2>
    <p>Your GitHub repository must contain a root folder named <strong>app</strong> with the following files:</p>
    <pre>
your-repo/
└── app/
    ├── src/                # Your application source code
    ├── Dockerfile          # Container build instructions
    ├── nginx.conf          # Web server configuration
    ├── schema.sql          # Database schema (PostgreSQL)
    ├── helm/               # Helm charts for Kubernetes
    └── .github/workflows/  # GitHub Actions CI/CD pipeline
    </pre>
  </div>

  <div class="card">
    <h2>⚙️ Supported frameworks</h2>
    <ul>
      <li>PHP</li>
      <li>Node.js</li>
      <li>Python</li>
      <li>Go</li>
      <li>Java</li>
    </ul>
  </div>

  <div class="card">
    <h2>🗄️ Database schema example</h2>
    <p>Use <code>schema.sql</code> to define your PostgreSQL tables:</p>
    <pre>
-- schema.sql
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at TIMESTAMP NOT NULL
);
    </pre>
  </div>

  <div class="card">
    <h2>🌐 Nginx configuration example</h2>
    <p>Place <code>nginx.conf</code> in the <code>app/</code> folder:</p>
    <pre>
# nginx.conf
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php-fpm:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
    </pre>
  </div>

  <div class="card">
    <h2>🔄 CI/CD behavior</h2>
    <p>
      Once deployed, your application is continuously updated.
    </p>
    <ul>
      <li>Push to <code>main</code> branch</li>
      <li>GitHub Actions runs automatically</li>
      <li>Helm upgrades the deployment</li>
      <li>No downtime during updates</li>
    </ul>
  </div>

  <!-- 🔐 Deploy Button -->
  <a href="paas.php" class="btn">
    🚀 Go to Deployment Page
  </a>

  <div class="footer">
    Logged in as <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong><br>
    © 2026 Your PaaS
  </div>

</div>
</body>
</html>