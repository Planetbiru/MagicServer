<?php

require_once __DIR__ . "/fn.php";

/**
 * fn-updater.php
 * 
 * Script to download and update MagicServer with the latest release from GitHub.
 * It will skip user data and configuration files.
 */

// --- Configuration ---
$repoOwner = 'Planetbiru';
$repoName = 'MagicServer';
$apiUrl = "https://api.github.com/repos/$repoOwner/$repoName/releases/latest";

// Directories and files to skip during the update
$skipPaths = [
    'data/',
    'mysql/',
    'redis/',
    'logs/',
    'sessions/',
    'tmp/',
    'www/',
    'config/httpd.conf',
    'config/my.ini',
    'config/php.ini',
    'config/redis.windows.conf',
    'config/redis.windows-service.conf',
    'backups/' // Don't include the backup directory in backups
];

// --- Main Script ---

$targetDir = __DIR__;
$backupDir = $targetDir . '/backups';
$updateTempDir = $targetDir . '/update-temp';
$tempZip = $targetDir . '/magicserver-update.zip';

echo COLOR_BLUE . "=== MagicServer Updater ===\n" . COLOR_NC;

// --- Stop all services before proceeding ---
echo COLOR_YELLOW . "Attempting to stop all running services before update...\n" . COLOR_NC;
require_once __DIR__ . "/stop.php";
echo COLOR_YELLOW . "-----------------------------------------------------\n" . COLOR_NC;

// 1. Fetch latest release info from GitHub
echo "Fetching latest release info from GitHub...\n";
$releaseInfo = fetchJson($apiUrl);

if (!$releaseInfo || !isset($releaseInfo['zipball_url'])) {
    echo COLOR_RED . "❌ Failed to fetch release information. Please check your internet connection.\n" . COLOR_NC;
    exit(1);
}

$tagName = $releaseInfo['tag_name'];

// --- Find the correct download URL and size from assets ---
$zipUrl = null;

if (!empty($releaseInfo['assets'])) {
    foreach ($releaseInfo['assets'] as $asset) {
        // Find the first asset that is a zip file
        if ($asset['content_type'] === 'application/zip' || pathinfo($asset['name'], PATHINFO_EXTENSION) === 'zip') {
            $zipUrl = $asset['browser_download_url'];
            break;
        }
    }
}

// Fallback to zipball_url if no suitable asset is found
if (!$zipUrl) {
    $zipUrl = $releaseInfo['zipball_url'];
}

echo "Found latest release: " . COLOR_YELLOW . $tagName . COLOR_NC . "\n";

// Ask for user confirmation before proceeding
echo "Do you want to proceed with the update to version " . COLOR_YELLOW . $tagName . COLOR_NC . "? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo COLOR_RED . "Update cancelled by user.\n" . COLOR_NC;
    exit(0);
}

// 2. Download the release ZIP file
echo "Confirmation received. Downloading update... (This may take a few moments)\n";

$fileContent = fetchStream($zipUrl);

if ($fileContent === false || file_put_contents($tempZip, $fileContent) === false) {
    echo COLOR_RED . "❌ Failed to download the update archive.\n" . COLOR_NC;
    @unlink($tempZip);
    exit(1);
}

if (!file_exists($tempZip)) {
    echo COLOR_RED . "❌ Failed to save the update archive.\n" . COLOR_NC;
    exit(1);
}

$currentBackupPath = $backupDir . '/update-backup-' . date('Ymd-His');

