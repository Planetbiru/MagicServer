<?php

require_once __DIR__ . "/fn.php";

echo "=== MagicAppBuilder Portable Stopper ===\n";



// Stop MySQL/MariaDB (mysqld.exe)
stopProcessByName("mysqld.exe");

// Stop Apache (httpd.exe)
stopProcessByName("httpd.exe");

echo "DONE. All services stopped.\n";
