<?php

require_once __DIR__ . "/fn.php";
require_once __DIR__ . "/stop.php";

putenv("PATH=" . __DIR__ . "/php;" . __DIR__ . "/php/ext;" . getenv("PATH"));

$newPathToAdd = __DIR__ . "/php/bin";
//addPathToEnvironment($newPathToAdd);
$newPathToAdd = __DIR__ . "/mysql/bin";
//addPathToEnvironment($newPathToAdd);

// Ensure necessary directories
ensureDirectory(__DIR__ . "/www");
ensureDirectory(__DIR__ . "/tmp");
ensureDirectory(__DIR__ . "/data");
ensureDirectory(__DIR__ . "/logs");
ensureDirectory(__DIR__ . "/sessions");
ensureDirectory(__DIR__ . "/apache/logs");


// Replace config templates
replaceAndWrite(__DIR__ . "/config/httpd-template.conf", __DIR__ . "/config/httpd.conf");
replaceAndWrite(__DIR__ . "/config/php-template.ini", __DIR__ . "/php/php.ini");
replaceAndWrite(__DIR__ . "/config/my-template.ini", __DIR__ . "/config/my.ini");


echo "=== MagicAppBuilder Portable Installer ===\n";

// Set path
$apacheBin = __DIR__ . "/apache/bin/httpd.exe";
$mysqlBin = __DIR__ . "/mysql/bin/mysqld.exe";

// Cek dependensi
if (!file_exists($apacheBin)) {
    echo "[ERROR] Apache not found!\n";
    exit(1);
}

if (!file_exists($mysqlBin)) {
    echo "[ERROR] MySQL/MariaDB not found!\n";
    exit(1);
}

// Cek port
function isPortInUse($port) {
    $conn = @fsockopen("127.0.0.1", $port);
    if ($conn) {
        fclose($conn);
        return true;
    }
    return false;
}

if (isPortInUse(80)) {
    echo "[WARNING] Port 80 already in use.\n";
}

if (isPortInUse(3306)) {
    echo "[WARNING] Port 3306 already in use.\n";
}



// Jalankan MySQL
echo "Starting MySQL...\n";
pclose(popen("start /B \"\" \"" . $mysqlBin . "\" --defaults-file=\"" . __DIR__ . "/config/my.ini", "r"));

// Jalankan Apache
echo "Starting Apache...\n";
pclose(popen("start /B \"\" \"" . $apacheBin . "\" -f \"" . __DIR__ . "/config/httpd.conf\"", "r"));

echo "DONE. Access your app at http://localhost\n";

pclose(popen("start http://localhost", "r"));