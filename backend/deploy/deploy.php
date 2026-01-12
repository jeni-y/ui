<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../config/db.php'; // PostgreSQL connection
require_once __DIR__ . '/../middleware/auth_check.php';


// ------------------------
// CSRF check
// ------------------------
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid CSRF token"]);
    exit;
}

// ------------------------
// Input sanitization
// ------------------------
$clientName  = strtolower(trim($_POST['client_name'] ?? ''));
$framework   = strtolower(trim($_POST['framework'] ?? ''));
$appDomain   = strtolower(trim($_POST['app_domain'] ?? ''));
$repoUrl     = trim($_POST['repo_url'] ?? '');
$repoBranch  = trim($_POST['repo_branch'] ?? 'main');
$isPrivate   = ($_POST['is_private_repo'] ?? 'no') === 'yes';
$clientToken = trim($_POST['client_github_token'] ?? '');

// ------------------------
// Input validation
// ------------------------
if (!preg_match('/^[a-z0-9-]{3,30}$/', $clientName)) {
    echo json_encode(["status" => "error", "message" => "Invalid client name"]);
    exit;
}

$allowedFrameworks = ['php','node','python','go','java'];
if (!in_array($framework, $allowedFrameworks, true)) {
    echo json_encode(["status" => "error", "message" => "Invalid framework"]);
    exit;
}

if (!filter_var('http://' . $appDomain, FILTER_VALIDATE_URL)) {
    echo json_encode(["status" => "error", "message" => "Invalid domain"]);
    exit;
}

if (!filter_var($repoUrl, FILTER_VALIDATE_URL)) {
    echo json_encode(["status" => "error", "message" => "Invalid repo URL"]);
    exit;
}

if ($isPrivate && empty($clientToken)) {
    echo json_encode(["status" => "error", "message" => "GitHub token required"]);
    exit;
}

// ------------------------
// Check uniqueness in DB
// ------------------------
$stmt = $pdo->prepare("SELECT id FROM deployments WHERE client_name = ?");
$stmt->execute([$clientName]);
if ($stmt->fetch()) {
    echo json_encode(["status"=>"error", "message"=>"app name already exists"]);
    exit;
}

// ------------------------
// Insert deployment
// ------------------------
$insert = $pdo->prepare(
    "INSERT INTO deployments 
     (client_name, framework, app_domain, repo_url, repo_branch, status)
     VALUES (?,?,?,?,?,?,?) RETURNING id"
);
$insert->execute([$clientName,$framework,$appDomain,$repoUrl,$repoBranch,'pending']);
$deploymentId = (int)$insert->fetchColumn();

// ------------------------
// Trigger GitHub workflow
// ------------------------
$serverToken = getenv('GITHUB_TOKEN');

$payload = json_encode([
    "ref" => "main",
    "inputs" => [
        "client_name" => $clientName,
        "framework" => $framework,
        "app_domain" => $appDomain,
        "repo_url" => $repoUrl,
        "repo_branch" => $repoBranch,
        "client_github_token" => $isPrivate ? $clientToken : null
    ]
], JSON_THROW_ON_ERROR);

$ch = curl_init("https://api.github.com/repos/Ajay003-j/Onehive/actions/workflows/main.yml/dispatches");//put your github api url here
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $serverToken",
        "Accept: application/vnd.github+json",
        "Content-Type: application/json",
        "User-Agent: OneHive-Deploy"
    ],
    CURLOPT_POSTFIELDS => $payload
]);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!in_array($httpCode, [201, 204], true)) {
    echo json_encode(["status"=>"error","message"=>"Workflow trigger failed"]);
    exit;
}

// ------------------------
// Success
// ------------------------
echo json_encode([
    "status" => "success",
    "message" => "Deployment started",
    "status_page" => "/status.php?client=" . urlencode($clientName)
]);
exit;