try {
    // 3. Create a backup before updating
    echo "Creating backup at: $currentBackupPath\n";
    ensureDirectory($backupDir);
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $relativePath = str_replace($targetDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
        
        // Check if the path should be skipped from backup
        $shouldSkip = false;
        foreach ($skipPaths as $skip) {
            // Normalize slashes for comparison
            if (strpos(str_replace('\\', '/', $relativePath), $skip) === 0) {
                $shouldSkip = true;
                break;
            }
        }
        if ($shouldSkip) continue;

        $backupFilePath = $currentBackupPath . DIRECTORY_SEPARATOR . $relativePath;
        if ($file->isDir()) {
            ensureDirectory($backupFilePath);
        } else {
            ensureDirectory(dirname($backupFilePath));
            copy($file->getRealPath(), $backupFilePath);
        }
    }
    echo COLOR_GREEN . "✅ Backup created successfully.\n" . COLOR_NC;

    // 4. Open the ZIP and copy files one by one
    echo "Applying update...\n";
    $zip = new ZipArchive();
    if ($zip->open($tempZip) !== true) {
        throw new Exception("Failed to open the update archive.");
    }

    // Find the root directory name inside the zip (e.g., 'Planetbiru-MagicServer-abcdef/')
    $rootPrefix = $zip->getNameIndex(0);
    $rootPrefix = strpos($rootPrefix, '/') !== false ? substr($rootPrefix, 0, strpos($rootPrefix, '/') + 1) : '';

    // Extract all new files to a temporary directory first
    echo "Extracting update to a temporary location...\n";
    $zip->extractTo($updateTempDir);
    $updatedFiles = 0;
    $skippedFiles = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        $relativePath = str_replace($rootPrefix, '', $entryName);

        if (empty($relativePath)) continue;

        // Check if the path should be skipped
        $shouldSkip = false;
        foreach ($skipPaths as $skip) {
            if (strpos($relativePath, $skip) === 0) {
                $shouldSkip = true;
                break;
            }
        }

        if ($shouldSkip) {
            $skippedFiles++;
            continue;
        }

        $destinationPath = $targetDir . '/' . $relativePath;

        // Special handling for the PHP directory itself
        if (strpos($relativePath, 'php/') === 0) {
            continue; // Skip PHP files for now, they will be handled by the batch script
        }

        $sourcePath = $updateTempDir . '/' . $entryName;

        // If it's a directory, ensure it exists
        if (is_dir($sourcePath)) {
            ensureDirectory($destinationPath);
            continue;
        }

        // It's a file, copy it
        ensureDirectory(dirname($destinationPath));
        if (copy($sourcePath, $destinationPath)) {
            $updatedFiles++;
        } else {
            throw new Exception("Could not copy file: $relativePath");
        }
    }

    $zip->close();

    // Check if there is a PHP update
    $phpNeedsUpdate = false;

    $newPhpDir = $updateTempDir . '/' . $rootPrefix . 'php';
    if (is_dir($newPhpDir)) {
        $phpNeedsUpdate = true;
        echo COLOR_YELLOW . "PHP runtime update detected.\n" . COLOR_NC;
    }

    if ($phpNeedsUpdate) {
        echo COLOR_YELLOW . "A separate script will complete the final update steps.\n" . COLOR_NC;
        $batchScriptPath = $targetDir . '/finalize-update-temp.bat';
        $batchScriptContent = <<<BAT
@echo off
setlocal

echo Waiting for the main process to release files...
rem Initial delay to allow the PHP process to terminate.
ping 127.0.0.1 -n 3 > nul

BAT;
        if ($phpNeedsUpdate) {
            $batchScriptContent .= "echo Updating PHP runtime...\r\n";
            $batchScriptContent .= "robocopy \"$newPhpDir\" \"$targetDir\\php\" /E /IS /MOVE > NUL\r\n";
        }

        $batchScriptContent .= <<<BAT

echo Cleaning up temporary update files...
if exist "$updateTempDir" (
    rmdir /s /q "$updateTempDir"
)

echo Update finished. Deleting this script...
(endlocal)
(goto) 2>nul & del "%~f0"

BAT;
        file_put_contents($batchScriptPath, $batchScriptContent);

        // Execute the batch script in a new, detached process
        pclose(popen("start /B \"\" \"$batchScriptPath\"", "r"));
    }

    echo COLOR_GREEN . "✅ Update applied successfully.\n" . COLOR_NC;
    echo "   - Files updated: $updatedFiles\n";
    echo "   - Files/directories skipped: $skippedFiles\n";

    // 5. Success: Clean up backup
    echo "Update successful. Removing temporary files...\n";
    deleteFolder($currentBackupPath);
    if (!$phpNeedsUpdate) { // Only clean up if no batch script is running
        deleteFolder($updateTempDir);
    }
    echo COLOR_GREEN . "✅ MagicServer has been updated to version " . COLOR_YELLOW . $tagName . COLOR_NC . ".\n";

} catch (Exception $e) {
    echo COLOR_RED . "\n❌ AN ERROR OCCURRED: " . $e->getMessage() . "\n" . COLOR_NC;
    echo COLOR_YELLOW . "Attempting to roll back changes...\n" . COLOR_NC;

    if (!is_dir($currentBackupPath)) {
        echo COLOR_RED . "❌ Rollback failed: Backup directory not found.\n" . COLOR_NC;
    } else {
        // Restore from backup
        $backupFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($currentBackupPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($backupFiles as $file) {
            $relativePath = str_replace($currentBackupPath . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $livePath = $targetDir . DIRECTORY_SEPARATOR . $relativePath;
            if ($file->isDir()) {
                ensureDirectory($livePath);
            } else {
                copy($file->getRealPath(), $livePath);
            }
        }
        echo COLOR_GREEN . "✅ Rollback complete. Your previous version has been restored.\n" . COLOR_NC;
        // Clean up the backup directory after successful rollback
        deleteFolder($currentBackupPath);
    }

} finally {
    // 6. Always clean up the temporary ZIP file
    if (file_exists($tempZip)) {
        echo "Cleaning up temporary files...\n";
        @unlink($tempZip);
        if (!$phpNeedsUpdate) {
            deleteFolder($updateTempDir); // Clean up only if the batch script is not responsible for it
        }
    }
}
