<?php
session_start();
require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/../../backend/middleware/auth_check.php';
if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

/* VM STATE */
$vm_status = $_SESSION['vm_status'] ?? 'Not Created';
$public_key = $_SESSION['public_key'] ?? '';

/* SSH KEY GENERATION */
if (isset($_POST['generate_key'])) {

    $algo = $_POST['algorithm'];

    if ($algo === 'rsa') {
        $config = [
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "private_key_bits" => 2048
        ];
    } else {
        $config = [
            "private_key_type" => OPENSSL_KEYTYPE_ED25519
        ];
    }

    $res = openssl_pkey_new($config);
    openssl_pkey_export($res, $privateKey);
    $details = openssl_pkey_get_details($res);

    $_SESSION['public_key'] = $details['key'];
    $public_key = $details['key'];

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=id_$algo");
    echo $privateKey;
    exit;
}

/* LAUNCH VM */
if (isset($_POST['launch_vm'])) {
    $_SESSION['vm_status'] = 'Running';
    $vm_status = 'Running';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IaaS VM Setup</title>

<style>
body{font-family:Segoe UI;background:#f4f6f9;margin:0;color:#333}
header{background:#fff;padding:20px 50px;box-shadow:0 2px 6px rgba(0,0,0,.1);display:flex;justify-content:space-between}
.container{max-width:950px;margin:40px auto}
.card{background:#fff;border-radius:10px;padding:30px;margin-bottom:25px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
h2,h3{margin-bottom:15px;color:#0073bb}
label{font-weight:600;display:block;margin:15px 0 6px}
input,select,textarea{width:100%;padding:12px;border-radius:6px;border:1px solid #ccc}
textarea{resize:vertical}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.box{background:#f8f9fb;border:1px solid #ddd;border-radius:8px;padding:20px;text-align:center}
button{background:#0073bb;color:#fff;border:none;padding:14px 26px;font-size:15px;border-radius:6px;cursor:pointer}
button:hover{background:#005fa3}
.status{font-weight:700;padding:10px;border-radius:6px}
.running{background:#e6ffed;color:#1a7f37}
.pending{background:#fff4e5;color:#8a5700}
footer{text-align:center;padding:20px;color:#777}
</style>
</head>

<body>

<header>
    <h2>CloudApp – IaaS</h2>
    <div>Welcome, <?php echo $_SESSION['username']; ?></div>
</header>

<div class="container">

<!-- VM CONFIG -->
<div class="card">
<h3>Virtual Machine Configuration</h3>

<form method="POST">
<label>VM Name</label>
<input type="text" required placeholder="ubuntu-prod-01">

<label>Region</label>
<select>
<option>US-East</option>
<option>EU-West</option>
<option>Asia-South</option>
</select>

<label>Operating System</label>
<select>
<option>Ubuntu 22.04 LTS</option>
</select>
</form>
</div>

<!-- RESOURCES -->
<div class="card">
<h3>Compute Resources (Free Tier)</h3>
<div class="grid">
<div class="box"><b>vCPU</b><br>1 Core</div>
<div class="box"><b>Memory</b><br>1 GB RAM</div>
<div class="box"><b>Storage</b><br>10 GB SSD</div>
</div>
</div>

<!-- SSH -->
<div class="card">
<h3>Access Configuration (SSH)</h3>

<form method="POST">
<label>Key Algorithm</label>
<select name="algorithm">
<option value="rsa">RSA (2048)</option>
<option value="ed25519">ED25519</option>
</select>

<label>Public Key</label>
<textarea rows="4" readonly><?php echo htmlspecialchars($public_key); ?></textarea>

<button name="generate_key">Generate & Download Private Key</button>
</form>
</div>

<!-- LAUNCH -->
<div class="card">
<h3>Instance State</h3>

<p>Status:
<span class="status <?php echo $vm_status === 'Running' ? 'running' : 'pending'; ?>">
<?php echo $vm_status; ?>
</span>
</p>

<form method="POST">
<button name="launch_vm">Launch Virtual Machine</button>
</form>
</div>

</div>

<footer>
© 2025 CloudApp – Infrastructure as a Service
</footer>

</body>
</html>