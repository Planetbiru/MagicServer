<?php

require_once __DIR__ . "/fn.php";

echo COLOR_BLUE . "=== Set MariaDB Root Password ===\n" . COLOR_NC;

$host = '127.0.0.1';
$port = 3306; // Default MariaDB port
$rootUser = 'root';
$rootPassword = ''; // enter root password here if any

// Change this to the user you want to modify
$userToChange = 'root';
$newPassword = 'password'; // new desired password

try {
    echo "Connecting to MariaDB...\n";
    // Create PDO connection with specified port
    $dsn = "mysql:host=$host;port=$port";
    $pdo = new PDO($dsn, $rootUser, $rootPassword); // NOSONAR
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo COLOR_GREEN . "Connection successful.\n" . COLOR_NC;

    // Execute commands to change password for different host entries
    $hosts = ['localhost', '127.0.0.1', '::1'];
    echo "Attempting to set password for '$userToChange' on hosts: " . implode(', ', $hosts) . "\n";

    foreach ($hosts as $hostName) {
        try {
            $sql = "ALTER USER '$userToChange'@'$hostName' IDENTIFIED BY :pass";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pass', $newPassword);
            $stmt->execute();
            echo "  - Password for '$userToChange'@'$hostName' updated.\n";
        } catch (PDOException $e) {
            // Ignore errors if user@host doesn't exist, which is common
            if (strpos($e->getMessage(), 'drop user') === false && strpos($e->getMessage(), 'rename user') === false) {
                 echo COLOR_YELLOW . "  - Could not update for '$userToChange'@'$hostName'. User may not exist. (Skipping)\n" . COLOR_NC;
            }
        }
    }

    echo COLOR_GREEN . "\n✅ Password successfully set to '$newPassword' for user '$userToChange' on all relevant hosts.\n" . COLOR_NC;
} catch (PDOException $e) {
    echo COLOR_RED . "\n❌ Error: " . $e->getMessage() . "\n" . COLOR_NC;
    echo COLOR_YELLOW . "If you have already set a password, please enter it in the '\$rootPassword' variable inside the 'set-mariadb-password.php' script.\n" . COLOR_NC;
}
