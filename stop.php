<?php

require_once __DIR__ . "/fn.php";

echo COLOR_BLUE . "=== MagicAppBuilder Portable Stopper ===\n" . COLOR_NC;

// Stop MySQL/MariaDB service (mysqld.exe)
echo "Stopping MariaDB (mysqld.exe)...\n";
stopProcessByName("mysqld.exe");

// Stop Redis service (redis-server.exe)
echo "Stopping Redis (redis-server.exe)...\n";
stopProcessByName("redis-server.exe");

// Stop Apache HTTP server (httpd.exe)
echo "Stopping Apache (httpd.exe)...\n";
stopProcessByName("httpd.exe");

echo COLOR_GREEN . "✅ All services stopped.\n" . COLOR_NC;
