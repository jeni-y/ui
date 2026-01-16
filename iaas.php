<?php
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/middleware/auth_check.php';

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IaaS - CloudApp</title>
    <link rel="stylesheet" href="assets/css/iaas.css">
</head>
<body>

<header>
    <h1>One Hive Web Service</h1>
    <nav>
        <a href="login_index.php">Home</a>
        <a href="paas.php">PaaS</a>
        <a href="iaas.php">IaaS</a>
        <a href="#">VPN</a>
        <a href="index.php">Logout</a>
    </nav>
</header>

<section class="hero">
    <h2>Infrastructure as a Service (IaaS)</h2>
    <p>Flexible, scalable compute infrastructure with per-second billing and enterprise-grade performance.</p>
</section>

<div class="container">

    <div class="description">
        <h3 class="section-title">Per-Second Billing</h3>
        <p>
            Per-second billing removes the cost of unused compute time from your bill.
            Usage is billed in one-second increments, with a minimum of 60 seconds.
        </p>

        <p>Supported operating systems:</p>

        <div class="os-list">
            <div class="os-box">Amazon Linux</div>
            <div class="os-box">Windows</div>
            <div class="os-box">Red Hat Enterprise Linux</div>
            <div class="os-box" data-os="ubuntu">Ubuntu</div>
            <div class="os-box">Ubuntu Pro</div>
        </div>
    </div>

    <h3 class="section-title">Pricing</h3>
    <div class="pricing">
        <div class="price-card" data-modal="free">
            <h3>Free Tier</h3>
            <div class="price">Free</div>
            <p>1 vCPU · 1GB RAM · 10GB Storage</p>
            <strong>Click for details</strong>
        </div>

        <div class="price-card" data-modal="paid">
            <h3>Paid Tier</h3>
            <div class="price">$0.05 / hour</div>
            <p>Custom resources</p>
            <strong>Click for details</strong>
        </div>
    </div>

    <h3 class="section-title">Key Features</h3>
    <div class="features">
        <div class="feature">
            <h4>Scalable Compute</h4>
            <p>Launch and scale virtual machines on demand.</p>
        </div>
        <div class="feature">
            <h4>Secure Storage</h4>
            <p>Durable and encrypted block storage.</p>
        </div>
        <div class="feature">
            <h4>High Availability</h4>
            <p>Multiple zones for fault tolerance.</p>
        </div>
        <div class="feature">
            <h4>Flexible OS</h4>
            <p>Run Linux and Windows workloads.</p>
        </div>
    </div>

</div>

<footer>
    © 2025 CloudApp. All rights reserved.
</footer>

<!-- MODALS -->
<div class="modal-overlay" id="overlay"></div>

<div class="modal" id="free">
    <span class="close">&times;</span>
    <h2>Free Tier</h2>
    <ul>
        <li>1 vCPU</li>
        <li>1 GB RAM</li>
        <li>10 GB Storage</li>
        <li>Per-second billing</li>
        <li>No cost for learning & testing</li>
    </ul>
    <button data-action="start-free">Start Free</button>
</div>

<div class="modal" id="paid">
    <span class="close">&times;</span>
    <h2>Paid Tier</h2>
    <ul>
        <li>Custom vCPU & RAM</li>
        <li>Scalable storage</li>
        <li>Per-second billing</li>
        <li>Production workloads</li>
        <li>Enterprise support</li>
    </ul>
    <button>Upgrade</button>
</div>

<script src="assets/js/iaas.js"></script>
</body>
</html>
