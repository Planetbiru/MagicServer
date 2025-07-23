<?php

require_once __DIR__ . "/fn.php";

/**
 * install.php
 * 
 * Script to download and extract the latest release of MagicAppBuilder
 * from GitHub into the www/MagicAppBuilder directory.
 */

$phpPath     = __DIR__ . '/php';
$phpExtPath  = __DIR__ . '/php/ext';
$currentPath = getenv('PATH');

$paths = explode(PATH_SEPARATOR, $currentPath);

// Add only if not already present
if (!in_array($phpPath, $paths)) {
    $paths[] = $phpPath;
}
if (!in_array($phpExtPath, $paths)) {
    $paths[] = $phpExtPath;
}

// Rebuild and set the new PATH
putenv('PATH=' . implode(PATH_SEPARATOR, $paths));


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

// Create www folder if it doesn't exist
if (!is_dir(__DIR__ . '/www')) {
    mkdir(__DIR__ . '/www', 0777, true);
} else {
    chmod(__DIR__ . '/www', 0777);
}

// Fetch latest release info from GitHub
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

// Manually extract ZIP to www/MagicAppBuilder
echo "Extracting files manually...\n";
$zip = new ZipArchive();
if ($zip->open($targetZip) === true) {
    mkdir($extractTo, 0777, true);

    // GitHub zipball folder prefix (e.g., 'planetbiru-magicappbuilder-abc123/')
    $firstDir = null;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        if (!$firstDir) {
            // Get root folder prefix from first file
            $firstDir = strtok($entry, '/');
        }

        // Remove the root folder prefix
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
