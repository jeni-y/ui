<?php
require_once __DIR__. '/backend/bootstrap.php';

/* ==========================
   CONFIG
========================== */
$API_BASE = "http://127.0.0.1:8000";

/* ==========================
   LOAD VM FROM SESSION
========================== */
$vm = $_SESSION['vm'] ?? null;
$notice = '';
$ip = null;

/* ==========================
   CURL HELPER
========================== */
function api_call($url, $post = false) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);

    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
    }

    $response = curl_exec($ch);

    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ["error" => $err];
    }

    curl_close($ch);
    return json_decode($response, true);
}

/* ==========================
   ACTION HANDLER
========================== */
if ($vm && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'start' && $vm['status'] === 'stopped') {
        $res = api_call("$API_BASE/vm-start/{$vm['name']}", true);

        if (empty($res['error'])) {
            $vm['status'] = 'running';
            $vm['since'] = time();
            $notice = "Instance started";
        } else {
            $notice = "Failed to start instance";
        }
    }

    if ($_POST['action'] === 'stop' && $vm['status'] === 'running') {
        $res = api_call("$API_BASE/vm-stop/{$vm['name']}", true);

        if (empty($res['error'])) {
            $vm['status'] = 'stopped';
            $notice = "Instance stopping";
        } else {
            $notice = "Failed to stop instance";
        }
    }

    $_SESSION['vm'] = $vm;
}

/* ==========================
   FETCH VM IP (REAL TIME)
========================== */
if ($vm && $vm['status'] === 'running') {
    $res = api_call("$API_BASE/vm-ip/{$vm['name']}");
    if (!empty($res['ip'])) {
        $ip = $res['ip'];
    }
}

/* ==========================
   UPTIME HELPER
========================== */
function uptime($vm){
    return ($vm && $vm['status'] === 'running')
        ? gmdate("H:i:s", time() - $vm['since'])
        : '—';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Compute Instance</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,sans-serif;}
body{background:#f9f9f9;color:#333}
header{background:#fff;padding:18px 50px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.1)}
header h1{color:#0072ff;font-size:22px}
.container{max-width:1100px;margin:40px auto;padding:0 20px}
h2{color:#0072ff;margin-bottom:20px}
.notice{background:#eaf2ff;border-left:4px solid #0072ff;padding:12px 16px;border-radius:6px;margin-bottom:16px}
.empty{
    background:#fff;
    padding:30px;
    border-radius:12px;
    text-align:center;
    color:#6b7280;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
}
table{width:100%;background:#fff;border-radius:12px;border-collapse:separate;border-spacing:0;box-shadow:0 8px 20px rgba(0,0,0,.1)}
thead th{background:#f5f7fb;padding:14px 16px;font-size:13px;text-align:left}
tbody td{padding:16px;border-bottom:1px solid #eee}
tbody tr:hover{background:#f9fbff}
.status{display:flex;align-items:center;gap:8px;font-weight:600}
.dot{width:9px;height:9px;border-radius:50%}
.running .dot{background:#22c55e}
.stopped .dot{background:#ef4444}
.actions{display:flex;gap:16px;align-items:center}
.action-btn{border:none;background:none;cursor:pointer;font-size:20px}
.start{color:#16a34a}
.stop{color:#f97316}
.delete{color:#9ca3af;cursor:not-allowed}
.action-btn:hover:not(.delete){opacity:.7}
form{display:none}
</style>
</head>

<body>

<header>
    <h1>One Hive Web Service</h1>
    <div>admin</div>
</header>

<div class="container">
<h2>Compute Instance</h2>

<?php if ($notice): ?>
<div class="notice"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<?php if (!$vm): ?>

<div class="empty">
    🚫 No virtual machine launched yet
</div>

<?php else: ?>

<table>
<thead>
<tr>
    <th>Name</th>
    <th>Status</th>
    <th>Public IP</th>
    <th>Port</th>
    <th>Uptime</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<tr>
    <td><?= htmlspecialchars($vm['name']) ?></td>

    <td>
        <span class="status <?= $vm['status'] === 'running' ? 'running' : 'stopped' ?>">
            <span class="dot"></span>
            <?= ucfirst($vm['status']) ?>
        </span>
    </td>

    <td><?= htmlspecialchars($ip ?? '—') ?></td>
    <td><?= htmlspecialchars($vm['port']) ?></td>
    <td><?= uptime($vm) ?></td>

    <td>
        <div class="actions">
            <?php if ($vm['status'] === 'stopped'): ?>
                <button class="action-btn start"
                        onclick="this.disabled=true;doAction('start')"
                        title="Start">▶</button>
            <?php else: ?>
                <button class="action-btn stop"
                        onclick="this.disabled=true;doAction('stop')"
                        title="Stop">⏹</button>
            <?php endif; ?>
            <span class="action-btn delete" title="Delete disabled">🗑</span>
        </div>
    </td>
</tr>

</tbody>
</table>

<form id="actionForm" method="POST">
    <input type="hidden" name="action" id="actionInput">
</form>

<?php endif; ?>

</div>

<script>
function doAction(action){
    document.getElementById('actionInput').value = action;
    document.getElementById('actionForm').submit();
}
</script>

</body>
</html>