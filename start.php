<?php
echo "=== MagicAppBuilder Portable Installer ===\n";

$dir = __DIR__ . "/www";
chmod($dir, 0777);

$path1 = __DIR__ . "/config/httpd-template.conf\"";
$path2 = __DIR__ . "/config/httpd.conf\"";
$conf = file_get_contents($path1);
$conf = str_replace('${INSTALL_DIR}', str_replace("\\", "/", __DIR__), $conf);
file_put_contents($path2, $conf);


$path1 = __DIR__ . "/php/php-template.ini\"";
$path2 = __DIR__ . "/php/php.ini\"";
$conf = file_get_contents($path1);
$conf = str_replace('${INSTALL_DIR}', str_replace("\\", "/", __DIR__), $conf);
file_put_contents($path2, $conf);


// Set path
$apacheBin = __DIR__ . "/apache/bin/httpd.exe";
$mysqlBin = __DIR__ . "/mysql/bin/mysqld.exe";
$phpIni = __DIR__ . "/php/php.ini";

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
pclose(popen("start /B \"\" \"" . $mysqlBin . "\" --defaults-file=\"" . __DIR__ . "/config/my.ini\"", "r"));

// Jalankan Apache
echo "Starting Apache...\n";
pclose(popen("start /B \"\" \"" . $apacheBin . "\" -f \"" . __DIR__ . "/config/httpd.conf\"", "r"));

echo "DONE. Access your app at http://localhost\n";

pclose(popen("start http://localhost", "r"));