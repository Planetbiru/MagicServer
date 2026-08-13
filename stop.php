<?php

require_once __DIR__ . "/fn.php";

echo COLOR_BLUE . "=== MagicAppBuilder Portable Stopper ===\n" . COLOR_NC;

$processesToStop = [
    "httpd.exe"        => "Apache",
    "mysqld.exe"       => "MariaDB",
    "redis-server.exe" => "Redis",
];

foreach ($processesToStop as $processName => $serviceName) {
    echo "Stopping $serviceName ($processName)...\n";
    stopProcessByName($processName);
}

echo COLOR_GREEN . "✅ All services stopped.\n" . COLOR_NC;
