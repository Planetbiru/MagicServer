<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Spicy.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\HttpServer as ReactHttpServer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SessionManager
 * Manages session-related operations, including reading session data from different storage handlers.
 */
class SessionManager {
    /**
     * @var array $sessionConfig The session configuration settings.
     */
    protected $sessionConfig;

    /**
     * SessionManager constructor.
     *
     * @param array $sessionConfig An optional array to override default session configuration.
     */
    public function __construct(array $sessionConfig = []) {
        // Merge the provided configuration with default values.
        $this->sessionConfig = array_merge([
            'name' => 'MAGICAPP',
            'saveHandler'=> 'files',
            'savePath' => null,
            'maxLifeTime' => 86400,
            'savePath' => '',
            'cookiePath' => '/',
            'cookieDomain' => '',
            'cookieSecure' => false,
            'cookieHttpOnly' => false,
            'cookieSameSite' => 'Strict',
        ], $sessionConfig);
    }

    /**
     * Retrieves session data from an HTTP request.
     *
     * @param object $httpRequest The HTTP request object.
     * @return \stdClass An object containing session ID, data, and cookies.
     */
    public function getSessionFromRequest($httpRequest) {
        // Create a new object to store session data.
        $sessionData = new \stdClass();
        $sessionData->session_id = null;
        $sessionData->data = null;
        $sessionData->cookies = new \stdClass();

        // Get the 'Cookie' header from the request.
        $cookies = $httpRequest->getHeader('Cookie');
        if (!empty($cookies)) {
            // Join cookie header array into a single string and parse it.
            $cookieHeader = implode(';', $cookies);
            parse_str(str_replace('; ', '&', $cookieHeader), $parsed);

            // Store each cookie in the sessionData object.
            foreach ($parsed as $key => $val) {
                $sessionData->cookies->{$key} = $val;
            }

            // Check if the session cookie exists in the parsed cookies.
            $sessionName = $this->sessionConfig['name'];
            if (isset($parsed[$sessionName])) {
                // Set the session ID and read the corresponding session data.
                $sessionData->session_id = $parsed[$sessionName];
                $sessionVars = $this->readSessionData($parsed[$sessionName]);
                $sessionData->data = isset($sessionVars) ? $sessionVars : array();
            }
        }

        return $sessionData;
    }

    /**
     * Reads session data based on the configured handler.
     *
     * @param string $sessionId The ID of the session to read.
     * @return array The decoded session data.
     */
    protected function readSessionData($sessionId) {
        // Check which session handler is configured and read data accordingly.
        if ($this->sessionConfig['handler'] === 'files') {
            return $this->readFromFiles($sessionId);
        } elseif ($this->sessionConfig['handler'] === 'redis') {
            return $this->readFromRedis($sessionId);
        }
        return [];
    }

    /**
     * Reads session data from files.
     *
     * @param string $sessionId The ID of the session to read.
     * @return array The decoded session data.
     */
    protected function readFromFiles($sessionId) {
        // Get the session save path from the config or PHP ini.
        $path = $this->sessionConfig['path'] ?: ini_get('session.save_path');
        // Construct the full path to the session file.
        $file = rtrim($path, '/') . '/sess_' . $sessionId;
        
        // Check if the file exists before attempting to read.
        if (!file_exists($file)) {
            return [];
        }
        
        // Read the content of the session file.
        $data = file_get_contents($file);
        // Decode the data and return it.
        return $this->decodeSessionData($data);
    }

    /**
     * Reads session data from Redis.
     *
     * @param string $sessionId The ID of the session to read.
     * @return array The decoded session data.
     */
    protected function readFromRedis($sessionId) {
        // Check if Redis configuration is set.
        if (empty($this->sessionConfig['redis'])) 
        {
            return [];
        }
        
        // Connect to Redis and get the session data.
        $redis = new Redis();
        $redis->connect($this->sessionConfig['redis']['host'], $this->sessionConfig['redis']['port']);
        $data = $redis->get($this->sessionConfig['name'] . '=' . $sessionId);
        
        // Decode the data and return it.
        return $this->decodeSessionData($data);
    }

