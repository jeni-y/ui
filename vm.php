<?php

require_once __DIR__. '/backend/bootstrap.php';
require_once __DIR__ . '/backend/middleware/auth_check.php';

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
<link rel="stylesheet" href="assets/css/vm.css">
</head>
<body>

<header>
    <h2>IaaS (Ubuntu)</h2>
    <div>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
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
<option>Ubuntu 22.04 LTS</option>
</select>

<?php if ($error): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<button name="generate_key_from_text">Generate & Download Key</button>
</div>

<div class="card">
<h3>Instance State</h3>

<p>Status:
<span class="status <?= $vm_status === 'Running' ? 'running' : 'pending' ?>">
<?= $vm_status ?>
</span>
</p>

<button type="submit" name="launch_vm" id="launchBtn">Launch Virtual Machine</button>
</div>

</form>
</div>

<div class="modal" id="launchModal">
    <div class="modal-box" id="modalText">Launching VM...</div>
</div>

<script>
window.VM_DATA = {
    hasKey: <?= isset($_SESSION['public_key']) ? 'true' : 'false' ?>,
    launchResult: <?= json_encode($launch_result) ?>
};
</script>
<script src="assets/js/vm.js"></script>

</body>
</html>