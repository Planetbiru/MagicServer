<?php

require_once __DIR__ . "/fn.php";

/**
 * install.php
 * 
 * Script to download and extract the latest release of MagicAppBuilder
 * from GitHub into the www/MagicAppBuilder directory.
 */


// Ensure necessary directories
ensureDirectory(__DIR__ . "/www");
ensureDirectory(__DIR__ . "/data");
ensureDirectory(__DIR__ . "/sessions");
ensureDirectory(__DIR__ . "/apache/logs");


// Replace config templates
replaceAndWrite(__DIR__ . "/config/httpd-template.conf", __DIR__ . "/config/httpd.conf");
replaceAndWrite(__DIR__ . "/config/php-template.ini", __DIR__ . "/php/php.ini");
replaceAndWrite(__DIR__ . "/config/my-template.ini", __DIR__ . "/mysql/my.ini");

$apiUrl = 'https://api.github.com/repos/planetbiru/magicappbuilder/releases/latest';
$targetZip = __DIR__ . '/magicappbuilder.zip';
$extractTo = __DIR__ . '/www/MagicAppBuilder';

echo "=== MagicAppBuilder Installer ===\n";

// Buat folder www jika belum ada
if (!is_dir(__DIR__ . '/www')) {
    mkdir(__DIR__ . '/www', 0777, true);
} else {
    chmod(__DIR__ . '/www', 0777);
}

// Hapus folder MagicAppBuilder lama jika ada
if (is_dir($extractTo)) {
    echo "Removing old MagicAppBuilder directory...\n";
    deleteFolder($extractTo);
}

// Ambil info rilis terbaru dari GitHub
echo "Fetching latest release info from GitHub...\n";
$releaseInfo = fetchJson($apiUrl);

if (!$releaseInfo || !isset($releaseInfo['zipball_url'])) {
    echo "❌ Failed to fetch release information.\n";
    exit(1);
}

$zipUrl = $releaseInfo['zipball_url'];
echo "Downloading latest release: {$releaseInfo['tag_name']}...\n";
file_put_contents($targetZip, fetchStream($zipUrl));

if (!file_exists($targetZip)) {
    echo "❌ Failed to download archive.\n";
    exit(1);
}

// Ekstrak ZIP secara manual ke www/MagicAppBuilder
echo "Extracting files manually...\n";
$zip = new ZipArchive();
if ($zip->open($targetZip) === true) {
    mkdir($extractTo, 0777, true);

    // Prefix folder dari GitHub zipball (e.g. 'planetbiru-magicappbuilder-abc123/')
    $firstDir = null;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        if (!$firstDir) {
            // Ambil prefix folder utama dari file pertama
            $firstDir = strtok($entry, '/');
        }

        // Hilangkan prefix folder
        $relativePath = preg_replace('#^' . preg_quote($firstDir, '#') . '/#', '', $entry);
        if ($relativePath === '') continue;

        $destPath = $extractTo . '/' . $relativePath;

        if (substr($entry, -1) === '/') {
            // Directory
            if (!is_dir($destPath)) {
                mkdir($destPath, 0777, true);
            }
        } else {
            // File
            $content = $zip->getFromIndex($i);
            $dir = dirname($destPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($destPath, $content);
        }
    }

    $zip->close();
    unlink($targetZip);

    echo "✅ MagicAppBuilder has been installed at: www/MagicAppBuilder\n";
} else {
    echo "❌ Failed to open zip archive.\n";
    unlink($targetZip);
    exit(1);
}

/**
 * Menghapus folder dan seluruh isinya
 */
function deleteFolder($path) {
    if (!file_exists($path)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
    }
    rmdir($path);
}

/**
 * Fetch JSON dari GitHub API (tanpa SSL verify)
 */
function fetchJson($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'MagicServerInstaller/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/vnd.github.v3+json'],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$result) {
        echo "❌ Failed to fetch JSON. HTTP Code: $httpCode\n";
        if ($err) echo "cURL Error: $err\n";
        return null;
    }

    return json_decode($result, true);
}

/**
 * Fetch binary stream dari URL
 */
function fetchStream($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'MagicServerInstaller/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
