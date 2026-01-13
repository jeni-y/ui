<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class DeploymentStatusServer implements MessageComponentInterface {
    private $clients;
    private $pdo;

    public function __construct($pdo) {
        $this->clients = new \SplObjectStorage;
        $this->pdo = $pdo;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $query = parse_url($conn->httpRequest->getUri(), PHP_URL_QUERY);
        parse_str($query, $params);
        $conn->client = $params['client'] ?? '';
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        // Optional: allow client to request immediate status
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    // Poll GitHub Actions and broadcast updates
    public function broadcastStatus() {
        foreach ($this->clients as $client) {
            if (!$client->client) continue;
            $status = $this->fetchStatusFromGitHub($client->client);
            $client->send(json_encode($status));
        }
    }

    private function fetchStatusFromGitHub(string $clientName): array {
        // Fetch deployment info from DB
        $stmt = $this->pdo->prepare("SELECT * FROM deployments WHERE client_name = ?");
        $stmt->execute([$clientName]);
        $deploy = $stmt->fetch();
        if (!$deploy) return ["error"=>"Unknown client"];

        // Poll GitHub Actions API (simplified)
        $workflowUrl = "https://api.github.com/repos/Ajay003-j/Onehive/actions/runs"; // put your api url here
        $token = getenv('GITHUB_TOKEN');
        $ch = curl_init($workflowUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/vnd.github+json",
                "User-Agent: OneHive-WS"
            ]
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($res, true);

        // Filter runs for this client_name
        $run = null;
        foreach($json['workflow_runs'] ?? [] as $r) {
            if (strpos($r['head_commit']['message'] ?? '', $clientName) !== false) {
                $run = $r; break;
            }
        }
        if (!$run) return ["status"=>"pending"];

        // Map run status
        $statusMap = [
            'queued'=>'pending',
            'in_progress'=>'in-progress',
            'completed'=>'success'
        ];
        $status = $statusMap[$run['status']] ?? 'pending';

        return [
            "status" => $status,
            "ingress_url" => $deploy['app_domain'],
            "run_id" => $run['id']
        ];
    }
}

// Run WebSocket server
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new DeploymentStatusServer($pdo)
        )
    ),
    8080
);

$server->run();