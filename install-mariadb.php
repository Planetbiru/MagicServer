<?php

// Path to the MariaDB installation utility (mariadb-install-db.exe)
$mysqlBin = __DIR__ . "/mysql/bin/mariadb-install-db.exe";

// Path to the data directory where the MariaDB system tables will be initialized
$dataDir = __DIR__ . "/mysql/data";

// Build the command to run mariadb-install-db.exe in background (non-blocking)
// Note: There's a typo in the original: missing closing quote for --datadir
$cmd1 = "start /B \"\" \"" . $mysqlBin . "\" --datadir=\"" . $dataDir . "\"";

// Execute the command in background using Windows popen and pclose
// This runs the initialization process without waiting for it to complete
pclose(popen($cmd1, "r"));
