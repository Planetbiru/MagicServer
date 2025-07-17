<?php

require_once __DIR__ . "/fn.php";

echo "=== MagicAppBuilder Portable Stopper ===\n";

// Stop MySQL/MariaDB service (mysqld.exe)
stopProcessByName("mysqld.exe");

// Stop Redis service (redis-server.exe)
stopProcessByName("redis-server.exe");

// Stop Apache HTTP server (httpd.exe)
stopProcessByName("httpd.exe");

echo "DONE. All services stopped.\n";
