<?php
/**
 * phpMyAdmin configuration — SHARED across all Planetbiru Server instances.
 *
 * Strategi mencari port MariaDB:
 *   1. Coba environment variable MYSQL_PORT (di-set Apache via SetEnv)
 *   2. Fallback: query config.db berdasarkan APACHE_PORT yang sedang diakses
 *   3. Fallback terakhir: 3306
 */

declare(strict_types=1);

$cfg['blowfish_secret'] = 'PlanetbiruServerSharedSecret0123456789ABCDEF';

$i = 0;

// ---------- Tentukan port MariaDB ----------
$maria_port = null;

// Cara 2: fallback ke config.db (shared SQLite)
if ($maria_port === null) {
    $apache_port = (int)($_SERVER['SERVER_PORT'] ?? 80);
    $db_path     = __DIR__ . '/../config.db';    // <dist>/config.db

    if (is_file($db_path) && class_exists('SQLite3')) {
        try {
            $db = new SQLite3($db_path, SQLITE3_OPEN_READONLY);
            $db->busyTimeout(500);

            $stmt = $db->prepare(
                "SELECT mariadb_port FROM instance_config WHERE apache_port = :p LIMIT 1"
            );
            $stmt->bindValue(':p', $apache_port, SQLITE3_INTEGER);
            $result = $stmt->execute();
            if ($result) {
                $row = $result->fetchArray(SQLITE3_ASSOC);
                if ($row && !empty($row['mariadb_port'])) {
                    $maria_port = (int)$row['mariadb_port'];
                }
                $result->finalize();
            }
            $db->close();
        } catch (Throwable $e) {
            // Diamkan; nanti fallback ke 3306
        }
    }
}

// Cara 3: fallback terakhir
if ($maria_port === null) {
    $maria_port = 3306;
}

// ---------- Server 1: instance aktif ----------
$i++;
$cfg['Servers'][$i]['auth_type']        = 'cookie';
$cfg['Servers'][$i]['host']             = '127.0.0.1';
$cfg['Servers'][$i]['port']             = (string)$maria_port;
$cfg['Servers'][$i]['connect_type']     = 'tcp';
$cfg['Servers'][$i]['compress']         = false;
$cfg['Servers'][$i]['AllowNoPassword']  = true;
$cfg['Servers'][$i]['extension']        = 'mysqli';
$cfg['Servers'][$i]['verbose']          = 'Instance: ' . (getenv('APP_NAME') ?: 'default');

// ---------- Global options ----------
$cfg['UploadDir']                       = '';
$cfg['SaveDir']                         = '';
$cfg['CheckConfigurationPermissions']   = false;
$cfg['TempDir']                         = sys_get_temp_dir();