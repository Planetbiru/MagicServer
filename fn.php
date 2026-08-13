<?php

// ANSI Color Codes for console output
const COLOR_GREEN = "\033[0;32m";
const COLOR_RED   = "\033[0;31m";
const COLOR_BLUE  = "\033[0;34m";
const COLOR_YELLOW = "\033[0;33m";
const COLOR_NC    = "\033[0m"; // No Color

/**
 * Ensure a directory exists and has 0777 permissions.
 *
 * @param string $path The path of the directory to ensure.
 */
function ensureDirectory($path)
{
    if (!file_exists($path)) {
        @mkdir($path, 0777, true);
    } else {
        @chmod($path, 0777);
    }
}

/**
 * Replace the {INSTALL_DIR} placeholder in a template file and
 * write the result to the given output path.
 *
 * @param string $templatePath The source template file.
 * @param string $outputPath   The destination output file.
 */
function replaceAndWrite($templatePath, $outputPath)
{
    $installDir = str_replace("\\", "/", __DIR__);
    $content = @file_get_contents($templatePath);
    $content = str_replace('{INSTALL_DIR}', $installDir, $content);

    $content = str_replace('{APACHE_PORT}', 80, $content);

    @file_put_contents($outputPath, $content);
}

/**
 * Adds a directory to the system PATH environment variable if it's not already present.
 *
 * @param string $newPath The directory path to add.
 */
function addPathToEnvironment($newPath)
{
    $newPath = rtrim($newPath, DIRECTORY_SEPARATOR);
    $os = strtoupper(substr(PHP_OS, 0, 3));
    $commandPrefix = '';
    if ($os === 'WIN') {
        // Windows system
        $currentPath = trim(shell_exec('echo %PATH%'));
        $separator = ';';
        $commandPrefix = 'setx PATH ';
    } else {
        // Unix/Linux/macOS system
        $currentPath = getenv('PATH');
        $separator = ':';
    }

    $paths = explode($separator, $currentPath);
    $normalizedPaths = array_map('trim', $paths);

    // Check if new path is already in PATH
    if (in_array($newPath, $normalizedPaths)) {
        echo "Path already exists in PATH.\n";
        return;
    }

    // Append the new path
    $updatedPath = $currentPath . $separator . $newPath;

    if ($os === 'WIN') {
        // Use setx to persist environment variable in Windows
        $command = $commandPrefix . escapeshellarg($updatedPath);
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            echo "Path successfully added to PATH.\n";
        } else {
            echo "Failed to update PATH. You may need to run this script as administrator.\n";
        }
    } else {
        // For Unix/Linux, suggest user to manually update shell profile
        echo "To update your PATH, add the following to your shell profile:\n";
        echo 'export PATH="$PATH:' . $newPath . '"' . "\n";
    }
}

/**
 * Attempts to stop all running processes by their executable name (Windows only).
 *
 * @param string $name The process executable name (e.g., "notepad.exe").
 */
function stopProcessByName($name)
{
    echo "Stopping $name...\n";
    // Check if the process is running before attempting to kill it
    $tasklistOutput = [];
    @exec("tasklist /FI \"IMAGENAME eq $name\" 2>NUL", $tasklistOutput);

    if (count($tasklistOutput) <= 1) { // tasklist returns header + process line if running
        echo "  [INFO] $name is not running.\n";
        return;
    }

    // Kill all processes with this name
    @exec("taskkill /F /IM $name", $output, $exitCode);

    if ($exitCode === 0) {
        echo "  [OK] $name stopped successfully.\n";
    } else {
        echo "  [ERROR] Failed to stop $name.\n";
    }
}

/**
 * Deletes a folder and all its contents.
 *
 * @param string $path The directory to delete.
 */
function deleteFolder($path)
{
    if (!file_exists($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
        );

    foreach ($items as $item) {
        $item->isDir()
            ? @rmdir($item->getRealPath())
            : @unlink($item->getRealPath());
    }

    @rmdir($path);
}

/**
 * Fetch JSON data from a given URL (with SSL verification disabled).
 *
 * @param string $url The URL to fetch.
 * @return array|null The parsed JSON response, or null on failure.
 */
function fetchJson($url)
{
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

    if ($httpCode !== 200 || !$result) {
        echo "❌ Failed to fetch JSON. HTTP Code: $httpCode\n";
        if ($err) {
            echo "cURL Error: $err\n";
        }
        return null;
    }

    return json_decode($result, true);
}

/**
 * Retrieve the sizes of release assets from a GitHub repository.
 *
 * This function queries the GitHub API for a specific repository release
 * and returns an array of asset information including name, size (in bytes),
 * and download URL.
 *
 * @param string $owner GitHub repository owner (username or organization).
 * @param string $repo  GitHub repository name.
 * @param string $tag   Release tag (default: 'latest').
 *
 * @return array|false  Returns an array of assets with keys:
 *                      - name (string): Asset file name
 *                      - size (int): Asset size in bytes
 *                      - download_url (string): Direct download URL
 *                      Returns false if the request fails or no assets are found.
 */
function getGithubAssetSizes($owner, $repo, $tag = 'latest')
{
    $url = "https://api.github.com/repos/{$owner}/{$repo}/releases/" . $tag;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP'); // GitHub API butuh user-agent
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    if (!$response) {
        return false;
    }

    $data = json_decode($response, true);
    if (!isset($data['assets'])) {
        return false;
    }

    $sizes = array();
    foreach ($data['assets'] as $asset) {
        $sizes[] = array(
            'name' => $asset['name'],
            'size' => $asset['size'], // dalam byte
            'download_url' => $asset['browser_download_url']
        );
    }

    return $sizes;
}

/**
 * Fetch a binary stream from the given URL.
 *
 * @param string $url The URL to fetch.
 * @param callable|null $progressCallback A callback function for download progress.
 * @return string|false The downloaded data, or false on failure.
 */
function fetchStream($url, $progressCallback = null)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'MagicServerInstaller/1.0',
        CURLOPT_NOPROGRESS => false, // Required to enable progress function
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    if ($progressCallback !== null && is_callable($progressCallback)) {
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progressCallback);
    }

    return curl_exec($ch);
}

/**
 * Downloads a file from a URL and saves it to a destination, showing progress.
 *
 * @param string $url The URL of the file to download.
 * @param string $destination The path to save the file.
 * @return bool True on success, false on failure.
 */
function downloadFileWithProgress($url, $destination)
{
    $fileHandle = fopen($destination, 'w');
    if ($fileHandle === false) {
        echo COLOR_RED . "❌ Could not open file for writing: $destination\n" . COLOR_NC;
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fileHandle);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MagicAppBuilder Installer');
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Add this line
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Add this line
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);

    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded) {
        if ($download_size > 0) {
            $percentage = round($downloaded * 100 / $download_size);
            $barLength = 40;
            $filledLength = round($barLength * $percentage / 100);
            $bar = str_repeat('=', $filledLength) . str_repeat(' ', $barLength - $filledLength);
            printf("\rDownloading: [%s] %d%%", $bar, $percentage);
        }
    });

    $result = curl_exec($ch);
    fclose($fileHandle);

    echo "\n"; // New line after progress bar

    return $result;
}