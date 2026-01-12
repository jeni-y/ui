<?php
session_start();
require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/../../backend/middleware/auth_check.php';

/* ==========================
   INITIAL STATE
========================== */
$vm_status = $_SESSION['vm_status'] ?? 'Not Created';
$error = '';
$launch_result = null;

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

            $_SESSION['vm_name'] = $_POST['vm_name'];
            $_SESSION['region']  = $_POST['region'];
            $_SESSION['os']      = $_POST['os'];

            $SSH_KEYGEN = '/usr/bin/ssh-keygen';
            $COMMENT    = $_SESSION['vm_name'] ?? 'vm-key';

            $tmpDir = sys_get_temp_dir() . '/ssh_' . bin2hex(random_bytes(16));
            if (!mkdir($tmpDir, 0700, true)) {
                http_response_code(500);
                exit('Temp dir error');
            }

            $privatePath = $tmpDir . '/id_ed25519';
            $publicPath  = $privatePath . '.pub';

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

            $privateKey = file_get_contents($privatePath);
            $publicKey  = trim(file_get_contents($publicPath));

            $_SESSION['public_key'] = $publicKey;

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="id_ed25519"');
            header('Content-Length: ' . strlen($privateKey));
            header('Cache-Control: no-store');
            echo $privateKey;

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

    if (empty($_SESSION['vm_name']) || empty($_SESSION['region']) || empty($_SESSION['os'])) {
        $error = "VM configuration missing.";
    } elseif (empty($_SESSION['public_key'])) {
        $error = "Generate SSH key before launching the VM.";
    } else {

        $data = [
            "vm_name"   => $_SESSION['vm_name'],
            "disk_size" => "5G",
            "user_name" => $_SESSION['vm_name'],
            "ssh_key"   => $_SESSION['public_key'],
        ];

        $ch = curl_init("http://127.0.0.1:9000/create-vm");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $launch_result = 'fail';
        } else {
            $_SESSION['vm_status'] = 'Running';
            $launch_result = 'success';
        }
        curl_close($ch);
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
input,select{width:100%;padding:12px;border-radius:6px;border:1px solid #ccc}
button{background:#0073bb;color:#fff;border:none;padding:14px 26px;font-size:15px;border-radius:6px;cursor:pointer}
button:disabled{opacity:.6;cursor:not-allowed}
.status{font-weight:700;padding:10px;border-radius:6px}
.running{background:#e6ffed;color:#1a7f37}
.pending{background:#fff4e5;color:#8a5700}

.modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center}
.modal-box{background:#fff;padding:30px;border-radius:10px;text-align:center;min-width:300px}
.success{color:#1a7f37;font-weight:700}
.fail{color:#a40000;font-weight:700}
</style>
</head>

<body>

<header>
    <h2> IaaS (Ubuntu)</h2>
    <div>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
</header>

<div class="container">
<form method="POST">

<div class="card">
<h3>Virtual Machine Configuration</h3>

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

<input type="hidden" name="user_text" value="default-secure-text">

<?php if ($error): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<button name="generate_key_from_text" style="margin-top:20px;">
Generate & Download Private Key
</button>
</div>

<div class="card">
<h3>Instance State</h3>

<p>Status:
<span class="status <?php echo $vm_status === 'Running' ? 'running' : 'pending'; ?>">
<?php echo $vm_status; ?>
</span>
</p>

<button type="submit" name="launch_vm" disabled id="launchBtn">
Launch Virtual Machine
</button>
</div>

</form>
</div>

<div class="modal" id="launchModal">
    <div class="modal-box" id="modalText">Launching VM... ⏳</div>
</div>

<script>
const vmName = document.querySelector('input[name="vm_name"]');
const region = document.querySelector('select[name="region"]');
const os = document.querySelector('select[name="os"]');
const launchBtn = document.getElementById('launchBtn');
const modal = document.getElementById('launchModal');
const modalText = document.getElementById('modalText');

const hasSSHKey = <?php echo isset($_SESSION['public_key']) ? 'true' : 'false'; ?>;

function validateLaunch() {
    launchBtn.disabled = !(
        vmName.value.trim() &&
        region.value &&
        os.value &&
        hasSSHKey
    );
}

validateLaunch();
vmName.addEventListener('input', validateLaunch);
region.addEventListener('change', validateLaunch);
os.addEventListener('change', validateLaunch);

/* ✅ FIX: modal only for launch button */
launchBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
    modalText.innerHTML = 'Launching VM... ⏳';
});

<?php if ($launch_result): ?>
modal.style.display = 'flex';
modalText.innerHTML =
"<?php echo $launch_result === 'success'
? '<span class=\"success\">✅ VM Launched Successfully</span>'
: '<span class=\"fail\">❌ Failed to Launch VM</span>'; ?>";

setTimeout(() => {
    modal.style.display = 'none';
    window.location.href = window.location.pathname;
}, 3000);
<?php endif; ?>
</script>

</body>
</html>