    /**
     * Decodes serialized session data from a string.
     *
     * This method handles the custom serialization format used by PHP's session handler.
     *
     * @param string $sessionData The serialized session data string.
     * @return array The decoded session data.
     */
    protected function decodeSessionData($sessionData) {
        $returnData = [];
        $offset = 0;
        $len = strlen($sessionData);

        // Loop through the data to parse each key-value pair.
        while ($offset < $len) {
            // Find the position of the '|' delimiter.
            $pos = strpos($sessionData, '|', $offset);
            if ($pos === false) {
                break;
            }

            // Extract the variable name.
            $varname = substr($sessionData, $offset, $pos - $offset);
            $offset = $pos + 1;

            // Get the serialized part from the current offset.
            $serializedPart = substr($sessionData, $offset);
            $data = @unserialize($serializedPart);

            // Handle unserialization errors.
            if ($data === false && $serializedPart !== 'b:0;') {
                // Data corrupt or not serialized properly, stop parsing.
                break;
            }

            // Store the decoded data.
            $returnData[$varname] = $data;

            // Calculate the length of the serialized data to advance the offset.
            $serializedActual = serialize($data);
            $offset += strlen($serializedActual);
        }
        return $returnData;
    }

}

/**
 * Class BroadcastServer
 * Implements the MessageComponentInterface to handle WebSocket connections.
 * This server manages client connections, handles messages, and can broadcast data to connected clients.
 */
class BroadcastServer implements MessageComponentInterface {
    /**
     * @var \SplObjectStorage A collection of all connected clients.
     */
    protected $clients;

    /**
     * @var array An array to store session data for each connection, keyed by resourceId.
     */
    protected $connSession = [];

    /**
     * @var SessionManager The session manager instance to retrieve session data.
     */
    protected $sessionManager;

    /**
     * BroadcastServer constructor.
     *
     * @param SessionManager $sessionManager The session manager dependency.
     */
    public function __construct(SessionManager $sessionManager) {
        // Initialize the client storage.
        $this->clients = new \SplObjectStorage;
        // Assign the session manager.
        $this->sessionManager = $sessionManager;
        // Output a ready message to the console.
        echo "BroadcastServer ready\n";
    }

    /**
     * Called when a new WebSocket connection is opened.
     *
     * @param ConnectionInterface $conn The new connection object.
     */
    public function onOpen(ConnectionInterface $conn) {
        // Attach the new connection to the client list.
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";

        // Check if the connection has an associated HTTP request.
        if (isset($conn->httpRequest)) {
            // Get session data from the HTTP request and store it.
            $this->connSession[$conn->resourceId] =
                $this->sessionManager->getSessionFromRequest($conn->httpRequest);

            // Send a welcome message to the new client.
            $conn->send(json_encode([
                'type'=>'welcome',
                'message'=>'Welcome to the WebSocket server!',
                'resourceId'=>$conn->resourceId
            ]));
        }
    }

    /**
     * Called when a message is received from a client.
     *
     * @param ConnectionInterface $from The connection from which the message was received.
     * @param string $msg The message received.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // Echo the received message back to the sender.
        $from->send(json_encode(['type'=>'echo', 'payload'=>$msg]));
    }

    /**
     * Called when a WebSocket connection is closed.
     *
     * @param ConnectionInterface $conn The connection that was closed.
     */
    public function onClose(ConnectionInterface $conn) {
        // Detach the connection from the client list.
        $this->clients->detach($conn);
        // Remove the session data associated with the closed connection.
        unset($this->connSession[$conn->resourceId]);
        echo "Connection {$conn->resourceId} closed\n";
    }

