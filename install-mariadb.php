<?php

require_once __DIR__ . "/fn.php";

ensureDirectory(__DIR__ . "/data");

echo COLOR_BLUE . "=== MariaDB Installer ===\n" . COLOR_NC;
ensureDirectory(__DIR__ . "/data/mysql");
replaceAndWrite(__DIR__ . "/config/my-template.ini", __DIR__ . "/config/my.ini");

// Path to the MariaDB installation utility (mariadb-install-db.exe)
$mysqlBin = __DIR__ . "/mysql/bin/mariadb-install-db.exe";

// Path to the data directory where the MariaDB system tables will be initialized
$dataDir = __DIR__ . "/data/mysql";

echo "Initializing MariaDB data directory...\n";

// Build the command to run mariadb-install-db.exe in background (non-blocking)
// Note: There's a typo in the original: missing closing quote for --datadir
$cmd1 = "start /B \"\" \"" . $mysqlBin . "\" --datadir=\"" . $dataDir . "\"";

// Execute the command in background using Windows popen and pclose
// This runs the initialization process without waiting for it to complete
pclose(popen($cmd1, "r"));

echo COLOR_GREEN . "✅ MariaDB initialization process has been started in the background.\n" . COLOR_NC;
echo COLOR_YELLOW . "Please wait a moment for the process to complete before starting the server.\n" . COLOR_NC;
