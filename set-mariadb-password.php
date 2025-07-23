<?php
$host = 'localhost';
$rootUser = 'root';
$rootPassword = ''; // enter root password here if any

// Change this to the user you want to modify
$userToChange = 'root';
$newPassword = 'password'; // new desired password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host", $rootUser, $rootPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Execute command to change the password
    $stmt = $pdo->prepare("ALTER USER :user@'localhost' IDENTIFIED BY :pass");
    $stmt->bindValue(':user', $userToChange);
    $stmt->bindValue(':pass', $newPassword);
    $stmt->execute();

    echo "✅ Password successfully changed";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