    /**
     * Called when an error occurs on a connection.
     *
     * @param ConnectionInterface $conn The connection where the error occurred.
     * @param \Exception $e The exception object.
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        // Close the connection immediately.
        $conn->close();
    }

    /**
     * Broadcasts a message to all connected clients.
     *
     * This method includes a filter example to send messages only to authenticated users.
     *
     * @param mixed $data The data to be broadcasted.
     */
    public function broadcast($data) {
        // Encode the data to JSON format.
        $payload = json_encode($data);
        // Loop through all connected clients.
        foreach ($this->clients as $client) {
            // Get the session data for the current client.
            $sessionData = isset($this->connSession[$client->resourceId]) ? $this->connSession[$client->resourceId] : null;
            // Example filter: send only to logged-in users.
            if ($sessionData && isset($sessionData->data) && isset($sessionData->data['magicUsername'])) {
                // Only send to users who have a valid session.
                try {
                    $client->send($payload);
                } catch (\Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            }
        }
        
        // Log the broadcasted message to the console.
        echo "Broadcast: {$payload}\n";
    }
}

/**
 * Normalizes array keys to camelCase.
 *
 * This function takes an associative array and converts all of its keys
 * from snake_case or kebab-case to camelCase. Non-alphanumeric characters
 * are removed, and the first letter of each word (after the first) is capitalized.
 *
 * @param array $array The input array with keys to be normalized.
 * @return array The new array with normalized keys.
 */
function normalizeKeysToCamelCase($array) {
    // Initialize an empty array to store the normalized key-value pairs.
    $normalized = [];
    
    // Loop through each key-value pair in the input array.
    foreach ($array as $key => $value) {
        // Replace underscores and hyphens with spaces to prepare for word-casing.
        $camelKey = str_replace(['_', '-'], ' ', $key);
        // Capitalize the first letter of each word.
        $camelKey = ucwords($camelKey);
        // Remove all spaces.
        $camelKey = str_replace(' ', '', $camelKey);
        // Convert the very first letter of the resulting string to lowercase.
        $camelKey = lcfirst($camelKey);
        
        // Assign the original value to the new camelCase key.
        $normalized[$camelKey] = $value;
    }
    
    return $normalized;
}

/**
 * Updates the status of the WebSocket server in the database.
 *
 * This function checks for the existence of a 'websocket' record in the 'setting' table.
 * If the record exists, it updates the 'content' column with the new status.
 * If the record does not exist, it inserts a new record with 'setting_id' as 'websocket'
 * and the provided status.
 *
 * @param PDO $pdo A PDO database connection object.
 * @param string $status The new status to be saved (e.g., 'running', 'stopped').
 */
function updateServerStatus(PDO $pdo, $status) {
    // Check if the record already exists.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM setting WHERE setting_id = ?");
    $stmt->execute(['websocket']);
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        // Update the record if it exists.
        $stmt = $pdo->prepare("UPDATE setting SET content = ? WHERE setting_id = ?");
        $stmt->execute([$status, 'websocket']);
    } else {
        // Insert a new record if it doesn't exist.
        $stmt = $pdo->prepare("INSERT INTO setting (setting_id, content) VALUES (?, ?)");
        $stmt->execute(['websocket', $status]);
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////

// Load configuration from YAML file
$spycy = new Spicy();
$config = $spycy->loadFile(dirname(__DIR__) . '/www/MagicAppBuilder/inc.cfg/core.yml');

$databaseConfig = isset($config['database']) ? $config['database'] : [];
$sessionConfig = isset($config['sessions']) ? $config['sessions'] : [];
$notificationConfig = isset($config['notification']) ? $config['notification'] : [];

$databaseConfig = normalizeKeysToCamelCase($databaseConfig);
$sessionConfig = normalizeKeysToCamelCase($sessionConfig);
$notificationConfig = normalizeKeysToCamelCase($notificationConfig);

$httpPort = isset($notificationConfig['httpPort']) ? $notificationConfig['httpPort'] : 8080; // default HTTP port for notify endpoint
$wsPort   = isset($notificationConfig['wsPort']) ? $notificationConfig['wsPort'] : 8081;     // default WebSocket port
$authToken = isset($notificationConfig['authToken']) ? $notificationConfig['authToken'] : ''; // default authentication token for notify requests

if($notificationConfig['enabled'])
{
    // Create the ReactPHP event loop
    $loop = Loop::get();

    // Create the session manager with the loaded session configuration
    $sessionManager = new SessionManager($sessionConfig);

    // Build the database DSN (Data Source Name) for PDO connection
    $dataSource = sprintf(
        '%s:host=%s;port=%d;dbname=%s;charset=utf8',
        $databaseConfig['driver'],
        $databaseConfig['host'],
        $databaseConfig['port'],
        $databaseConfig['databaseName']
    );

    // Create a new PDO instance for database operations with exception error mode
    $pdo = new PDO($dataSource, $databaseConfig['username'], $databaseConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Update the server status to 'online' on startup
    updateServerStatus($pdo, 'online');

    // Initialize the broadcast WebSocket server with session management
    $broadcastServer = new BroadcastServer($sessionManager);
    $wsServer = new Ratchet\Server\IoServer(
        new HttpServer(new WsServer($broadcastServer)),
        new SocketServer("0.0.0.0:{$wsPort}", [], $loop),
        $loop
    );

    // Create a small HTTP server to receive notification requests (e.g. POST /notify)
    $http = new ReactHttpServer(function (ServerRequestInterface $req) use ($broadcastServer, $authToken) {
        $path = $req->getUri()->getPath();
        if ($path !== '/notify') {
            // Return 404 if the endpoint is not /notify
            return new React\Http\Message\Response(404, ['Content-Type'=>'application/json'], json_encode(['success'=>false, 'error'=>'not found']));
        }

        // Simple authentication: check for Bearer token in Authorization header
        $auth = $req->getHeaderLine('Authorization'); // expected format: "Bearer token..."
        if (!preg_match('/Bearer\s+(.+)/', $auth, $m) || $m[1] !== $authToken) {
            // Return 401 Unauthorized if token is missing or invalid
            return new React\Http\Message\Response(401, ['Content-Type'=>'application/json'], json_encode(['success'=>false, 'error'=>'unauthorized']));
        }

        // Read and decode JSON payload from request body
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['data'])) {
            // Return 400 Bad Request if payload is invalid or missing required 'message' field
            return new React\Http\Message\Response(400, ['Content-Type'=>'application/json'], json_encode(['success'=>false, 'error'=>'invalid payload, must be json with field data']));
        }

        // Prepare the event data to broadcast
        $event = [
            'type' => isset($data['type']) ? $data['type'] : 'notification',
            'data' => $data['data'],
            'meta' => isset($data['meta']) ? $data['meta'] : null,
            'sent_at' => gmdate('c'), // UTC timestamp in ISO 8601 format
        ];

        // Broadcast the event to all connected WebSocket clients
        $broadcastServer->broadcast($event);

        // Return success response
        return new React\Http\Message\Response(200, ['Content-Type'=>'application/json'], json_encode(['success'=>true, 'error' => null]));
    });

    // Listen on configured HTTP port for notify endpoint
    $socket = new SocketServer("0.0.0.0:{$httpPort}", [], $loop);
    $http->listen($socket);

    echo "HTTP notify endpoint listening on port {$httpPort}\n";
    echo "WebSocket listening on port {$wsPort}\n";

    // Signal handler for Linux/Mac (SIGINT for Ctrl+C)
    if (defined('SIGINT') && function_exists('pcntl_signal')) {
        $loop->addSignal(SIGINT, function() use (&$running, $loop, $pdo) {
            echo "CTRL+C detected\n";
            $running = false;  // Optional flag to stop application logic if needed
            $loop->stop();     // Stop the ReactPHP event loop gracefully
            updateServerStatus($pdo, 'offline'); // Update server status to offline
        });
    }

    // Signal handler for Windows (CTRL+C event)
    if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_set_ctrl_handler')) {
        sapi_windows_set_ctrl_handler(function($event) use (&$running, $loop, $pdo) {
            if ($event === PHP_WINDOWS_EVENT_CTRL_C) {
                echo "CTRL+C detected\n";
                $running = false;  // Optional flag to stop application logic if needed
                $loop->stop();     // Stop the ReactPHP event loop gracefully
                updateServerStatus($pdo, 'offline'); // Update server status to offline
            }
            return true; // Indicate that we handled the event
        });
    }

    // Add a periodic timer to keep the event loop alive and responsive
    $loop->addPeriodicTimer(1, function() {
        // No operation needed here; this keeps the loop active
    });

    // Start the ReactPHP event loop (blocking call)
    $loop->run();
}
else
{
    // Notify that the notification service is disabled in configuration
    echo "Notification service is disabled.\n";
}
