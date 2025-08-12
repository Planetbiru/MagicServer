<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use React\Http\HttpServer as ReactHttpServer;
use Psr\Http\Message\ServerRequestInterface;

// CONFIG
$HTTP_PORT = 8080;           // port untuk HTTP endpoint (bisa sama host)
$WS_PORT   = 8081;           // port untuk WebSocket (atau gunakan same port upgrade)
$AUTH_TOKEN = getenv('NOTIFY_TOKEN') ?: 'change_me_to_a_random_secret'; // ganti di env


class SessionManager {
    protected $sessionConfig;

    public function __construct(array $sessionConfig = []) {
        $this->sessionConfig = array_merge([
            'name'   => 'PHPSESSID',
            'handler'=> 'files', // files | redis
            'path'   => null,    // untuk handler files
            'redis'  => null,    // ['host'=>'127.0.0.1','port'=>6379]
        ], $sessionConfig);
    }

    public function getSessionFromRequest($httpRequest) {
        $sessionData = new \stdClass();
        $sessionData->session_id = null;
        $sessionData->data = null;
        $sessionData->cookies = new \stdClass();

        $cookies = $httpRequest->getHeader('Cookie');
        if (!empty($cookies)) {
            $cookieHeader = implode(';', $cookies);
            parse_str(str_replace('; ', '&', $cookieHeader), $parsed);

            foreach ($parsed as $key => $val) {
                $sessionData->cookies->{$key} = $val;
            }

            $sessionName = $this->sessionConfig['name'];
            if (isset($parsed[$sessionName])) {
                $sessionData->session_id = $parsed[$sessionName];
                $sessionVars = $this->readSessionData($parsed[$sessionName]);
                $sessionData->data = isset($sessionVars) ? $sessionVars : array();
            }
        }

        return $sessionData;
    }

    protected function readSessionData($sessionId) {
        if ($this->sessionConfig['handler'] === 'files') {
            return $this->readFromFiles($sessionId);
        } elseif ($this->sessionConfig['handler'] === 'redis') {
            return $this->readFromRedis($sessionId);
        }
        return [];
    }

    protected function readFromFiles($sessionId) {
        $path = $this->sessionConfig['path'] ?: ini_get('session.save_path');
        $file = rtrim($path, '/').'/sess_'.$sessionId;
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        return $this->decodeSessionData($data);
    }

    protected function readFromRedis($sessionId) {
        if (empty($this->sessionConfig['redis'])) 
        {
            return [];
        }
        $r = new Redis();
        $r->connect($this->sessionConfig['redis']['host'], $this->sessionConfig['redis']['port']);
        $data = $r->get($this->sessionConfig['name'] . '=' . $sessionId);
        return $this->decodeSessionData($data);
    }

    protected function decodeSessionData($session_data) {
    $return_data = [];
    $offset = 0;
    $len = strlen($session_data);

    while ($offset < $len) {
        // Pastikan ada tanda pemisah '|'
        $pos = strpos($session_data, '|', $offset);
        if ($pos === false) {
            break;
        }

        $varname = substr($session_data, $offset, $pos - $offset);
        $offset = $pos + 1;

        // Ambil serialized data dari offset ini
        $serialized_part = substr($session_data, $offset);
        $data = @unserialize($serialized_part);

        if ($data === false && $serialized_part !== 'b:0;') {
            // Data corrupt atau tidak dapat di-unserialize
            break;
        }

        $return_data[$varname] = $data;

        // Hitung panjang serialized sebenarnya
        $serialized_actual = serialize($data);
        $offset += strlen($serialized_actual);
    }

    return $return_data;
}

}


class BroadcastServer implements MessageComponentInterface {
    protected $clients;
    protected $connSession = [];
    protected $sessionManager;

    public function __construct(SessionManager $sessionManager) {
        $this->clients = new \SplObjectStorage;
        $this->sessionManager = $sessionManager;
        echo "BroadcastServer ready\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";

        if (isset($conn->httpRequest)) {
            $this->connSession[$conn->resourceId] = 
                $this->sessionManager->getSessionFromRequest($conn->httpRequest);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $from->send(json_encode(['type'=>'echo', 'payload'=>$msg]));
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->connSession[$conn->resourceId]);
        echo "Connection {$conn->resourceId} closed\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function broadcast($data) {
        $payload = json_encode($data);
        foreach ($this->clients as $client) {
            $sessionData = isset($this->connSession[$client->resourceId]) ? $this->connSession[$client->resourceId] : null;
            // contoh filter: kirim hanya ke user yang login
            if ($sessionData && isset($sessionData->data) && isset($sessionData->data['magicUsername'])) {
                // hanya kirim ke user yang memiliki session
                try {
                    $client->send($payload);
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            } 

        }
        echo "Broadcast: {$payload}\n";
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////

// create event loop
$loop = Loop::get();

// create WebSocket server (listen WS_PORT)
$sessionConfig = [
    'name'   => 'PHPSESSID',
    'handler'=> 'files', // atau 'redis'
    // 'path' => '/var/lib/php/sessions',
    // 'redis'=> ['host'=>'127.0.0.1','port'=>6379],
];
$sessionManager = new SessionManager($sessionConfig);

$broadcastServer = new BroadcastServer($sessionManager);
$wsServer = new Ratchet\Server\IoServer(
    new HttpServer(new WsServer($broadcastServer)),
    new SocketServer("0.0.0.0:{$WS_PORT}", [], $loop),
    $loop
);

// create a small React HTTP server for receiving notify requests
$http = new ReactHttpServer(function (ServerRequestInterface $req) use ($broadcastServer, $AUTH_TOKEN) {
    $path = $req->getUri()->getPath();
    if ($path !== '/notify') {
        return new React\Http\Message\Response(404, ['Content-Type'=>'application/json'], json_encode(['error'=>'not found']));
    }

    // Auth: simple Bearer token in Authorization header
    $auth = $req->getHeaderLine('Authorization'); // "Bearer token..."
    if (!preg_match('/Bearer\s+(.+)/', $auth, $m) || $m[1] !== $AUTH_TOKEN) {
        return new React\Http\Message\Response(401, ['Content-Type'=>'application/json'], json_encode(['error'=>'unauthorized']));
    }

    $body = $req->getBody()->getContents();
    $data = json_decode($body, true);
    if (!is_array($data) || !isset($data['message'])) {
        return new React\Http\Message\Response(400, ['Content-Type'=>'application/json'], json_encode(['error'=>'invalid payload, must be json with field message']));
    }

    // optional fields: 'type', 'meta', 'targets'...
    $event = [
        'type' => $data['type'] != null ? $data['type'] : 'notification',
        'message' => $data['message'],
        'meta' => $data['meta'] != null ? $data['meta'] : null,
        'sent_at' => gmdate('c'),
    ];

    // kirim ke semua client
    $broadcastServer->broadcast($event);

    return new React\Http\Message\Response(200, ['Content-Type'=>'application/json'], json_encode(['ok' => true]));
});

$socket = new SocketServer("0.0.0.0:{$HTTP_PORT}", [], $loop);
$http->listen($socket);

echo "HTTP notify endpoint listening on port {$HTTP_PORT}\n";
echo "WebSocket listening on port {$WS_PORT}\n";

// run loop
$loop->run();
