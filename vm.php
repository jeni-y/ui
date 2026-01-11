<?php
session_start();
require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/../../backend/middleware/auth_check.php';
/* ==========================
   INITIAL STATE
========================== */
$vm_status = $_SESSION['vm_status'] ?? 'Not Created';
$error = '';

/* ==========================
   GENERATE SSH KEY
========================== */
if (isset($_POST['generate_key_from_text'])) {

    if (
        empty($_POST['vm_name']) ||
        empty($_POST['region']) ||
        empty($_POST['os'])
    ) {
        $error = "Please fill VM Name, Region and OS before generating SSH key.";
    } else {

        $user_text = trim($_POST['user_text']);

        if (strlen($user_text) < 6) {
            $error = "Text must be at least 6 characters.";
        } else {

            // ✅ Save VM config to SESSION
            $_SESSION['vm_name'] = $_POST['vm_name'];
            $_SESSION['region']  = $_POST['region'];
            $_SESSION['os']      = $_POST['os'];

            // 🔐 Generate SSH Key


            /* ========= CONFIG ========= */
            $SSH_KEYGEN = '/usr/bin/ssh-keygen';   // fixed path (important)
            $COMMENT    = $_SESSION['vm_name'] ?? 'vm-key';

            /* ========= TEMP DIR ========= */
            $tmpDir = sys_get_temp_dir() . '/ssh_' . bin2hex(random_bytes(16));
            if (!mkdir($tmpDir, 0700, true)) {
                http_response_code(500);
                exit('Temp dir error');
            }

            $privatePath = $tmpDir . '/id_ed25519';
            $publicPath  = $privatePath . '.pub';

            /* ========= GENERATE KEY ========= */
            $cmd = sprintf(
                '%s -t ed25519 -N "" -C %s -f %s',
                $SSH_KEYGEN,
                escapeshellarg($COMMENT),
                escapeshellarg($privatePath)
            );

            exec($cmd, $out, $code);
            if ($code !== 0 || !file_exists($privatePath)) {
                http_response_code(500);
                exit('SSH key generation failed');
            }

            /* ========= READ KEYS ========= */
            $privateKey = file_get_contents($privatePath);
            $publicKey  = trim(file_get_contents($publicPath));

            /* ========= STORE PUBLIC KEY ========= */
            $_SESSION['public_key'] = $publicKey;     // or DB / file

            /* ========= SEND PRIVATE KEY ========= */
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="id_ed25519"');
            header('Content-Length: ' . strlen($privateKey));
            header('Cache-Control: no-store');
            echo $privateKey;

            /* ========= CLEANUP ========= */
            unlink($privatePath);
            unlink($publicPath);
            rmdir($tmpDir);
            exit;


           
        }
    }
}

/* ==========================
   LAUNCH VM
========================== */
if (isset($_POST['launch_vm'])) {

    if (
        empty($_SESSION['vm_name']) ||
        empty($_SESSION['region']) ||
        empty($_SESSION['os'])
    ) {
        $error = "VM configuration missing.";
    }
    elseif (empty($_SESSION['public_key'])) {
        $error = "Generate SSH key before launching the VM.";
    } else {

        // ✅ FINAL VM DATA AVAILABLE HERE
        #$vm_name = $_SESSION['vm_name'];
        #$region  = $_SESSION['region'];
        #$os      = $_SESSION['os'];
        #$disk_size = "5G";
        #$user_name = $_SESSION['vm_name'];
        #$ssh_key = $_SESSION['public_key'];

        $data = [
            "vm_name"   => $_SESSION['vm_name'],        
            "disk_size" => "5G",                        
            "user_name" => $_SESSION['vm_name'],        
            "ssh_key"   => $_SESSION['public_key'],    
            
        ];

                // Initialize cURL
        $ch = curl_init("http://127.0.0.1:9000/create-vm");

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true
        ]);

        // 👉 Save to DB / Provision VM here
        $response = curl_exec($ch);
        if ($response === false) {
            $error = 'cURL error: ' . curl_error($ch);
        } else {
            $_SESSION['vm_status'] = 'Running';
        }
        curl_close($ch);

        #$_SESSION['vm_status'] = 'Running';
        #$_SESSION['launched_at'] = date('Y-m-d H:i:s');
        #$vm_status = 'Running';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Ubuntu VM</title>

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
.error{background:#ffecec;color:#a40000;padding:10px;border-radius:6px}
footer{text-align:center;padding:20px;color:#777}
</style>
</head>

<body>

<header>
    <h2>komba jenitta – IaaS (Ubuntu)</h2>
    <div>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
</header>

<div class="container">

<!-- VM CONFIG + SSH -->
<div class="card">
<h3>Virtual Machine Configuration</h3>

<form method="POST">

<label>VM Name</label>
<input type="text" name="vm_name" required>

<label>Region</label>
<select name="region" required>
<option value="">Select Region</option>
<option>US-East</option>
</select>

<label>Operating System</label>
<select name="os" required>
<option value="">Select OS</option>
<option>Ubuntu 22.04 LTS</option>
</select>

<h3 style="margin-top:30px">SSH Access</h3>

<?php if ($error): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<label>Enter Any Text / Passphrase</label>
<textarea name="user_text" rows="4" required></textarea>

<button name="generate_key_from_text">
Generate & Download Private Key
</button>

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
