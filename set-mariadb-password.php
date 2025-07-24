<?php
$host = 'localhost';
$port = 3306; // Default MariaDB port
$rootUser = 'root';
$rootPassword = ''; // enter root password here if any

// Change this to the user you want to modify
$userToChange = 'root';
$newPassword = 'password'; // new desired password

try {
    // Create PDO connection with specified port
    $dsn = "mysql:host=$host;port=$port";
    $pdo = new PDO($dsn, $rootUser, $rootPassword); // NOSONAR
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Execute commands to change password for different host entries
    $hosts = ['localhost', '127.0.0.1', '::1'];
    foreach ($hosts as $hostName) {
        $sql = "ALTER USER '$userToChange'@'$hostName' IDENTIFIED BY :pass";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pass', $newPassword);
        $stmt->execute();
    }

    echo "✅ Password successfully changed for user '$userToChange'";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
