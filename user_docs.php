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

  <style>
    body {
      font-family: Inter, Arial, sans-serif;
      background: #0f172a;
      color: #e5e7eb;
      margin: 0;
    }

    .container {
      max-width: 900px;
      margin: auto;
      padding: 40px 20px;
    }

    h1, h2 {
      color: #f8fafc;
    }

    p {
      line-height: 1.7;
      color: #cbd5f5;
    }

    .card {
      background: #020617;
      border-radius: 14px;
      padding: 25px;
      margin: 25px 0;
      border: 1px solid #1e293b;
    }

    pre {
      background: #020617;
      color: #22c55e;
      padding: 14px;
      border-radius: 8px;
      overflow-x: auto;
    }

    .btn {
      display: inline-block;
      padding: 14px 28px;
      background: linear-gradient(135deg, #6366f1, #22c55e);
      color: #020617;
      font-weight: bold;
      border-radius: 10px;
      text-decoration: none;
      margin-top: 20px;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .footer {
      text-align: center;
      margin-top: 60px;
      font-size: 14px;
      color: #64748b;
    }
  </style>
</head>

<body>

<div class="container">

  <h1>🚀 Deploy Applications on Your PaaS</h1>
  <p>
    This guide explains how to deploy applications using
    <strong>GitHub Actions, Docker, and Kubernetes</strong>.
  </p>

  <div class="card">
    <h2>📁 Repository Structure</h2>
    <p>Your GitHub repository must contain a root folder named <strong>app</strong>.</p>
    <pre>
your-repo/
└── app/
    </pre>
  </div>

  <div class="card">
    <h2>⚙️ Supported Frameworks</h2>
    <ul>
      <li>PHP</li>
      <li>Node.js</li>
      <li>Python</li>
      <li>Go</li>
      <li>Java</li>
    </ul>
  </div>

  <div class="card">
    <h2>🗄️ Database Support</h2>
    <p>
       <strong>PostgreSQL only</strong> as a managed database.
      Credentials are injected securely via environment variables.
    </p>
  </div>

  <div class="card">
    <h2>🔄 CI/CD Behavior</h2>
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