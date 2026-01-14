<?php

require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/backend/middleware/auth_check.php';

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IaaS - CloudApp</title>

<style>
/* ===== GLOBAL ===== */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;}
body{background:#f9f9f9;color:#333;line-height:1.6;}
a{text-decoration:none;color:inherit}

/* ===== HEADER ===== */
header{
    background:#fff;
    padding:20px 50px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
header h1{font-size:24px;font-weight:700;color:#0072ff;}
nav a{margin-left:20px;font-weight:500;}
nav a:hover{color:#0072ff}

/* ===== HERO ===== */
.hero{
    background:linear-gradient(180deg,#00c6ff,#0072ff);
    color:#fff;
    text-align:center;
    padding:90px 20px;
}
.hero h2{font-size:46px;margin-bottom:15px;}
.hero p{font-size:18px;max-width:800px;margin:0 auto;}

/* ===== CONTENT ===== */
.container{max-width:1100px;margin:50px auto;padding:0 20px;}
.section-title{font-size:26px;color:#0072ff;margin-bottom:20px;}

/* ===== DESCRIPTION ===== */
.description p{margin-bottom:15px;color:#555;}

/* ===== OS BOXES ===== */
.os-list{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:10px;
}
.os-box{
    padding:10px 16px;
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:14px;
    cursor:pointer;
    transition:all 0.2s ease;
}
.os-box:hover,
.os-box.active{
    background:#0072ff;
    color:#fff;
    border-color:#0072ff;
}

/* ===== PRICING ===== */
.pricing{
    display:flex;
    gap:25px;
    flex-wrap:wrap;
    margin-top:20px;
}
.price-card{
    background:#fff;
    border-radius:12px;
    padding:30px;
    width:300px;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    transition:all 0.2s ease;
}
.price-card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(0,0,0,0.15);
}
.price-card h3{color:#0072ff;margin-bottom:10px;}
.price{font-size:26px;font-weight:bold;margin-bottom:10px;}

/* ===== FEATURES ===== */
.features{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
    margin-top:30px;
}
.feature{
    background:#fff;
    padding:25px;
    width:260px;
    border-radius:12px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
}
.feature h4{color:#0072ff;margin-bottom:8px;}

/* ===== MODAL ===== */
.modal-overlay{
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.5);
    display:none;
    z-index:999;
}
.modal{
    position:fixed;
    top:50%;left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    width:380px;
    padding:30px;
    border-radius:12px;
    box-shadow:0 20px 40px rgba(0,0,0,0.3);
    display:none;
    z-index:1000;
}
.modal h2{color:#0072ff;margin-bottom:10px;}
.modal ul{list-style:none;margin:15px 0;}
.modal ul li{padding:6px 0;border-bottom:1px solid #eee;}
.modal button{
    width:100%;
    padding:12px;
    background:#0072ff;
    color:#fff;
    border:none;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
}
.close{
    position:absolute;
    top:12px;
    right:15px;
    font-size:22px;
    cursor:pointer;
    color:#888;
}
.close:hover{color:#000}

/* ===== FOOTER ===== */
footer{
    margin-top:60px;
    padding:25px;
    background:#f1f1f1;
    text-align:center;
    font-size:14px;
}

/* ===== RESPONSIVE ===== */
@media(max-width:768px){
    header{flex-direction:column;align-items:flex-start;}
    nav{margin-top:10px;}
    .pricing,.features{justify-content:center;}
}
</style>
</head>
<body>

<header>
    <h1>One Hive Web Service</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="paas.php">PaaS</a>
        <a href="iaas.php">IaaS</a>
        <a href="#">VPN</a>
        <a href="login.php">Login</a>

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
            Per-second billing removes the cost of unused compute time from your bill. This particularly helps workloads
            that run over irregular time periods. Usage is billed in one-second increments, with a minimum of 60 seconds.
        </p>
        <p>Supported operating systems:</p>

        <div class="os-list">
            <div class="os-box">Amazon Linux</div>
            <div class="os-box">Windows</div>
            <div class="os-box">Red Hat Enterprise Linux</div>
            <div class="os-box" onclick="goToUbuntu()">Ubuntu</div>
            <div class="os-box">Ubuntu</div>
            <div class="os-box">Ubuntu Pro</div>
        </div>
    </div>

    <h3 class="section-title">Pricing</h3>
    <div class="pricing">
        <div class="price-card" onclick="openModal('free')">
            <h3>Free Tier</h3>
            <div class="price">Free</div>
            <p>1 vCPU · 1GB RAM · 10GB Storage</p>
            <strong>Click for details</strong>
        </div>

        <div class="price-card" onclick="openModal('paid')">
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

<!-- ===== MODALS ===== -->
<div class="modal-overlay" id="overlay" onclick="closeModal()"></div>

<div class="modal" id="free">
    <span class="close" onclick="closeModal()">×</span>
    <h2>Free Tier</h2>
    <ul>
        <li>1 vCPU</li>
        <li>1 GB RAM</li>
        <li>10 GB Storage</li>
        <li>Per-second billing</li>
        <li>No cost for learning & testing</li>
    </ul>
    <button onclick="window.location.href='vm.php'">Start Free</button>
</div>

<div class="modal" id="paid">
    <span class="close" onclick="closeModal()">×</span>
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

<script>
function openModal(id){
    document.getElementById('overlay').style.display='block';
    document.getElementById(id).style.display='block';
}
function closeModal(){
    document.getElementById('overlay').style.display='none';
    document.getElementById('free').style.display='none';
    document.getElementById('paid').style.display='none';
}

function goToUbuntu(){
    window.location.href = 'create_vm_ubuntu.php';
}
document.querySelectorAll('.os-box').forEach(box=>{
    box.addEventListener('click',(e)=>{
        if(box.textContent.trim() === 'Ubuntu') return;
        document.querySelectorAll('.os-box').forEach(b=>b.classList.remove('active'));
        box.classList.add('active');
    });
});

</script>

</body>
</html>
