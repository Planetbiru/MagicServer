<?php
echo "=== MagicAppBuilder Portable Stopper ===\n";

function stopProcessByName($name) {
    echo "Stopping $name...\n";
    $output = [];
    exec("tasklist /FI \"IMAGENAME eq $name\" 2>NUL", $output);
    
    if (count($output) <= 1) {
        echo "  [INFO] $name is not running.\n";
        return;
    }

    // Kill all processes with this name
    exec("taskkill /F /IM $name", $result, $exitCode);
    
    if ($exitCode === 0) {
        echo "  [OK] $name stopped successfully.\n";
    } else {
        echo "  [ERROR] Failed to stop $name.\n";
    }
}

// Stop MySQL/MariaDB (mysqld.exe)
stopProcessByName("mysqld.exe");

// Stop Apache (httpd.exe)
stopProcessByName("httpd.exe");

echo "DONE. All services stopped.\n";
